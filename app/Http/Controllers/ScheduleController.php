<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        $tenant_id = $this->getTenantId();

        $operatingHoursData = DB::table('operating_hours')
            ->where('tenant_id', $tenant_id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week')
            ->toArray();

        $operatingHours = [];
        for ($i = 0; $i <= 6; $i++) {
            if (isset($operatingHoursData[$i])) {
                $operatingHours[$i] = $operatingHoursData[$i];
            } else {
                $operatingHours[$i] = (object) [
                    'day_of_week' => $i,
                    'is_open' => false,
                    'open_time' => '09:00',
                    'close_time' => '20:00',
                ];
            }
        }

        $staffs = DB::table('staff')
            ->where('tenant_id', $tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('schedule.index', compact('operatingHours', 'staffs'));
    }

    public function saveOperatingHours(Request $request)
    {
        $tenant_id = $this->getTenantId();
        $days = $request->days;

        if (! $days || ! is_array($days)) {
            return response()->json(['message' => 'Invalid data'], 400);
        }

        foreach ($days as $day => $data) {
            $existing = DB::table('operating_hours')
                ->where('tenant_id', $tenant_id)
                ->where('day_of_week', $day)
                ->first();

            $isOpen = isset($data['is_open']) && $data['is_open'] == '1';

            if ($existing) {
                DB::table('operating_hours')
                    ->where('id', $existing->id)
                    ->update([
                        'is_open' => $isOpen,
                        'open_time' => $data['open_time'] ?? null,
                        'close_time' => $data['close_time'] ?? null,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('operating_hours')->insert([
                    'id' => Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'day_of_week' => $day,
                    'is_open' => $isOpen,
                    'open_time' => $data['open_time'] ?? null,
                    'close_time' => $data['close_time'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Operating hours saved successfully.']);
    }

    public function getStaffSchedule($staff_id)
    {
        $tenant_id = $this->getTenantId();

        $schedules = DB::table('staff_schedules')
            ->where('tenant_id', $tenant_id)
            ->where('staff_id', $staff_id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week')
            ->toArray();

        $result = [];
        for ($i = 0; $i <= 6; $i++) {
            if (isset($schedules[$i])) {
                $result[$i] = $schedules[$i];
            } else {
                $result[$i] = [
                    'day_of_week' => $i,
                    'is_working' => false,
                    'start_time' => '09:00:00',
                    'end_time' => '20:00:00',
                ];
            }
        }

        return response()->json($result);
    }

    public function saveStaffSchedule(Request $request, $staff_id)
    {
        $tenant_id = $this->getTenantId();
        $days = $request->days;

        if (! $days || ! is_array($days)) {
            return response()->json(['message' => 'Invalid data'], 400);
        }

        foreach ($days as $day => $data) {
            $existing = DB::table('staff_schedules')
                ->where('tenant_id', $tenant_id)
                ->where('staff_id', $staff_id)
                ->where('day_of_week', $day)
                ->first();

            $isWorking = isset($data['is_working']) && $data['is_working'] == '1';

            if ($existing) {
                DB::table('staff_schedules')
                    ->where('id', $existing->id)
                    ->update([
                        'is_working' => $isWorking,
                        'start_time' => $data['start_time'] ?? null,
                        'end_time' => $data['end_time'] ?? null,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('staff_schedules')->insert([
                    'id' => Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'staff_id' => $staff_id,
                    'day_of_week' => $day,
                    'is_working' => $isWorking,
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Staff schedule saved successfully.']);
    }

    public function timeOffData(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $data = DB::table('staff_time_off')
            ->join('staff', 'staff_time_off.staff_id', '=', 'staff.id')
            ->where('staff_time_off.tenant_id', $tenant_id)
            ->select(
                'staff_time_off.id',
                'staff.name as staff_name',
                'staff_time_off.start_datetime',
                'staff_time_off.end_datetime',
                'staff_time_off.reason'
            )
            ->orderBy('staff_time_off.start_datetime', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                return '<button onclick="deleteTimeOff(\''.$row->id.'\')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';
            })
            ->editColumn('start_datetime', function ($row) {
                return date('d M Y H:i', strtotime($row->start_datetime));
            })
            ->editColumn('end_datetime', function ($row) {
                return date('d M Y H:i', strtotime($row->end_datetime));
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function saveTimeOff(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::table('staff_time_off')->insert([
            'id' => Str::uuid(),
            'tenant_id' => $this->getTenantId(),
            'staff_id' => $request->staff_id,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'reason' => $request->reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Time off added successfully.']);
    }

    public function deleteTimeOff($id)
    {
        DB::table('staff_time_off')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->delete();

        return response()->json(['message' => 'Time off deleted successfully.']);
    }
}
