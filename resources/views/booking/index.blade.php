@extends('template.app')

@section('title', 'Booking Management')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Booking Management</h5>
                <button type="button" class="btn btn-primary" onclick="showCreateModal()">Add Booking</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered w-100" id="bookingTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Action</th>
                                <th>Booking #</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Treatment</th>
                                <th>Staff</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="bookingForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bookingId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select id="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="treatment_id" class="form-label">Treatment</label>
                            <select id="treatment_id" class="form-select" required>
                                <option value="">Select Treatment</option>
                                @foreach($treatments as $treatment)
                                    <option value="{{ $treatment->id }}" data-price="{{ $treatment->price }}">{{ $treatment->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="staff_id" class="form-label">Staff (Optional)</label>
                            <select id="staff_id" class="form-select">
                                <option value="">Any Staff</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="booking_date" class="form-label">Date</label>
                            <input type="date" id="booking_date" class="form-control" onchange="loadAvailableTimes()" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="start_time" class="form-label">Available Time</label>
                            <select id="start_time" class="form-select" required>
                                <option value="">Select Time</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="discount" class="form-label">Discount Amount (Rp)</label>
                            <input type="number" id="discount" class="form-control" placeholder="0" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" class="form-control" rows="2" placeholder="Booking notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Save Booking</button>
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
    let table;
    let modal;

    $(document).ready(function() {
        modal = new bootstrap.Modal(document.getElementById('bookingModal'));

        table = $('#bookingTable').DataTable({
            responsive: true,
            ordering: false,
            serverSide: true,
            processing: true,
            ajax: {
                url: "{{ route('booking.load_data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: "text-center" },
                { data: 'aksi', name: 'aksi', className: "text-center", width: "80px" },
                { data: 'booking_number', name: 'booking_number' },
                { data: 'booking_date', name: 'booking_date' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'treatment_name', name: 'treatment_name' },
                { data: 'staff_name', name: 'staff_name', defaultContent: '-' },
                { data: 'status', name: 'status' },
            ],
        });

        $('#bookingForm').on('submit', saveBooking);

        $('#treatment_id, #staff_id').on('change', loadAvailableTimes);
    });

    function showCreateModal() {
        $('#bookingId').val('');
        $('#customer_id').val('');
        $('#treatment_id').val('');
        $('#staff_id').val('');
        $('#booking_date').val('');
        $('#start_time').val('');
        $('#discount').val('');
        $('#notes').val('');
        $('#modalTitle').text('Add Booking');
        modal.show();
    }

    function editBooking(id) {
        $.get(`{{ url('booking') }}/${id}/edit`, function(data) {
            $('#bookingId').val(data.id);
            $('#customer_id').val(data.customer_id);
            $('#treatment_id').val(data.treatment_id);
            $('#staff_id').val(data.staff_id);
            $('#booking_date').val(data.booking_date);
            
            // Temporary set option for start_time so it can be selected before AJAX finishes
            const st = data.start_time ? data.start_time.substring(0,5) : '';
            if (st) {
                $('#start_time').html(`<option value="${st}">${st}</option>`);
                $('#start_time').val(st);
            }
            
            loadAvailableTimes(st); // pass current time to re-select after load
            
            $('#discount').val(parseInt(data.discount));
            $('#notes').val(data.notes);
            
            $('#modalTitle').text('Edit Booking');
            modal.show();
        }).fail(function() {
            showToast('Data not found', 'error');
        });
    }

    function saveBooking(e) {
        e.preventDefault();
        const id = $('#bookingId').val();
        let url = '';
        let method = 'POST';

        const data = {
            _token: "{{ csrf_token() }}",
            customer_id: $('#customer_id').val(),
            treatment_id: $('#treatment_id').val(),
            staff_id: $('#staff_id').val(),
            booking_date: $('#booking_date').val(),
            start_time: $('#start_time').val(),
            discount: $('#discount').val(),
            notes: $('#notes').val()
        };

        if (id) {
            data._method = 'PUT';
            url = `{{ url('booking') }}/${id}`;
        } else {
            url = `{{ route('booking.store') }}`;
        }

        $('#btnSave').prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(res) {
                modal.hide();
                table.ajax.reload(null, false);
                showToast(res.message, 'success');
            },
            error: function(err) {
                let msg = 'Failed to save data';
                if (err.responseJSON && err.responseJSON.errors) {
                    msg = Object.values(err.responseJSON.errors).map(e => e.join('<br>')).join('<br>');
                } else if (err.responseJSON && err.responseJSON.message) {
                    msg = err.responseJSON.message;
                }
                showToast(msg, 'error');
            },
            complete: function() {
                $('#btnSave').prop('disabled', false).text('Save Booking');
            }
        });
    }

    function updateStatus(id, status) {
        let confirmMsg = `Are you sure you want to change status to ${status.replace('_', ' ').toUpperCase()}?`;
        if (confirm(confirmMsg)) {
            $.ajax({
                url: `{{ url('booking') }}/${id}/status`,
                type: 'PATCH',
                data: {
                    _token: "{{ csrf_token() }}",
                    status: status
                },
                success: function(res) {
                    table.ajax.reload(null, false);
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    let msg = 'Failed to update status';
                    if(err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
                    showToast(msg, 'error');
                }
            });
        }
    }

    function loadAvailableTimes(selectedTime = null) {
        const treatmentId = $('#treatment_id').val();
        const staffId = $('#staff_id').val();
        const date = $('#booking_date').val();

        if (!treatmentId || !date) {
            $('#start_time').html('<option value="">Select Time</option>');
            return;
        }

        $('#start_time').html('<option value="">Loading...</option>');

        $.ajax({
            url: "{{ route('booking.get_available_times') }}",
            type: "GET",
            data: {
                treatment_id: treatmentId,
                staff_id: staffId,
                date: date
            },
            success: function(times) {
                let options = '<option value="">Select Time</option>';
                if (times.length === 0) {
                    options = '<option value="">No Slots Available</option>';
                } else {
                    times.forEach(t => {
                        options += `<option value="${t}">${t}</option>`;
                    });
                }
                
                // If editing and selectedTime is not in the array, push it so we don't lose existing data
                if (selectedTime && !times.includes(selectedTime)) {
                    options += `<option value="${selectedTime}">${selectedTime} (Current)</option>`;
                }

                $('#start_time').html(options);
                
                if (selectedTime) {
                    $('#start_time').val(selectedTime);
                }
            },
            error: function() {
                $('#start_time').html('<option value="">Error Loading</option>');
            }
        });
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
