@extends('template.app')

@section('title', 'Schedule Management')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Schedule Management</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-operating-hours" aria-controls="navs-operating-hours" aria-selected="true">
                            Clinic Operating Hours
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-staff-schedule" aria-controls="navs-staff-schedule" aria-selected="false">
                            Staff Schedule
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-time-off" aria-controls="navs-time-off" aria-selected="false">
                            Staff Time Off
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content px-0 py-2">
                    <!-- Operating Hours Tab -->
                    <div class="tab-pane fade show active" id="navs-operating-hours" role="tabpanel">
                        <form id="operatingHoursForm">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="150">Hari</th>
                                            <th width="100">Buka/Tutup</th>
                                            <th>Jam Buka</th>
                                            <th>Jam Tutup</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                        @endphp
                                        @foreach($operatingHours as $i => $data)
                                            <tr>
                                                <td class="fw-semibold">{{ $days[$i] }}</td>
                                                <td>
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input op-status-toggle" type="checkbox" id="op_status_{{ $i }}" name="days[{{ $i }}][is_open]" value="1" {{ $data->is_open ? 'checked' : '' }} onchange="toggleTimeInputs('op', {{ $i }})">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control op-time-input-{{ $i }}" name="days[{{ $i }}][open_time]" value="{{ $data->open_time ? \Carbon\Carbon::parse($data->open_time)->format('H:i') : '' }}" {{ $data->is_open ? '' : 'disabled' }}>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control op-time-input-{{ $i }}" name="days[{{ $i }}][close_time]" value="{{ $data->close_time ? \Carbon\Carbon::parse($data->close_time)->format('H:i') : '' }}" {{ $data->is_open ? '' : 'disabled' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary" id="btnSaveOperating">Save Operating Hours</button>
                            </div>
                        </form>
                    </div>

                    <!-- Staff Schedule Tab -->
                    <div class="tab-pane fade" id="navs-staff-schedule" role="tabpanel">
                        <div class="mb-3">
                            <label for="selectStaff" class="form-label">Pilih Staff</label>
                            <select id="selectStaff" class="form-select w-50" onchange="loadStaffSchedule()">
                                <option value="">-- Pilih Staff --</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="staffScheduleContainer" class="d-none">
                            <form id="staffScheduleForm">
                                <input type="hidden" id="current_staff_id">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="150">Hari</th>
                                                <th width="100">Bekerja</th>
                                                <th>Mulai Kerja</th>
                                                <th>Selesai Kerja</th>
                                            </tr>
                                        </thead>
                                        <tbody id="staffScheduleBody">
                                            <!-- Injected by JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-primary" id="btnSaveStaffSchedule">Save Staff Schedule</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Staff Time Off Tab -->
                    <div class="tab-pane fade" id="navs-time-off" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-primary" onclick="showAddTimeOffModal()">
                                <i class="bx bx-plus me-1"></i> Add Time Off
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered w-100" id="timeOffTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Action</th>
                                        <th>Staff Name</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Time Off -->
