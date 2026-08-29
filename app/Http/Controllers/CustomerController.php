<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        return view('customer.index');
    }

    public function load_data(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $data = DB::table('customers')
            ->where('tenant_id', $tenant_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $btn = '<a href="'.route('customer.profile', $row->id).'" class="btn btn-info btn-sm" style="margin-right:4px"><i class="bx bx-show"></i></a>';
                $btn .= '<button onclick="editCustomer(\''.$row->id.'\')" class="btn btn-warning btn-sm" style="margin-right:4px"><i class="bx bx-edit"></i></button>';
                $btn .= '<button onclick="deleteCustomer(\''.$row->id.'\')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';

                return $btn;
            })
            ->editColumn('total_visits', function ($row) {
                return $row->total_visits.' visits';
            })
            ->editColumn('last_visit_at', function ($row) {
                return $row->last_visit_at ? date('d M Y', strtotime($row->last_visit_at)) : '-';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'gender' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $tenant_id = $this->getTenantId();

        $existing = DB::table('customers')
            ->where('tenant_id', $tenant_id)
            ->where('phone', $request->phone)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Customer with this phone number already exists.'], 400);
        }

        DB::table('customers')->insert([
            'id' => Str::uuid(),
            'tenant_id' => $tenant_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Customer created successfully.']);
    }

    public function edit($id)
    {
        $customer = DB::table('customers')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $customer) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'gender' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $tenant_id = $this->getTenantId();

        $existing = DB::table('customers')
            ->where('tenant_id', $tenant_id)
            ->where('phone', $request->phone)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Another customer with this phone number already exists.'], 400);
        }

        DB::table('customers')
            ->where('id', $id)
            ->where('tenant_id', $tenant_id)
            ->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'notes' => $request->notes,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Customer updated successfully.']);
    }

    public function destroy($id)
    {
        DB::table('customers')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->delete();

        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    public function profile($id)
    {
        $tenant_id = $this->getTenantId();

        $customer = DB::table('customers')
            ->where('id', $id)
            ->where('tenant_id', $tenant_id)
            ->first();

        if (! $customer) {
            abort(404);
        }

        // Dynamically calculate total_visits and last_visit
        $total_visits = DB::table('bookings')
            ->where('customer_id', $id)
            ->where('tenant_id', $tenant_id)
            ->where('status', 'completed')
            ->count();

        $last_booking = DB::table('bookings')
            ->where('customer_id', $id)
            ->where('tenant_id', $tenant_id)
            ->orderBy('booking_date', 'desc')
            ->first();

        $last_visit_at = $last_booking ? date('d M Y', strtotime($last_booking->booking_date)) : '-';

        $bookings = DB::table('bookings')
            ->leftJoin('treatments', 'bookings.treatment_id', '=', 'treatments.id')
            ->leftJoin('staff', 'bookings.staff_id', '=', 'staff.id')
            ->where('bookings.customer_id', $id)
            ->where('bookings.tenant_id', $tenant_id)
            ->select(
                'bookings.id',
                'bookings.booking_date',
                'treatments.name as treatment_name',
                'staff.name as staff_name',
                'bookings.status'
            )
            ->orderBy('bookings.booking_date', 'desc')
            ->get();

        return view('customer.profile', compact('customer', 'bookings', 'total_visits', 'last_visit_at'));
    }
}
