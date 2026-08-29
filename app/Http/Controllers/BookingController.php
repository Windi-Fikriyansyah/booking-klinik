<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenantUsers()->first()->tenant_id;
    }

    public function index()
    {
        $tenant_id = $this->getTenantId();

        $customers = DB::table('customers')->where('tenant_id', $tenant_id)->orderBy('name')->get();
        $treatments = DB::table('treatments')->where('tenant_id', $tenant_id)->where('is_active', true)->orderBy('name')->get();
        $staffs = DB::table('staff')->where('tenant_id', $tenant_id)->where('is_active', true)->orderBy('name')->get();

        return view('booking.index', compact('customers', 'treatments', 'staffs'));
    }

    public function load_data(Request $request)
    {
        $tenant_id = $this->getTenantId();

        $data = DB::table('bookings')
            ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
            ->leftJoin('treatments', 'bookings.treatment_id', '=', 'treatments.id')
            ->leftJoin('staff', 'bookings.staff_id', '=', 'staff.id')
            ->where('bookings.tenant_id', $tenant_id)
            ->select(
                'bookings.id',
                'bookings.booking_number',
                'customers.name as customer_name',
                'treatments.name as treatment_name',
                'staff.name as staff_name',
                'bookings.booking_date',
                'bookings.start_time',
                'bookings.status'
            )
            ->orderBy('bookings.booking_date', 'desc')
            ->orderBy('bookings.start_time', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" onclick="editBooking(\''.$row->id.'\')"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(\''.$row->id.'\', \'confirmed\')"><i class="bx bx-check-circle me-1 text-success"></i> Confirm</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(\''.$row->id.'\', \'checked_in\')"><i class="bx bx-door-open me-1 text-info"></i> Check-In</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(\''.$row->id.'\', \'in_progress\')"><i class="bx bx-play-circle me-1 text-primary"></i> In Progress</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(\''.$row->id.'\', \'completed\')"><i class="bx bx-check-double me-1 text-success"></i> Complete</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(\''.$row->id.'\', \'no_show\')"><i class="bx bx-user-x me-1 text-secondary"></i> No Show</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="updateStatus(\''.$row->id.'\', \'cancelled\')"><i class="bx bx-x-circle me-1"></i> Cancel</a>
                            </div>
                        </div>';

                return $btn;
            })
            ->editColumn('booking_date', function ($row) {
                return date('d M Y', strtotime($row->booking_date)).' '.date('H:i', strtotime($row->start_time));
            })
            ->editColumn('status', function ($row) {
                $badge = 'bg-label-primary';
                switch ($row->status) {
                    case 'completed': $badge = 'bg-label-success';
                        break;
                    case 'cancelled': $badge = 'bg-label-danger';
                        break;
                    case 'no_show': $badge = 'bg-label-secondary';
                        break;
                    case 'pending': $badge = 'bg-label-warning';
                        break;
                    case 'checked_in': $badge = 'bg-label-info';
                        break;
                    case 'in_progress': $badge = 'bg-label-primary';
                        break;
                    case 'confirmed': $badge = 'bg-label-success';
                        break;
                }

                return '<span class="badge '.$badge.' text-capitalize">'.str_replace('_', ' ', $row->status).'</span>';
            })
            ->rawColumns(['aksi', 'status'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'treatment_id' => 'required|exists:treatments,id',
            'staff_id' => 'nullable|exists:staff,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'discount' => 'nullable|numeric',
        ]);

        $tenant_id = $this->getTenantId();

        $treatment = DB::table('treatments')->where('id', $request->treatment_id)->first();

        $duration = $treatment->duration_minutes ?? 60;
        $endTime = date('H:i', strtotime($request->start_time." +{$duration} minutes"));

        $slotStart = strtotime($request->booking_date.' '.$request->start_time);
        $slotEnd = $slotStart + ($duration * 60);

        // App Layer Validation for Double Booking
        $query = DB::table('bookings')
            ->where('tenant_id', $tenant_id)
            ->where('booking_date', $request->booking_date)
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $overlapping = $query->get()->filter(function ($b) use ($slotStart, $slotEnd, $request) {
            $bStart = strtotime($request->booking_date.' '.$b->start_time);
            $bEnd = strtotime($request->booking_date.' '.$b->end_time);

            return max($slotStart, $bStart) < min($slotEnd, $bEnd);
        })->count();

        if ($overlapping > 0) {
            return response()->json(['message' => 'The selected time slot is already booked.'], 400);
        }

        $price = $treatment->price ?? 0;
        $discount = $request->discount ?? 0;
        $total_amount = $price - $discount;

        $bookingCount = DB::table('bookings')
            ->where('tenant_id', $tenant_id)
            ->whereDate('created_at', today())
            ->count();
        $bookingNumber = 'BK-'.date('Ymd').'-'.str_pad($bookingCount + 1, 5, '0', STR_PAD_LEFT);

        $bookingId = Str::uuid();

        DB::table('bookings')->insert([
            'id' => $bookingId,
            'tenant_id' => $tenant_id,
            'booking_number' => $bookingNumber,
            'customer_id' => $request->customer_id,
            'treatment_id' => $request->treatment_id,
            'staff_id' => $request->staff_id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime,
            'price' => $price,
            'discount' => $discount,
            'total_amount' => $total_amount,
            'status' => 'pending',
            'source' => 'admin',
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_status_histories')->insert([
            'id' => Str::uuid(),
            'tenant_id' => $tenant_id,
            'booking_id' => $bookingId,
            'old_status' => null,
            'new_status' => 'pending',
            'notes' => 'Booking created.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Booking created successfully.']);
    }

    public function edit($id)
    {
        $booking = DB::table('bookings')
            ->where('id', $id)
            ->where('tenant_id', $this->getTenantId())
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($booking);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'treatment_id' => 'required|exists:treatments,id',
            'staff_id' => 'nullable|exists:staff,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'discount' => 'nullable|numeric',
        ]);

        $tenant_id = $this->getTenantId();

        $booking = DB::table('bookings')->where('id', $id)->where('tenant_id', $tenant_id)->first();
        if (! $booking) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $treatment = DB::table('treatments')->where('id', $request->treatment_id)->first();

        $duration = $treatment->duration_minutes ?? 60;
        $endTime = date('H:i', strtotime($request->start_time." +{$duration} minutes"));

        $slotStart = strtotime($request->booking_date.' '.$request->start_time);
        $slotEnd = $slotStart + ($duration * 60);

        // App Layer Validation for Double Booking
        $query = DB::table('bookings')
            ->where('tenant_id', $tenant_id)
            ->where('booking_date', $request->booking_date)
            ->where('id', '!=', $id)
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $overlapping = $query->get()->filter(function ($b) use ($slotStart, $slotEnd, $request) {
            $bStart = strtotime($request->booking_date.' '.$b->start_time);
            $bEnd = strtotime($request->booking_date.' '.$b->end_time);

            return max($slotStart, $bStart) < min($slotEnd, $bEnd);
        })->count();

        if ($overlapping > 0) {
            return response()->json(['message' => 'The selected time slot is already booked.'], 400);
        }

        $price = $treatment->price ?? 0;
        $discount = $request->discount ?? 0;
        $total_amount = $price - $discount;

        DB::table('bookings')
            ->where('id', $id)
            ->update([
                'customer_id' => $request->customer_id,
                'treatment_id' => $request->treatment_id,
                'staff_id' => $request->staff_id,
                'booking_date' => $request->booking_date,
                'start_time' => $request->start_time,
                'end_time' => $endTime,
                'price' => $price,
                'discount' => $discount,
                'total_amount' => $total_amount,
                'notes' => $request->notes,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Booking updated successfully.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,in_progress,completed,cancelled,no_show',
        ]);

        $tenant_id = $this->getTenantId();

        $booking = DB::table('bookings')
            ->where('id', $id)
            ->where('tenant_id', $tenant_id)
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($booking->status === $request->status) {
            return response()->json(['message' => 'Booking is already '.$request->status], 400);
        }

        $updates = [
            'status' => $request->status,
            'updated_at' => now(),
        ];

        if ($request->status === 'completed') {
            $updates['completed_at'] = now();
        } elseif ($request->status === 'cancelled') {
            $updates['cancelled_at'] = now();
        }

        DB::table('bookings')->where('id', $id)->update($updates);

        DB::table('booking_status_histories')->insert([
            'id' => Str::uuid(),
            'tenant_id' => $tenant_id,
            'booking_id' => $id,
            'old_status' => $booking->status,
            'new_status' => $request->status,
            'notes' => 'Status updated to '.$request->status.' by admin.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Booking status updated to '.$request->status]);
    }

    public function getAvailableTimes(Request $request)
    {
        $tenant_id = $this->getTenantId();
        $date = $request->date;
        $staff_id = $request->staff_id;
        $treatment_id = $request->treatment_id;

        if (! $date || ! $treatment_id) {
            return response()->json([]);
        }

        $treatment = DB::table('treatments')->where('id', $treatment_id)->first();
        if (! $treatment) {
            return response()->json([]);
        }
        $duration = $treatment->duration_minutes ?? 60;

        $dayOfWeek = date('w', strtotime($date));

        // 1. Get Operating Hours
        $opHour = DB::table('operating_hours')
            ->where('tenant_id', $tenant_id)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $opHour || ! $opHour->is_open) {
            return response()->json([]); // Clinic is closed
        }

        $openTime = strtotime($opHour->open_time);
        $closeTime = strtotime($opHour->close_time);

        // 2. If staff selected, check Staff Schedule
        if ($staff_id) {
            $staffSchedule = DB::table('staff_schedules')
                ->where('tenant_id', $tenant_id)
                ->where('staff_id', $staff_id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if (! $staffSchedule || ! $staffSchedule->is_working) {
                return response()->json([]); // Staff not working today
            }

            $staffStart = strtotime($staffSchedule->start_time);
            $staffEnd = strtotime($staffSchedule->end_time);

            // Intersect Staff time with Clinic time
            $openTime = max($openTime, $staffStart);
            $closeTime = min($closeTime, $staffEnd);
        }

        // 3. Generate slots (every 30 mins)
        $slots = [];
        $currentTime = $openTime;
        while ($currentTime + ($duration * 60) <= $closeTime) {
            $slots[] = date('H:i', $currentTime);
            $currentTime += 30 * 60;
        }

        // 4. Check Existing Bookings
        $query = DB::table('bookings')
            ->where('tenant_id', $tenant_id)
            ->where('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($staff_id) {
            $query->where('staff_id', $staff_id);
        }

        $existingBookings = $query->get();

        $availableSlots = [];
        foreach ($slots as $slot) {
            $slotStart = strtotime($date.' '.$slot);
            $slotEnd = $slotStart + ($duration * 60);

            $isOverlap = false;
            foreach ($existingBookings as $booking) {
                $bStart = strtotime($date.' '.$booking->start_time);
                $bEnd = strtotime($date.' '.$booking->end_time);

                if (max($slotStart, $bStart) < min($slotEnd, $bEnd)) {
                    $isOverlap = true;
                    break;
                }
            }

            // 5. Check Staff Time Off
            if (! $isOverlap && $staff_id) {
                $timeOffs = DB::table('staff_time_off')
                    ->where('tenant_id', $tenant_id)
                    ->where('staff_id', $staff_id)
                    ->whereDate('start_datetime', '<=', $date)
                    ->whereDate('end_datetime', '>=', $date)
                    ->get();

                foreach ($timeOffs as $to) {
                    $toStart = strtotime($to->start_datetime);
                    $toEnd = strtotime($to->end_datetime);

                    if (max($slotStart, $toStart) < min($slotEnd, $toEnd)) {
                        $isOverlap = true;
                        break;
                    }
                }
            }

            if (! $isOverlap) {
                $availableSlots[] = $slot;
            }
        }

        return response()->json($availableSlots);
    }
}
