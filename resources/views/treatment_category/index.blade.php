@extends('template.app')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Treatment Categories</h5>
                <button type="button" class="btn btn-primary" onclick="showCreateModal()">Add Category</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="categoryTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Action</th>
                                <th>Name</th>
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
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="categoryId">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" id="name" class="form-control" placeholder="e.g. Facial" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
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

<!-- Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let table;
    let modal;

    $(document).ready(function() {
        modal = new bootstrap.Modal(document.getElementById('categoryModal'));

        table = $('#categoryTable').DataTable({
            responsive: true,
            ordering: false,
            serverSide: true,
            processing: true,
            lengthMenu: [10, 50],
            ajax: {
                "url": "{{ route('treatment.categories.load_data') }}",
                "type": "POST",
                "data": function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    width: 100,
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name',
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
            ],
        });
    });

    function showCreateModal() {
        $('#categoryId').val('');
        $('#name').val('');
        $('#is_active').prop('checked', true);
        $('#modalTitle').text('Add Category');
        modal.show();
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

    function editCategory(id) {
        $.get(`{{ url('treatment/categories') }}/${id}/edit`, function(data) {
            $('#categoryId').val(data.id);
            $('#name').val(data.name);
            $('#is_active').prop('checked', data.is_active == 1);
            $('#modalTitle').text('Edit Category');
            modal.show();
        }).fail(function() {
            showToast('Data not found', 'error');
        });
    }

    function saveCategory(e) {
        e.preventDefault();
        const id = $('#categoryId').val();
        const url = id ? `{{ url('treatment/categories') }}/${id}` : `{{ route('treatment.categories.store') }}`;
        const method = id ? 'PUT' : 'POST';
        
        const data = {
            _token: "{{ csrf_token() }}",
            name: $('#name').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0
        };

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
                showToast('Failed to save data', 'error');
            },
            complete: function() {
                $('#btnSave').prop('disabled', false).text('Save');
            }
        });
    }

    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this?')) {
            $.ajax({
                url: `{{ url('treatment/categories') }}/${id}`,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    table.ajax.reload();
                    showToast(res.message, 'success');
                },
                error: function() {
                    showToast('Failed to delete data', 'error');
                }
            });
        }
    }

    function toggleStatus(id) {
        $.ajax({
            url: `{{ url('treatment/categories') }}/${id}/toggle-status`,
            type: 'PATCH',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                table.ajax.reload(null, false);
            },
            error: function() {
                table.ajax.reload(null, false);
                showToast('Failed to update status', 'error');
            }
        });
    }
</script>
@endsection
