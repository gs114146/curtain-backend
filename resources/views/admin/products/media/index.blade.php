@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Product Media Management</h4>
                        <p class="text-muted mb-0">Manage images and videos for: <strong>{{ $product->name }}</strong></p>
                    </div>
                    <div>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="icon-base bx bx-arrow-back me-1"></i>
                            Back to Products
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="icon-base bx bx-upload me-1"></i>
                            Upload Media
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media Grid -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Media Files</h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="mediaGrid">
                            @forelse($media as $item)
                                <div class="col-md-3 col-sm-6 mb-4" data-id="{{ $item->id }}">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            @if ($item->file_type === 'image')
                                                <img src="{{ asset('storage/' . $item->file_path) }}" class="card-img-top"
                                                    alt="{{ $item->alt_text }}" style="height: 200px; object-fit: cover;">
                                            @else
                                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                                                    style="height: 200px;">
                                                    <i class="icon-base bx bx-video text-muted"
                                                        style="font-size: 3rem;"></i>
                                                </div>
                                            @endif

                                            @if ($item->is_primary)
                                                <span
                                                    class="position-absolute top-0 start-0 badge bg-primary m-2">Primary</span>
                                            @endif

                                            <div class="position-absolute top-0 end-0 m-2">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                                        data-bs-toggle="dropdown">
                                                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item btn-set-primary" href="#"
                                                                data-id="{{ $item->id }}">
                                                                <i class="icon-base bx bx-star me-1"></i>Set as Primary
                                                            </a></li>
                                                        <li><a class="dropdown-item btn-edit-media" href="#"
                                                                data-id="{{ $item->id }}">
                                                                <i class="icon-base bx bx-edit me-1"></i>Edit Details
                                                            </a></li>
                                                        <li><a class="dropdown-item btn-delete-media text-danger"
                                                                href="#" data-id="{{ $item->id }}">
                                                                <i class="icon-base bx bx-trash me-1"></i>Delete
                                                            </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-1">{{ $item->file_name }}</h6>
                                            <p class="card-text small text-muted mb-2">
                                                {{ $item->file_type === 'image' ? 'Image' : 'Video' }} •
                                                {{ number_format($item->file_size / 1024, 1) }} KB
                                            </p>
                                            @if ($item->alt_text)
                                                <p class="card-text small">{{ $item->alt_text }}</p>
                                            @endif
                                            @if ($item->caption)
                                                <p class="card-text small text-muted">{{ $item->caption }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span
                                                    class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                                <small class="text-muted">Order: {{ $item->sort_order }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="icon-base bx bx-images text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">No media files</h5>
                                        <p class="text-muted">Upload images or videos for this product</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Select Files <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file"
                                accept="image/*,video/*" required>
                            <div class="form-text">Supported formats: JPG, PNG, GIF, SVG, MP4, AVI, MOV, WMV (Max: 10MB)
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="file_type" class="form-label">File Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="file_type" name="file_type" required>
                                    <option value="">Select Type</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order"
                                    min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="alt_text" class="form-label">Alt Text</label>
                            <input type="text" class="form-control" id="alt_text" name="alt_text"
                                placeholder="Alternative text for accessibility">
                        </div>
                        <div class="mb-3">
                            <label for="caption" class="form-label">Caption</label>
                            <textarea class="form-control" id="caption" name="caption" rows="2" placeholder="Optional caption"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary"
                                value="1">
                            <label class="form-check-label" for="is_primary">
                                Set as primary media
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Media Modal -->
    <div class="modal fade" id="editMediaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Media Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editMediaForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_media_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_alt_text" class="form-label">Alt Text</label>
                            <input type="text" class="form-control" id="edit_alt_text" name="alt_text">
                        </div>
                        <div class="mb-3">
                            <label for="edit_caption" class="form-label">Caption</label>
                            <textarea class="form-control" id="edit_caption" name="caption" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="edit_sort_order" name="sort_order"
                                    min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_primary" name="is_primary"
                                value="1">
                            <label class="form-check-label" for="edit_is_primary">
                                Set as primary media
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteMediaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this media file? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteMedia">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let deleteMediaId = null;

            // Upload form submission
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('product_id', {{ $product->id }});

                const submitBtn = $(this).find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');

                spinner.removeClass('d-none');
                submitBtn.prop('disabled', true);

                $.ajax({
                    url: '{{ route('admin.product-media.store') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#uploadModal').modal('hide');
                        $('#uploadForm')[0].reset();
                        location.reload(); // Reload to show new media
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            // Handle validation errors
                            console.log(errors);
                        }
                    },
                    complete: function() {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                });
            });

            // Edit media button click
            $(document).on('click', '.btn-edit-media', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: '{{ route('admin.product-media.show', '') }}/' + id,
                    method: 'GET',
                    success: function(response) {
                        $('#edit_media_id').val(response.id);
                        $('#edit_alt_text').val(response.alt_text);
                        $('#edit_caption').val(response.caption);
                        $('#edit_sort_order').val(response.sort_order);
                        $('#edit_status').val(response.status);
                        $('#edit_is_primary').prop('checked', response.is_primary);
                        $('#editMediaModal').modal('show');
                    }
                });
            });

            // Edit media form submission
            $('#editMediaForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = $('#edit_media_id').val();

                const submitBtn = $(this).find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');

                spinner.removeClass('d-none');
                submitBtn.prop('disabled', true);

                $.ajax({
                    url: '{{ route('admin.product-media.update', '') }}/' + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#editMediaModal').modal('hide');
                        location.reload();
                    },
                    complete: function() {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                });
            });

            // Delete media button click
            $(document).on('click', '.btn-delete-media', function() {
                deleteMediaId = $(this).data('id');
                $('#deleteMediaModal').modal('show');
            });

            // Confirm delete
            $('#confirmDeleteMedia').on('click', function() {
                const submitBtn = $(this);
                const spinner = submitBtn.find('.spinner-border');

                spinner.removeClass('d-none');
                submitBtn.prop('disabled', true);

                $.ajax({
                    url: '{{ route('admin.product-media.destroy', '') }}/' + deleteMediaId,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#deleteMediaModal').modal('hide');
                        location.reload();
                    },
                    complete: function() {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                });
            });

            // Set as primary
            $(document).on('click', '.btn-set-primary', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: '{{ route('admin.product-media.update', '') }}/' + id,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'PUT',
                        is_primary: 1
                    },
                    success: function(response) {
                        location.reload();
                    }
                });
            });
        });
    </script>
@endpush
