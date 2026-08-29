<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TreatmentController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        $categories = DB::table('treatment_categories')
            ->where('tenant_id', $this->getTenantId())
            ->get();

        return view('treatment.index', compact('categories'));
    }

    public function load_data(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $data = DB::table('treatments')
            ->leftJoin('treatment_categories', 'treatments.category_id', '=', 'treatment_categories.id')
            ->select('treatments.*', 'treatment_categories.name as category_name')
            ->where('treatments.tenant_id', $tenant_id)
            ->orderBy('treatments.sort_order', 'asc')
            ->orderBy('treatments.created_at', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $btn = '<button onclick="editTreatment(\''.$row->id.'\')" class="btn btn-warning btn-sm" style="margin-right:4px"><i class="bx bx-edit"></i></button>';
                $btn .= '<button onclick="deleteTreatment(\''.$row->id.'\')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';

                return $btn;
            })
            ->editColumn('price', function ($row) {
                return 'Rp '.number_format($row->price, 0, ',', '.');
            })
            ->editColumn('duration_minutes', function ($row) {
                return $row->duration_minutes.' menit';
            })
            ->editColumn('is_active', function ($row) {
                $checked = $row->is_active ? 'checked' : '';

                return '<div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" onchange="toggleStatus(\''.$row->id.'\')" '.$checked.'>
                        </div>';
            })
            ->rawColumns(['aksi', 'is_active'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|uuid',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'sub_images' => 'nullable|array|max:3',
            'sub_images.*' => 'nullable|image|max:2048',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('treatments', 'public');
        }

        $treatmentId = Str::uuid();

        DB::table('treatments')->insert([
            'id' => $treatmentId,
            'tenant_id' => $this->getTenantId(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.Str::random(4),
            'description' => $request->description,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'sort_order' => $request->sort_order ?? 0,
            'image' => $thumbnailPath,
            'is_active' => $request->is_active == 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->hasFile('sub_images')) {
            foreach ($request->file('sub_images') as $index => $file) {
                $path = $file->store('treatments/sub', 'public');
                DB::table('treatment_images')->insert([
                    'id' => Str::uuid(),
                    'treatment_id' => $treatmentId,
                    'image_path' => $path,
                    'is_thumbnail' => false,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Treatment created successfully.']);
    }

    public function edit($id)
    {
        $treatment = DB::table('treatments')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $treatment) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $sub_images = DB::table('treatment_images')
            ->where('treatment_id', $id)
            ->orderBy('sort_order', 'asc')
            ->get();

        $treatment->sub_images = $sub_images;

        return response()->json($treatment);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|uuid',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'sub_images' => 'nullable|array|max:3',
            'sub_images.*' => 'nullable|image|max:2048',
        ]);

        $treatment = DB::table('treatments')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $treatment) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $thumbnailPath = $treatment->image;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('treatments', 'public');
        }

        DB::table('treatments')
            ->where('id', $id)
            ->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name).'-'.Str::random(4),
                'description' => $request->description,
                'price' => $request->price,
                'duration_minutes' => $request->duration_minutes,
                'sort_order' => $request->sort_order ?? 0,
                'image' => $thumbnailPath,
                'is_active' => $request->is_active == 1,
                'updated_at' => now(),
            ]);

        if ($request->hasFile('sub_images')) {
            $existingCount = DB::table('treatment_images')->where('treatment_id', $id)->count();
            $newFiles = $request->file('sub_images');
            if ($existingCount + count($newFiles) > 3) {
                return response()->json(['message' => 'Total sub image (lama + baru) tidak boleh lebih dari 3.'], 422);
            }
            foreach ($newFiles as $index => $file) {
                $path = $file->store('treatments/sub', 'public');
                DB::table('treatment_images')->insert([
                    'id' => Str::uuid(),
                    'treatment_id' => $id,
                    'image_path' => $path,
                    'is_thumbnail' => false,
                    'sort_order' => $existingCount + $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Treatment updated successfully.']);
    }

    public function delete_sub_image($id)
    {
        $image = DB::table('treatment_images')
            ->join('treatments', 'treatment_images.treatment_id', '=', 'treatments.id')
            ->where('treatment_images.id', $id)
            ->where('treatments.tenant_id', $this->getTenantId())
            ->select('treatment_images.*')
            ->first();

        if (! $image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        DB::table('treatment_images')->where('id', $id)->delete();

        return response()->json(['message' => 'Sub image deleted successfully.']);
    }

    public function delete_thumbnail($id)
    {
        $treatment = DB::table('treatments')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $treatment) {
            return response()->json(['message' => 'Treatment not found'], 404);
        }

        if ($treatment->image && Storage::disk('public')->exists($treatment->image)) {
            Storage::disk('public')->delete($treatment->image);
        }

        DB::table('treatments')->where('id', $id)->update(['image' => null]);

        return response()->json(['message' => 'Thumbnail deleted successfully.']);
    }

    public function destroy($id)
    {
        DB::table('treatments')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->delete();

        return response()->json(['message' => 'Treatment deleted successfully.']);
    }

    public function toggle_status($id)
    {
        $treatment = DB::table('treatments')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if ($treatment) {
            DB::table('treatments')
                ->where('id', $id)
                ->update(['is_active' => ! $treatment->is_active]);

            return response()->json(['message' => 'Status updated.']);
        }

        return response()->json(['message' => 'Not found'], 404);
    }
}
