@extends('template.app')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Treatments Management</h5>
                <button type="button" class="btn btn-primary" onclick="showCreateModal()">Add Treatment</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="treatmentTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Action</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Duration</th>
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
<div class="modal fade" id="treatmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="treatmentForm" onsubmit="saveTreatment(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Treatment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="treatmentId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Treatment Name</label>
                            <input type="text" id="name" class="form-control" placeholder="e.g. Facial Acne" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (Rp)</label>
                            <input type="text" id="price" class="form-control" placeholder="e.g. 150.000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="duration_minutes" class="form-label">Duration (Minutes)</label>
                            <input type="number" id="duration_minutes" class="form-control" min="1" placeholder="e.g. 60" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="thumbnail" class="form-label fw-semibold">Thumbnail Utama</label>
                            <input type="file" id="thumbnail" class="form-control" accept="image/*">
                            <small class="text-muted d-block mt-1">Pilih file baru jika ingin mengganti thumbnail.</small>
                            <div id="thumbnailPreview" class="mt-2 position-relative d-none">
                                <img src="" id="thumbnailImg" class="img-thumbnail rounded shadow-sm" style="max-height: 100px; object-fit: cover;">
                                <button type="button" onclick="deleteThumbnail()" id="btnDeleteThumbnail" class="btn btn-danger btn-sm position-absolute top-0 start-0 m-1 rounded-circle p-0 align-items-center justify-content-center" style="width:24px; height:24px; display:none;" title="Hapus Thumbnail">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Sub Images / Galeri (Maksimal 3)</label>
                            <div id="existingSubImages" class="d-flex gap-2 mb-2 flex-wrap"></div>
                            <div id="subImagesContainer">
                                <div class="input-group mb-2">
                                    <input type="file" class="form-control sub-image-input" accept="image/*">
                                    <button class="btn btn-outline-primary" type="button" onclick="addSubImageInput()">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" id="sort_order" class="form-control" value="0">
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
        modal = new bootstrap.Modal(document.getElementById('treatmentModal'));

        table = $('#treatmentTable').DataTable({
            responsive: true,
            ordering: false,
            serverSide: true,
            processing: true,
            lengthMenu: [10, 50],
            ajax: {
                "url": "{{ route('treatment.list.load_data') }}",
                "type": "POST",
                "data": function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: "text-center", orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', width: 100, className: "text-center", orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'price', name: 'price' },
                { data: 'duration_minutes', name: 'duration_minutes' },
                { data: 'is_active', name: 'is_active', className: "text-center", orderable: false, searchable: false },
            ],
        });
        $('#price').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value) {
                $(this).val(parseInt(value, 10).toLocaleString('id-ID'));
            } else {
                $(this).val('');
            }
        });

        $('#thumbnail').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    showThumbnailPreview(e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                showThumbnailPreview(null);
            }
        });
    });

    function addSubImageInput() {
        const container = document.getElementById('subImagesContainer');
        const existingCount = $('#existingSubImages').children().length;
        const currentInputCount = container.querySelectorAll('.input-group').length;
        
        if (existingCount + currentInputCount >= 3) {
            showToast('Maksimal total 3 sub image (tersimpan + baru).', 'error');
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="file" class="form-control sub-image-input" accept="image/*">
            <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">-</button>
        `;
        container.appendChild(div);
    }

    function resetSubImages() {
        $('#existingSubImages').html('');
        $('#subImagesContainer').html(`
            <div class="input-group mb-2">
                <input type="file" class="form-control sub-image-input" accept="image/*">
                <button class="btn btn-outline-primary" type="button" onclick="addSubImageInput()">+</button>
            </div>
        `);
    }

    function renderExistingSubImages(images) {
        if (!images || images.length === 0) {
            $('#existingSubImages').html('');
            return;
        }
        let html = '';
        images.forEach(img => {
            html += `
                <div class="position-relative d-inline-block" id="sub-img-card-${img.id}">
                    <img src="{{ asset('storage') }}/${img.image_path}" class="img-thumbnail rounded shadow-sm" style="max-height: 80px; width: 80px; object-fit: cover;">
                    <button type="button" onclick="deleteSubImage('${img.id}')" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:22px; height:22px;" title="Hapus Gambar">
                        <i class="bx bx-x" style="font-size:14px;"></i>
                    </button>
                </div>
            `;
        });
        $('#existingSubImages').html(html);
    }

    function deleteSubImage(imageId) {
        if (!confirm('Hapus gambar ini secara permanen?')) return;
        $.ajax({
            url: `{{ url('treatment/list/sub-image') }}/${imageId}`,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                showToast(res.message, 'success');
                $(`#sub-img-card-${imageId}`).fadeOut(300, function() { $(this).remove(); });
            },
            error: function() {
                showToast('Gagal menghapus gambar', 'error');
            }
        });
    }

    function showThumbnailPreview(src) {
        if (src) {
            $('#thumbnailImg').attr('src', src);
            $('#btnDeleteThumbnail').css('display', 'flex');
            $('#thumbnailPreview').removeClass('d-none').addClass('d-inline-block').show();
        } else {
            $('#thumbnailImg').attr('src', '');
            $('#btnDeleteThumbnail').hide();
            $('#thumbnailPreview').removeClass('d-inline-block').addClass('d-none').hide();
        }
    }

    function deleteThumbnail() {
        const id = $('#treatmentId').val();
        if (!id) {
            $('#thumbnail').val('');
            showThumbnailPreview(null);
            return;
        }
        if (!confirm('Hapus thumbnail ini secara permanen?')) return;
        $.ajax({
            url: `{{ url('treatment/list') }}/${id}/thumbnail`,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                showToast(res.message, 'success');
                $('#thumbnail').val('');
                showThumbnailPreview(null);
                table.ajax.reload();
            },
            error: function() {
                showToast('Gagal menghapus thumbnail', 'error');
            }
        });
    }

    function showCreateModal() {
        $('#treatmentId').val('');
        $('#name').val('');
        $('#category_id').val('');
        $('#price').val('');
        $('#duration_minutes').val('');
        $('#description').val('');
        $('#sort_order').val('0');
        $('#thumbnail').val('');
        showThumbnailPreview(null);
        resetSubImages();
        $('#subImagesPreview').hide().html('');
        $('#is_active').prop('checked', true);
        $('#modalTitle').text('Add Treatment');
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

    function editTreatment(id) {
        $.get(`{{ url('treatment/list') }}/${id}/edit`, function(data) {
            $('#treatmentId').val(data.id);
            $('#name').val(data.name);
            $('#category_id').val(data.category_id);
            $('#price').val(parseInt(data.price).toLocaleString('id-ID'));
            $('#duration_minutes').val(data.duration_minutes);
            $('#description').val(data.description);
            $('#sort_order').val(data.sort_order);
            $('#thumbnail').val('');
            if (data.image) {
                showThumbnailPreview(`{{ asset('storage') }}/${data.image}`);
            } else {
                showThumbnailPreview(null);
            }

            resetSubImages();
            renderExistingSubImages(data.sub_images);
            $('#is_active').prop('checked', data.is_active == 1);
            $('#modalTitle').text('Edit Treatment');
            modal.show();
        }).fail(function() {
            showToast('Data not found', 'error');
        });
    }

    function saveTreatment(e) {
        e.preventDefault();
        const id = $('#treatmentId').val();
        let url = '';
        let method = 'POST';

        const formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('name', $('#name').val());
        formData.append('category_id', $('#category_id').val());
        formData.append('price', $('#price').val().replace(/\./g, ''));
        formData.append('duration_minutes', $('#duration_minutes').val());
        formData.append('description', $('#description').val());
        formData.append('sort_order', $('#sort_order').val());
        formData.append('is_active', $('#is_active').is(':checked') ? '1' : '0');

        const thumbnail = $('#thumbnail')[0].files[0];
        if (thumbnail) {
            formData.append('thumbnail', thumbnail);
        }

        const subImageInputs = document.querySelectorAll('.sub-image-input');
        let fileCount = 0;
        subImageInputs.forEach(input => {
            if (input.files[0]) {
                fileCount++;
            }
        });
        
        if (fileCount > 3) {
            showToast('Maksimal 3 sub image diperbolehkan.', 'error');
            return;
        }
        
        subImageInputs.forEach(input => {
            if (input.files[0]) {
                formData.append('sub_images[]', input.files[0]);
            }
        });

        if (id) {
            formData.append('_method', 'PUT');
            url = `{{ url('treatment/list') }}/${id}`;
        } else {
            url = `{{ route('treatment.list.store') }}`;
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

    function deleteTreatment(id) {
        if (confirm('Are you sure you want to delete this?')) {
            $.ajax({
                url: `{{ url('treatment/list') }}/${id}`,
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
            url: `{{ url('treatment/list') }}/${id}/toggle-status`,
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
