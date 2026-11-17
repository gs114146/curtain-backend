@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Product</h5>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Products
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="editProductForm" action="{{ route('admin.products.update', $product->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit_id" name="id" value="{{ $product->id }}">
                            @if ($product->variants && $product->variants->count() > 0)
                                @foreach ($product->variants as $preserveVariant)
                                    <input type="hidden" name="variants[keep_{{ $preserveVariant->id }}][id]"
                                        value="{{ $preserveVariant->id }}">
                                @endforeach
                            @endif

                            @php $isVariantProduct = $isVariantProduct ?? ($product->variants && $product->variants->count() > 0); @endphp

                            @if ($isVariantProduct)
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    This is a <strong>Multi-Color Variant Product</strong> with
                                    {{ $product->variants->count() }} color variants.
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    This is a <strong>Single Color Product</strong>.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_name" class="form-label">Hanger Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" name="name"
                                        value="{{ $product->name }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control" id="edit_sku" name="sku"
                                        value="{{ $product->sku }}" readonly>
                                    <div class="form-text">
                                        <i class="bx bx-info-circle text-info"></i>
                                        SKU cannot be changed after product creation
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_manual_product_id" class="form-label">Hanger ID</label>
                                    <input type="text" class="form-control" id="edit_manual_product_id"
                                        name="manual_product_id" value="{{ $product->manual_product_id }}"
                                        placeholder="Enter hanger ID">
                                    <div class="form-text">
                                        <i class="bx bx-info-circle text-info"></i>
                                        Enter a unique hanger ID
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Product Image</label>
                                    <div class="product-image-upload p-3 rounded border d-flex align-items-center justify-content-center"
                                        style="cursor:pointer; min-height:120px; background-color: #f8f9fa;">
                                        <div class="text-center">
                                            <i class="bx bx-cloud-upload me-2" style="font-size: 24px; color: #6c757d;"></i>
                                            <div>Click to upload image or drag & drop</div>
                                            <small class="text-muted">PNG, JPG, GIF up to 8MB</small>
                                        </div>
                                        <input type="file" id="productImageInput" name="image" accept="image/*"
                                            style="display: none;">
                                    </div>
                                    <div class="product-image-error text-danger small mt-1" style="display:none;"></div>
                                    <div class="product-image-preview d-flex gap-2 flex-wrap mt-2">
                                        @if ($product->image)
                                            <div class="product-image-item"
                                                style="position:relative;width:120px;height:120px;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;">
                                                <img src="{{ asset($product->image) }}" alt="Current Image"
                                                    class="js-image-preview"
                                                    title="Click to preview"
                                                    style="width:100%;height:100%;object-fit:cover;display:block;cursor:pointer;">
                                                <button type="button" class="product-image-remove" title="Remove"
                                                    style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,0.95);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer;">
                                                    <i class="bx bx-x" style="line-height:1;"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="edit_description" class="form-label">Hanger Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="3">{{ $product->description }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_dimensions" class="form-label">Dimensions <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_dimensions" name="dimensions"
                                        placeholder="e.g., 120cm x 150cm" value="{{ $product->dimensions }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_weight" class="form-label">Weight <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_weight" name="weight"
                                        placeholder="e.g., 2.5kg" value="{{ $product->weight }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_horizontal_repeat" class="form-label">Horizontal Repeat</label>
                                    <input type="text" class="form-control" id="edit_horizontal_repeat"
                                        name="horizontal_repeat" placeholder="Enter horizontal repeat"
                                        value="{{ $product->horizontal_repeat }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_vertical_repeat" class="form-label">Vertical Repeat</label>
                                    <input type="text" class="form-control" id="edit_vertical_repeat"
                                        name="vertical_repeat" placeholder="Enter vertical repeat"
                                        value="{{ $product->vertical_repeat }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_direction" class="form-label">Direction</label>
                                    <input type="text" class="form-control" id="edit_direction" name="direction"
                                        placeholder="Enter direction" value="{{ $product->direction }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_extra_data" class="form-label">Extra Data</label>
                                    <textarea class="form-control" id="edit_extra_data" name="extra_data" rows="2"
                                        placeholder="Enter extra data">{{ $product->extra_data }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_design_ids" class="form-label">Design/Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_design_ids" name="design_ids[]" multiple
                                        required>
                                        @foreach ($designs as $design)
                                            <option value="{{ $design->id }}"
                                                {{ $product->relationLoaded('designs') && $product->getRelation('designs')->contains($design->id) ? 'selected' : '' }}>
                                                {{ $design->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_material_ids" class="form-label">Materials <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_material_ids" name="material_ids[]" multiple
                                        required>
                                        @foreach ($materials as $material)
                                            <option value="{{ $material->id }}"
                                                {{ $product->relationLoaded('materials') && $product->getRelation('materials')->contains($material->id) ? 'selected' : '' }}>
                                                {{ $material->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_transparency_id" class="form-label">Transparency</label>
                                    <select class="form-select" id="edit_transparency_id" name="transparency_id">
                                        <option value="">Select Transparency</option>
                                        @foreach ($transparencies as $transparency)
                                            <option value="{{ $transparency->id }}"
                                                {{ $product->transparency_id == $transparency->id ? 'selected' : '' }}>
                                                {{ $transparency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_characteristic_ids" class="form-label">Characteristics <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_characteristic_ids" name="characteristic_ids[]"
                                        multiple required>
                                        @foreach ($characteristics as $characteristic)
                                            <option value="{{ $characteristic->id }}"
                                                {{ $product->relationLoaded('characteristics') && $product->getRelation('characteristics')->contains($characteristic->id) ? 'selected' : '' }}>
                                                {{ $characteristic->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_usage_ids" class="form-label">Usage <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_usage_ids" name="usage_ids[]" multiple
                                        required>
                                        @foreach ($usage as $usageItem)
                                            <option value="{{ $usageItem->id }}"
                                                {{ $product->relationLoaded('usages') && $product->getRelation('usages')->contains($usageItem->id) ? 'selected' : '' }}>
                                                {{ $usageItem->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="edit_price" name="price"
                                            step="0.01" min="0" value="{{ $product->price }}">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_sale_price" class="form-label">Sale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="edit_sale_price"
                                            name="sale_price" step="0.01" min="0"
                                            value="{{ $product->sale_price }}">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_stock_quantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="edit_stock_quantity"
                                        name="stock_quantity" min="0" value="{{ $product->stock_quantity }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_types" class="form-label">Product Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_types" name="types[]" multiple required>
                                        @php $selectedTypes = $product->typeValues(); @endphp
                                        <option value="product_listing"
                                            {{ in_array('product_listing', $selectedTypes) ? 'selected' : '' }}>Product Listing
                                        </option>
                                        <option value="view_qualities"
                                            {{ in_array('view_qualities', $selectedTypes) ? 'selected' : '' }}>View Qualities
                                        </option>
                                        <option value="upholstery"
                                            {{ in_array('upholstery', $selectedTypes) ? 'selected' : '' }}>
                                            Upholstery</option>
                                        <option value="latest"
                                            {{ in_array('latest', $selectedTypes) ? 'selected' : '' }}>
                                            Latest</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_product_base" class="form-label">Product Base <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_product_base" name="product_base" required>
                                        <option value="curtain"
                                            {{ $product->product_base == 'curtain' || !$product->product_base ? 'selected' : '' }}>Curtain</option>
                                        <option value="upholstery"
                                            {{ $product->product_base == 'upholstery' ? 'selected' : '' }}>Upholstery
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status">
                                        <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="inactive" {{ $product->status == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                        <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }}>Draft
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">In Stock</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_in_stock"
                                            name="in_stock" value="1" {{ $product->in_stock ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_compositions" class="form-label">Compositions</label>
                                    <textarea class="form-control" id="edit_compositions" name="compositions" rows="2"
                                        placeholder="Enter compositions">{{ $product->compositions }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_hsn_code" class="form-label">HSN Code</label>
                                    <input type="text" class="form-control" id="edit_hsn_code" name="hsn_code"
                                        value="{{ $product->hsn_code }}" placeholder="Enter HSN code">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_gst" class="form-label">GST (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="edit_gst" name="gst"
                                            step="0.01" min="0" max="100" value="{{ $product->gst }}"
                                            placeholder="Enter GST percentage">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>

                            @if (!$isVariantProduct)
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_color_id" class="form-label">Color <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_color_id" name="color_id" required>
                                            @foreach ($colors as $color)
                                                <option value="{{ $color->id }}"
                                                    {{ $product->color_id == $color->id ? 'selected' : '' }}>
                                                    {{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if ($isVariantProduct)
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Product Variants</h6>
                                            </div>
                                            <div class="card-body" id="variantsContainer">
                                                @foreach ($product->variants as $index => $variant)
                                                    @php
                                                        $idx = $index + 1;
                                                        $primaryMedia = $variant->media->first();
                                                    @endphp
                                                    <div class="variant-item card border-0 shadow-sm mb-3 existing-variant-item"
                                                        data-variant-index="{{ $idx }}" data-variant-id="{{ $variant->id }}">
                                                        <div
                                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center">
                                                                <i
                                                                    class="bx bx-grip-vertical variant-sort-handle me-2"></i>
                                                                <h6 class="variant-title mb-0">
                                                                    <span class="variant-color-preview"
                                                                        style="background-color: {{ $variant->color->color_value ?? '#e5e7eb' }};"></span>
                                                                    {{ $variant->color->name ?? 'Variant ' . $idx }}
                                                                </h6>
                                                                @if ($variant->is_default)
                                                                    <span class="default-variant-badge ms-2">Default</span>
                                                                @endif
                                                            </div>
                                                            <div class="variant-actions">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-primary set-default-variant">
                                                                    <i class="bx bx-star"></i>
                                                                </button>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-danger delete-variant"
                                                                    data-variant-id="{{ $variant->id }}">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="card-body variant-content">
                                                            <input type="hidden"
                                                                name="variants[{{ $idx }}][id]"
                                                                value="{{ $variant->id }}">
                                                            <div class="row g-3">
                                                                <div class="col-md-12">
                                                                    <label class="form-label">Color <span
                                                                            class="text-danger">*</span></label>
                                                                    <select class="form-select variant-color-select"
                                                                        name="variants[{{ $idx }}][color_id]"
                                                                        required>
                                                                        @foreach ($colors as $color)
                                                                            <option value="{{ $color->id }}"
                                                                                data-color-value="{{ $color->color_value }}"
                                                                                {{ $variant->color_id == $color->id ? 'selected' : '' }}>
                                                                                {{ $color->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Article/Color No <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text"
                                                                        class="form-control variant-article"
                                                                        name="variants[{{ $idx }}][article]"
                                                                        value="{{ $variant->article ?? '' }}"
                                                                        placeholder="Enter article/color number" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Article/Design Description
                                                                        <span class="text-danger">*</span></label>
                                                                    <textarea class="form-control variant-article-description" name="variants[{{ $idx }}][article_description]"
                                                                        placeholder="Enter article/design description" required>{{ $variant->article_description ?? '' }}</textarea>
                                                                </div>
                                                                <div class="col-12 mt-2">
                                                                    <div class="form-check form-switch">
                                                                        <input
                                                                            class="form-check-input variant-default-checkbox"
                                                                            type="checkbox"
                                                                            name="variants[{{ $idx }}][is_default]"
                                                                            value="1"
                                                                            {{ $variant->is_default ? 'checked' : '' }}>
                                                                        <label class="form-check-label ms-2">Default
                                                                            Variant</label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label">Variant Image <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="variant-image-upload p-3 rounded border d-flex align-items-center justify-content-center"
                                                                        style="cursor:pointer; min-height:60px;">
                                                                        <i class="bx bx-cloud-upload me-2"></i>
                                                                        <span>Select an image or drag & drop</span>
                                                                        <input type="file" class="variant-image-input"
                                                                            name="variants[{{ $idx }}][images][]"
                                                                            accept="image/*" style="display: none;">
                                                                    </div>
                                                                    <div class="text-muted small mt-1">PNG, JPG, GIF up to 8MB</div>
                                                                    <div class="variant-error-message text-danger small mt-1"
                                                                        style="display:none;">An image is required for this
                                                                        variant.</div>
                                                                </div>
                                                                <div class="col-md-4 d-flex align-items-center">
                                                                    <div
                                                                        class="variant-image-preview d-flex gap-2 flex-wrap mt-2 w-100 justify-content-start">
                                                                        @if ($primaryMedia)
                                                                            <div class="variant-image-item"
                                                                                style="position:relative;width:96px;height:96px;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;">
                                                                                <img src="{{ asset($primaryMedia->file_path) }}"
                                                                                    alt="Preview"
                                                                                    class="js-image-preview"
                                                                                    title="Click to preview"
                                                                                    style="width:100%;height:100%;object-fit:cover;display:block;cursor:pointer;">
                                                                                <button type="button"
                                                                                    class="variant-image-remove"
                                                                                    title="Remove"
                                                                                    style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,0.95);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                                                                    <i class="bx bx-x"
                                                                                        style="line-height:1;"></i>
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-3 mb-3 text-end">
                                                <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">
                                                    <i class="bx bx-plus"></i> Add Variant
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif



                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productImagePreviewModal" tabindex="-1" aria-labelledby="productImagePreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productImagePreviewLabel">Image preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img src="" alt="" id="productImagePreviewImg" class="img-fluid product-image-preview-full">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .product-image-upload {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .product-image-upload:hover {
            border-color: #3b82f6;
            background-color: #eff6ff !important;
        }

        .product-image-upload.dragover {
            border-color: #3b82f6;
            background-color: #dbeafe !important;
        }

        .js-image-preview {
            cursor: pointer;
        }

        .js-image-preview:hover {
            opacity: 0.92;
        }

        .product-image-preview-full {
            max-height: min(70vh, 640px);
            width: auto;
            max-width: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/product-variants.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '#editProductForm .js-image-preview', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const src = $(this).attr('src') || '';
                const title = $(this).attr('alt') || 'Image preview';
                const $modal = $('#productImagePreviewModal');

                $('#productImagePreviewImg').attr('src', src).attr('alt', title);
                $('#productImagePreviewLabel').text(title);

                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance($modal[0]).show();
                } else {
                    $modal.modal('show');
                }
            });

            // Initialize Select2 for multi-select fields on edit page
            if ($('#editProductForm').length > 0) {
                // Wait a bit to ensure DOM is fully ready and products.js has loaded
                setTimeout(function() {
                    const multiSelectFields = [
                        '#edit_design_ids',
                        '#edit_material_ids',
                        '#edit_characteristic_ids',
                        '#edit_usage_ids',
                        '#edit_types'
                    ];

                    multiSelectFields.forEach(function(selector) {
                        const $select = $(selector);
                        if ($select.length > 0) {
                            // Destroy existing Select2 instance if any
                            if ($select.hasClass('select2-hidden-accessible')) {
                                $select.select2('destroy');
                            }

                            // Get selected values from the option elements
                            const selectedValues = [];
                            $select.find('option:selected').each(function() {
                                selectedValues.push($(this).val());
                            });

                            // Initialize Select2 with Bootstrap 5 theme
                            $select.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Select options',
                                allowClear: true,
                                width: '100%',
                                closeOnSelect: false
                            });

                            // Set selected values after initialization
                            if (selectedValues.length > 0) {
                                $select.val(selectedValues).trigger('change');
                            }
                        }
                    });
                }, 300);
            }
        });
    </script>
@endpush