<div class="modal fade" id="timeOffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="timeOffForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Time Off</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="timeoff_staff_id" class="form-label">Staff</label>
                        <select id="timeoff_staff_id" class="form-select" required>
                            <option value="">Pilih Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_datetime" class="form-label">Mulai (Tanggal & Jam)</label>
                            <input type="datetime-local" id="start_datetime" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_datetime" class="form-label">Selesai (Tanggal & Jam)</label>
                            <input type="datetime-local" id="end_datetime" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan (Cuti, Sakit, dll)</label>
                        <textarea id="reason" class="form-control" rows="3" placeholder="Contoh: Cuti Tahunan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveTimeOff">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    let timeOffTable;
    let timeOffModal;

    $(document).ready(function() {
        timeOffModal = new bootstrap.Modal(document.getElementById('timeOffModal'));

        // Init DataTables for Time Off
        timeOffTable = $('#timeOffTable').DataTable({
            responsive: true,
            ordering: false,
            serverSide: true,
            processing: true,
            ajax: {
                url: "{{ route('staff.schedule.time_off_data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: "text-center" },
                { data: 'aksi', name: 'aksi', className: "text-center" },
                { data: 'staff_name', name: 'staff_name' },
                { data: 'start_datetime', name: 'start_datetime' },
                { data: 'end_datetime', name: 'end_datetime' },
                { data: 'reason', name: 'reason' }
            ]
        });

        $('#operatingHoursForm').on('submit', function(e) {
            e.preventDefault();
            $('#btnSaveOperating').prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: "{{ route('staff.schedule.operating_hours') }}",
                type: "POST",
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    showToast('Failed to save operating hours', 'error');
                },
                complete: function() {
                    $('#btnSaveOperating').prop('disabled', false).text('Save Operating Hours');
                }
            });
        });

        $('#staffScheduleForm').on('submit', function(e) {
            e.preventDefault();
            const staffId = $('#current_staff_id').val();
            if (!staffId) return;

            $('#btnSaveStaffSchedule').prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: `{{ url('staff/schedule/staff-schedule') }}/${staffId}`,
                type: "POST",
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    showToast('Failed to save staff schedule', 'error');
                },
                complete: function() {
                    $('#btnSaveStaffSchedule').prop('disabled', false).text('Save Staff Schedule');
                }
            });
        });

        $('#timeOffForm').on('submit', function(e) {
            e.preventDefault();
            $('#btnSaveTimeOff').prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: "{{ route('staff.schedule.save_time_off') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    staff_id: $('#timeoff_staff_id').val(),
                    start_datetime: $('#start_datetime').val(),
                    end_datetime: $('#end_datetime').val(),
                    reason: $('#reason').val(),
                },
                success: function(res) {
                    timeOffModal.hide();
                    timeOffTable.ajax.reload();
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    let msg = 'Failed to save time off';
                    if (err.responseJSON && err.responseJSON.errors) {
                        msg = Object.values(err.responseJSON.errors).map(e => e.join('<br>')).join('<br>');
                    }
                    showToast(msg, 'error');
                },
                complete: function() {
                    $('#btnSaveTimeOff').prop('disabled', false).text('Save');
                }
            });
        });
    });

    function toggleTimeInputs(prefix, dayIndex) {
        const isChecked = $(`#${prefix}_status_${dayIndex}`).is(':checked');
        $(`.${prefix}-time-input-${dayIndex}`).prop('disabled', !isChecked);
    }

    function loadStaffSchedule() {
        const staffId = $('#selectStaff').val();
        if (!staffId) {
            $('#staffScheduleContainer').addClass('d-none');
            return;
        }

        $('#staffScheduleBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#staffScheduleContainer').removeClass('d-none');
        $('#current_staff_id').val(staffId);

        $.get(`{{ url('staff/schedule/staff-schedule') }}/${staffId}`, function(data) {
            let html = '';
            for (let i = 0; i <= 6; i++) {
                const dayData = data[i];
                const isWorking = dayData.is_working == 1;
                const startTime = dayData.start_time ? dayData.start_time.substring(0, 5) : '09:00';
                const endTime = dayData.end_time ? dayData.end_time.substring(0, 5) : '20:00';

                html += `
                <tr>
                    <td class="fw-semibold">${dayNames[i]}</td>
                    <td>
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" id="st_status_${i}" name="days[${i}][is_working]" value="1" ${isWorking ? 'checked' : ''} onchange="toggleTimeInputs('st', ${i})">
                        </div>
                    </td>
                    <td>
                        <input type="time" class="form-control st-time-input-${i}" name="days[${i}][start_time]" value="${startTime}" ${isWorking ? '' : 'disabled'}>
                    </td>
                    <td>
                        <input type="time" class="form-control st-time-input-${i}" name="days[${i}][end_time]" value="${endTime}" ${isWorking ? '' : 'disabled'}>
                    </td>
                </tr>
                `;
            }
            $('#staffScheduleBody').html(html);
        });
    }

    function showAddTimeOffModal() {
        $('#timeoff_staff_id').val('');
        $('#start_datetime').val('');
        $('#end_datetime').val('');
        $('#reason').val('');
        timeOffModal.show();
    }

    function deleteTimeOff(id) {
        if (confirm('Yakin ingin menghapus data libur ini?')) {
            $.ajax({
                url: `{{ url('staff/schedule/time-off') }}/${id}`,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    timeOffTable.ajax.reload();
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    showToast('Failed to delete time off', 'error');
                }
            });
        }
    }

    function showToast(message, type = 'success') {
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const title = type === 'success' ? 'Success' : 'Error';
        const toastHtml = `
            <div class="bs-toast toast toast-placement-ex m-2 top-0 end-0 fade show ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="bx bx-bell me-2"></i>
                    <div class="me-auto fw-semibold">${title}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body text-white">
                    ${message}
                </div>
            </div>
        `;
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        container.innerHTML = toastHtml;
        const toastEl = container.querySelector('.toast');
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }
</script>
@endpush
