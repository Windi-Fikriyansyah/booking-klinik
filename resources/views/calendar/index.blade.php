@extends('template.app')

@section('title', 'Calendar')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Booking Calendar</h5>
            </div>
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Staff Filter</label>
                        <select class="form-select" id="filter_staff">
                            <option value="">All Staff</option>
                            @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Treatment Filter</label>
                        <select class="form-select" id="filter_treatment">
                            <option value="">All Treatments</option>
                            @foreach($treatments as $treatment)
                            <option value="{{ $treatment->id }}">{{ $treatment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Filter</label>
                        <select class="form-select" id="filter_status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="no_show">No Show</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventTitle">Booking Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <strong class="d-block text-muted">Customer</strong>
                        <span id="eventCustomer" class="fs-5"></span>
                    </li>
                    <li class="mb-3">
                        <strong class="d-block text-muted">Treatment</strong>
                        <span id="eventTreatment"></span>
                    </li>
                    <li class="mb-3">
                        <strong class="d-block text-muted">Staff</strong>
                        <span id="eventStaff"></span>
                    </li>
                    <li class="mb-3">
                        <strong class="d-block text-muted">Time</strong>
                        <span id="eventTime"></span>
                    </li>
                    <li class="mb-0">
                        <strong class="d-block text-muted">Status</strong>
                        <span id="eventStatus" class="badge bg-primary"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '06:00:00', // start at 6am
            slotMaxTime: '23:00:00', // end at 11pm
            allDaySlot: false,
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: "{{ route('calendar.events') }}",
                    type: "GET",
                    data: {
                        start: info.startStr,
                        end: info.endStr,
                        staff_id: $('#filter_staff').val(),
                        treatment_id: $('#filter_treatment').val(),
                        status: $('#filter_status').val()
                    },
                    success: function(events) {
                        successCallback(events);
                    },
                    error: function() {
                        failureCallback();
                        alert('Error fetching calendar events!');
                    }
                });
            },
            eventClick: function(info) {
                var props = info.event.extendedProps;
                $('#eventCustomer').text(props.customer);
                $('#eventTreatment').text(props.treatment);
                $('#eventStaff').text(props.staff);
                $('#eventTime').text(info.event.start.toLocaleDateString() + ' ' + props.time);
                $('#eventStatus').text(props.status);
                
                $('#eventStatus').removeClass().addClass('badge').css('background-color', info.event.backgroundColor);
                
                eventModal.show();
            }
        });

        calendar.render();

        // Refresh calendar on filter change
        $('#filter_staff, #filter_treatment, #filter_status').on('change', function() {
            calendar.refetchEvents();
        });
    });
</script>

<style>
    #calendar {
        min-height: 600px;
    }
    
    .fc-event {
        cursor: pointer;
    }
</style>
@endpush
