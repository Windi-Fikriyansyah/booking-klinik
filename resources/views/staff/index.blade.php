@extends('template.app')

@section('title', 'Staff Management')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Staff Management</h5>
                <button type="button" class="btn btn-primary" onclick="showCreateModal()">Add Staff</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="staffTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Action</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Specialization</th>
                                <th>Treatments</th>
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
<div class="modal fade" id="staffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="staffForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="staffId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" id="name" class="form-control" placeholder="Contoh: dr. Amanda" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" class="form-select">
                                <option value="">Pilih Role</option>
                                <option value="Doctor">Doctor</option>
                                <option value="Beautician">Beautician</option>
                                <option value="Therapist">Therapist</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor HP</label>
                            <input type="text" id="phone" class="form-control" placeholder="08123456789">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label fw-semibold">Foto</label>
                            <input type="file" id="photo" class="form-control" accept="image/*">
                            <small class="text-muted d-block mt-1">Pilih file baru jika ingin mengganti foto.</small>
                            <div id="photoPreview" class="mt-2 position-relative d-none">
                                <img src="" id="photoImg" class="img-thumbnail rounded shadow-sm" style="max-height: 100px; object-fit: cover;">
                                <button type="button" onclick="deletePhoto()" id="btnDeletePhoto" class="btn btn-danger btn-sm position-absolute top-0 start-0 m-1 rounded-circle p-0 align-items-center justify-content-center" style="width:24px; height:24px; display:none;" title="Hapus Foto">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Spesialisasi</label>
                            <input type="text" id="specialization" class="form-control" placeholder="Contoh: Aesthetic Medicine">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Treatments yang Ditangani</label>
                            <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                <div class="row">
                                    @foreach($treatments as $treatment)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input treatment-checkbox" type="checkbox" value="{{ $treatment->id }}" id="treatment_{{ $treatment->id }}">
                                            <label class="form-check-label" for="treatment_{{ $treatment->id }}">
                                                {{ $treatment->name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">Centang treatment yang dapat ditangani oleh staf ini.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="bio" class="form-label">Bio / Deskripsi</label>
                            <textarea id="bio" class="form-control" rows="3" placeholder="Informasi singkat tentang staff"></textarea>
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
@endsection

@push('js')
<script>
    let table;
    let modal;

    $(document).ready(function() {
        modal = new bootstrap.Modal(document.getElementById('staffModal'));

        table = $('#staffTable').DataTable({
            responsive: true,
            ordering: false,
            serverSide: true,
            processing: true,
            lengthMenu: [10, 50],
            ajax: {
                url: "{{ route('staff.list.load_data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: "text-center", orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', width: 100, className: "text-center", orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'role', name: 'role' },
                { data: 'specialization', name: 'specialization' },
                { data: 'treatments', name: 'treatments' },
                { data: 'is_active', name: 'is_active', className: "text-center", orderable: false, searchable: false },
            ],
        });

        $('#staffForm').on('submit', saveStaff);

        $('#photo').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    showPhotoPreview(e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                showPhotoPreview(null);
            }
        });
    });

    function showPhotoPreview(src) {
        if (src) {
            $('#photoImg').attr('src', src);
            $('#btnDeletePhoto').css('display', 'flex');
            $('#photoPreview').removeClass('d-none').addClass('d-inline-block').show();
        } else {
            $('#photoImg').attr('src', '');
            $('#btnDeletePhoto').hide();
            $('#photoPreview').removeClass('d-inline-block').addClass('d-none').hide();
        }
    }

    function deletePhoto() {
        const id = $('#staffId').val();
        if (!id) {
            $('#photo').val('');
            showPhotoPreview(null);
            return;
        }
        if (!confirm('Hapus foto ini secara permanen?')) return;
        $.ajax({
            url: `{{ url('staff/list') }}/${id}/photo`,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                showToast(res.message, 'success');
                $('#photo').val('');
                showPhotoPreview(null);
                table.ajax.reload();
            },
            error: function() {
                showToast('Gagal menghapus foto', 'error');
            }
        });
    }

    function showCreateModal() {
        $('#staffId').val('');
        $('#name').val('');
        $('#role').val('');
        $('#phone').val('');
        $('#email').val('');
        $('#specialization').val('');
        $('#bio').val('');
        $('.treatment-checkbox').prop('checked', false);
        $('#photo').val('');
        showPhotoPreview(null);
        $('#is_active').prop('checked', true);
        $('#modalTitle').text('Add Staff');
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

    function editStaff(id) {
        $.get(`{{ url('staff/list') }}/${id}/edit`, function(data) {
            $('#staffId').val(data.id);
            $('#name').val(data.name);
            $('#role').val(data.role);
            $('#phone').val(data.phone);
            $('#email').val(data.email);
            $('#specialization').val(data.specialization);
            $('#bio').val(data.bio);
            
            $('.treatment-checkbox').prop('checked', false);
            if (data.treatments && data.treatments.length > 0) {
                data.treatments.forEach(t_id => {
                    $(`#treatment_${t_id}`).prop('checked', true);
                });
            }

            $('#photo').val('');
            
            if (data.photo) {
                showPhotoPreview(`{{ asset('storage') }}/${data.photo}`);
            } else {
                showPhotoPreview(null);
            }

            $('#is_active').prop('checked', data.is_active == 1);
            $('#modalTitle').text('Edit Staff');
            modal.show();
        }).fail(function() {
            showToast('Data not found', 'error');
        });
    }

    function saveStaff(e) {
        e.preventDefault();
        const id = $('#staffId').val();
        let url = '';
        let method = 'POST';

        const formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('name', $('#name').val());
        formData.append('role', $('#role').val());
        formData.append('phone', $('#phone').val());
        formData.append('email', $('#email').val());
        formData.append('specialization', $('#specialization').val());
        formData.append('bio', $('#bio').val());
        
        $('.treatment-checkbox:checked').each(function() {
            formData.append('treatments[]', $(this).val());
        });
        
        formData.append('is_active', $('#is_active').is(':checked') ? '1' : '0');

        const photo = $('#photo')[0].files[0];
        if (photo) {
            formData.append('photo', photo);
        }

        if (id) {
            formData.append('_method', 'PUT');
            url = `{{ url('staff/list') }}/${id}`;
        } else {
            url = `{{ route('staff.list.store') }}`;
        }

        $('#btnSave').prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
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

    function deleteStaff(id) {
        if (confirm('Yakin ingin menghapus data ini?')) {
            $.ajax({
                url: `{{ url('staff/list') }}/${id}`,
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

    function toggleStatus(id) {
        $.ajax({
            url: `{{ url('staff/list') }}/${id}/toggle-status`,
            type: 'PATCH',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                showToast(res.message, 'success');
                table.ajax.reload(null, false);
            },
            error: function(err) {
                showToast('Failed to update status', 'error');
                table.ajax.reload(null, false);
            }
        });
    }
</script>
@endpush
