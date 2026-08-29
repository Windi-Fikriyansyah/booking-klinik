<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        $tenant_id = $this->getTenantId();

        $staffs = DB::table('staff')->where('tenant_id', $tenant_id)->where('is_active', true)->orderBy('name')->get();
        $treatments = DB::table('treatments')->where('tenant_id', $tenant_id)->where('is_active', true)->orderBy('name')->get();

        return view('calendar.index', compact('staffs', 'treatments'));
    }

    public function events(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $start = $request->start;
        $end = $request->end;

        $query = DB::table('bookings')
            ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
            ->leftJoin('treatments', 'bookings.treatment_id', '=', 'treatments.id')
            ->leftJoin('staff', 'bookings.staff_id', '=', 'staff.id')
            ->where('bookings.tenant_id', $tenant_id);

        if ($start) {
            $query->where('bookings.booking_date', '>=', date('Y-m-d', strtotime($start)));
        }
        if ($end) {
            $query->where('bookings.booking_date', '<=', date('Y-m-d', strtotime($end)));
        }

        if ($request->staff_id) {
            $query->where('bookings.staff_id', $request->staff_id);
        }
        if ($request->treatment_id) {
            $query->where('bookings.treatment_id', $request->treatment_id);
        }
        if ($request->status) {
            $query->where('bookings.status', $request->status);
        }

        $bookings = $query->select(
            'bookings.id',
            'bookings.booking_date',
            'bookings.start_time',
            'bookings.end_time',
            'bookings.status',
            'customers.name as customer_name',
            'treatments.name as treatment_name',
            'staff.name as staff_name'
        )->get();

        $events = [];
        foreach ($bookings as $b) {
            $color = '#696cff'; // primary
            switch ($b->status) {
                case 'completed': $color = '#71dd37';
                    break; // success
                case 'cancelled': $color = '#ff3e1d';
                    break; // danger
                case 'no_show': $color = '#8592a3';
                    break; // secondary
                case 'pending': $color = '#ffab00';
                    break; // warning
                case 'checked_in': $color = '#03c3ec';
                    break; // info
                case 'confirmed': $color = '#71dd37';
                    break; // success
            }

            $events[] = [
                'id' => $b->id,
                'title' => $b->customer_name.' ('.$b->treatment_name.')',
                'start' => $b->booking_date.'T'.$b->start_time,
                'end' => $b->booking_date.'T'.$b->end_time,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'customer' => $b->customer_name,
                    'treatment' => $b->treatment_name,
                    'staff' => $b->staff_name ?? 'Any',
                    'status' => strtoupper(str_replace('_', ' ', $b->status)),
                    'time' => date('H:i', strtotime($b->start_time)).' - '.date('H:i', strtotime($b->end_time)),
                ],
            ];
        }

        return response()->json($events);
    }
}
