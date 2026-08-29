<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        $treatments = DB::table('treatments')
            ->where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->get();

        return view('staff.index', compact('treatments'));
    }

    public function load_data(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $data = DB::table('staff')
            ->where('tenant_id', $tenant_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $btn = '<button onclick="editStaff(\''.$row->id.'\')" class="btn btn-warning btn-sm" style="margin-right:4px"><i class="bx bx-edit"></i></button>';
                $btn .= '<button onclick="deleteStaff(\''.$row->id.'\')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';

                return $btn;
            })
            ->addColumn('treatments', function ($row) {
                $treatments = DB::table('staff_treatments')
                    ->join('treatments', 'staff_treatments.treatment_id', '=', 'treatments.id')
                    ->where('staff_treatments.staff_id', $row->id)
                    ->pluck('treatments.name')
                    ->toArray();

                return implode(', ', $treatments);
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
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'role' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:150',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'treatments' => 'nullable|array',
            'treatments.*' => 'exists:treatments,id',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff', 'public');
        }

        $staffId = Str::uuid();

        DB::table('staff')->insert([
            'id' => $staffId,
            'tenant_id' => $this->getTenantId(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'photo' => $photoPath,
            'role' => $request->role,
            'specialization' => $request->specialization,
            'bio' => $request->bio,
            'is_active' => $request->is_active == 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->has('treatments')) {
            $staffTreatments = [];
            foreach ($request->treatments as $treatmentId) {
                $staffTreatments[] = [
                    'id' => Str::uuid(),
                    'tenant_id' => $this->getTenantId(),
                    'staff_id' => $staffId,
                    'treatment_id' => $treatmentId,
                    'created_at' => now(),
                ];
            }
            if (! empty($staffTreatments)) {
                DB::table('staff_treatments')->insert($staffTreatments);
            }
        }

        return response()->json(['message' => 'Staff created successfully.']);
    }

    public function edit($id)
    {
        $staff = DB::table('staff')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $staff) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $treatments = DB::table('staff_treatments')
            ->where('staff_id', $id)
            ->pluck('treatment_id')
            ->toArray();

        $staff->treatments = $treatments;

        return response()->json($staff);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'role' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:150',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'treatments' => 'nullable|array',
            'treatments.*' => 'exists:treatments,id',
        ]);

        $staff = DB::table('staff')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $staff) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $photoPath = $staff->photo;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff', 'public');
        }

        DB::table('staff')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'photo' => $photoPath,
                'role' => $request->role,
                'specialization' => $request->specialization,
                'bio' => $request->bio,
                'is_active' => $request->is_active == 1,
                'updated_at' => now(),
            ]);

        DB::table('staff_treatments')->where('staff_id', $id)->delete();
        if ($request->has('treatments')) {
            $staffTreatments = [];
            foreach ($request->treatments as $treatmentId) {
                $staffTreatments[] = [
                    'id' => Str::uuid(),
                    'tenant_id' => $this->getTenantId(),
                    'staff_id' => $id,
                    'treatment_id' => $treatmentId,
                    'created_at' => now(),
                ];
            }
            if (! empty($staffTreatments)) {
                DB::table('staff_treatments')->insert($staffTreatments);
            }
        }

        return response()->json(['message' => 'Staff updated successfully.']);
    }

    public function destroy($id)
    {
        DB::table('staff')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->delete();

        return response()->json(['message' => 'Staff deleted successfully.']);
    }

    public function toggle_status($id)
    {
        $staff = DB::table('staff')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if ($staff) {
            DB::table('staff')
                ->where('id', $id)
                ->update(['is_active' => ! $staff->is_active]);

            return response()->json(['message' => 'Status updated.']);
        }

        return response()->json(['message' => 'Not found'], 404);
    }

    public function delete_photo($id)
    {
        $staff = DB::table('staff')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
            Storage::disk('public')->delete($staff->photo);
        }

        DB::table('staff')->where('id', $id)->update(['photo' => null]);

        return response()->json(['message' => 'Photo deleted successfully.']);
    }
}
