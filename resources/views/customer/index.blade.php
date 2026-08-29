@extends('template.app')

@section('title', 'Customer Management')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customer Management</h5>
                <button type="button" class="btn btn-primary" onclick="showCreateModal()">Add Customer</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered w-100" id="customerTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Action</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Total Visits</th>
                                <th>Last Visit</th>
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
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="customerForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="customerId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" class="form-control" placeholder="e.g. Sinta" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number (WhatsApp)</label>
                            <input type="text" id="phone" class="form-control" placeholder="e.g. 08123456789" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" placeholder="e.g. sinta@email.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">Birth Date</label>
                            <input type="date" id="birth_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="notes" class="form-label">Notes (Internal)</label>
                            <textarea id="notes" class="form-control" rows="3" placeholder="e.g. Alergi obat tertentu..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Save</button>
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
        modal = new bootstrap.Modal(document.getElementById('customerModal'));

        table = $('#customerTable').DataTable({
            responsive: true,
            ordering: false,
            serverSide: true,
            processing: true,
            lengthMenu: [10, 50],
            ajax: {
                url: "{{ route('customer.load_data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: "text-center" },
                { data: 'aksi', name: 'aksi', className: "text-center", width: "120px" },
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'total_visits', name: 'total_visits', className: "text-center" },
                { data: 'last_visit_at', name: 'last_visit_at', className: "text-center" },
            ],
        });

        $('#customerForm').on('submit', saveCustomer);
    });

    function showCreateModal() {
        $('#customerId').val('');
        $('#name').val('');
        $('#phone').val('');
        $('#email').val('');
        $('#gender').val('');
        $('#birth_date').val('');
        $('#address').val('');
        $('#notes').val('');
        $('#modalTitle').text('Add Customer');
        modal.show();
    }

    function editCustomer(id) {
        $.get(`{{ url('customer') }}/${id}/edit`, function(data) {
            $('#customerId').val(data.id);
            $('#name').val(data.name);
            $('#phone').val(data.phone);
            $('#email').val(data.email);
            $('#gender').val(data.gender);
            $('#birth_date').val(data.birth_date);
            $('#address').val(data.address);
            $('#notes').val(data.notes);
            
            $('#modalTitle').text('Edit Customer');
            modal.show();
        }).fail(function() {
            showToast('Data not found', 'error');
        });
    }

    function saveCustomer(e) {
        e.preventDefault();
        const id = $('#customerId').val();
        let url = '';
        let method = 'POST';

        const data = {
            _token: "{{ csrf_token() }}",
            name: $('#name').val(),
            phone: $('#phone').val(),
            email: $('#email').val(),
            gender: $('#gender').val(),
            birth_date: $('#birth_date').val(),
            address: $('#address').val(),
            notes: $('#notes').val()
        };

        if (id) {
            data._method = 'PUT';
            url = `{{ url('customer') }}/${id}`;
        } else {
            url = `{{ route('customer.store') }}`;
        }

        $('#btnSave').prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(res) {
                modal.hide();
                table.ajax.reload();
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
                $('#btnSave').prop('disabled', false).text('Save');
            }
        });
    }

    function deleteCustomer(id) {
        if (confirm('Yakin ingin menghapus pelanggan ini?')) {
            $.ajax({
                url: `{{ url('customer') }}/${id}`,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    table.ajax.reload();
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    showToast('Failed to delete data', 'error');
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
