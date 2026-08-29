<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TreatmentCategoryController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        return view('treatment_category.index');
    }

    public function load_data(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $data = DB::table('treatment_categories')
            ->where('tenant_id', $tenant_id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $btn = '<button onclick="editCategory(\''.$row->id.'\')" class="btn btn-warning btn-sm" style="margin-right:4px"><i class="bx bx-edit"></i></button>';
                $btn .= '<button onclick="deleteCategory(\''.$row->id.'\')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';

                return $btn;
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
        ]);

        DB::table('treatment_categories')->insert([
            'id' => Str::uuid(),
            'tenant_id' => $this->getTenantId(),
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Category created successfully.']);
    }

    public function edit($id)
    {
        $category = DB::table('treatment_categories')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $category) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        DB::table('treatment_categories')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->update([
                'name' => $request->name,
                'is_active' => $request->has('is_active'),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy($id)
    {
        DB::table('treatment_categories')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    public function toggle_status($id)
    {
        $category = DB::table('treatment_categories')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if ($category) {
            DB::table('treatment_categories')
                ->where('id', $id)
                ->update(['is_active' => ! $category->is_active]);

            return response()->json(['message' => 'Status updated.']);
        }

        return response()->json(['message' => 'Not found'], 404);
    }
}
