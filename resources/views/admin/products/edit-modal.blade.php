<!-- Edit Product Modal Content -->
<style>
    /* Include variant management styles */
    .variant-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .variant-item:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .variant-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .variant-title {
        color: #374151;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .variant-color-preview {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #e5e7eb;
        display: inline-block;
    }

    .variant-actions {
        display: flex;
        gap: 8px;
    }

    .variant-actions .btn {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
    }

    .variant-content {
        padding: 20px;
    }

    .default-variant-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .existing-variant-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #f9fafb;
    }

    .existing-variant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .existing-variant-title {
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .existing-variant-actions {
        display: flex;
        gap: 8px;
    }

    .existing-variant-actions .btn {
        padding: 4px 8px;
        font-size: 11px;
    }

    .product-type-toggle {
        margin-bottom: 20px;
    }

    .product-type-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .product-type-card:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }

    .product-type-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .product-type-card input[type="radio"] {
        margin-right: 10px;
    }

    .product-type-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
    }

    .product-type-description {
        color: #6b7280;
        font-size: 14px;
    }
</style>
.image-preview-item {
position: relative;
overflow: hidden;
}

.image-actions {
position: absolute;
top: 5px;
right: 5px;
z-index: 10;
}

.image-actions .btn {
min-width: 30px;
height: 30px;
padding: 0.25rem 0.5rem;
display: flex;
align-items: center;
justify-content: center;
}

.image-actions .d-flex {
gap: 2px;
}

.image-preview-item:hover .image-actions {
opacity: 1;
}

.image-actions {
opacity: 0.8;
transition: opacity 0.2s ease;
}

.image-preview-item img {
transition: transform 0.2s ease;
}

.image-preview-item:hover img {
transform: scale(1.05);
}

.image-preview-item img {
border-radius: 6px;
background-color: #f8f9fa;
padding: 5px;
}

.image-preview-container {
margin-top: 15px;
}

/* Modern Image Upload Styles */
.image-upload-wrapper {
position: relative;
}

.upload-area-modern {
border: 2px dashed #d1d5db;
border-radius: 12px;
padding: 30px 20px;
text-align: center;
background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
cursor: pointer;
position: relative;
overflow: hidden;
}

.upload-area-modern::before {
content: '';
position: absolute;
top: 0;
left: 0;
right: 0;
bottom: 0;
background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
opacity: 0;
transition: opacity 0.3s ease;
z-index: 0;
}

.upload-area-modern:hover {
border-color: #3b82f6;
background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
transform: translateY(-2px);
box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
}

.upload-area-modern:hover::before {
opacity: 0.05;
}

.upload-area-modern.dragover {
border-color: #3b82f6;
background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
transform: scale(1.02);
box-shadow: 0 15px 35px rgba(59, 130, 246, 0.2);
}

.upload-area-modern.dragover::before {
opacity: 0.1;
}

.upload-content-modern {
display: flex;
flex-direction: column;
align-items: center;
gap: 15px;
position: relative;
z-index: 1;
}

.upload-icon-wrapper {
width: 60px;
height: 60px;
border-radius: 50%;
background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
display: flex;
align-items: center;
justify-content: center;
box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);
}

.upload-icon-modern {
font-size: 24px;
color: white;
transition: transform 0.3s ease;
}

.upload-area-modern:hover .upload-icon-modern {
transform: scale(1.1);
}

.upload-text-wrapper {
text-align: center;
}

.upload-title {
font-size: 18px;
font-weight: 600;
color: #1f2937;
margin-bottom: 8px;
}

.upload-description {
color: #6b7280;
margin-bottom: 16px;
font-size: 14px;
}

.upload-format-info {
display: flex;
align-items: center;
justify-content: center;
gap: 8px;
flex-wrap: wrap;
}

.upload-format-info .badge {
font-size: 11px;
padding: 4px 8px;
border-radius: 6px;
}

/* Modern Image Preview Styles */
.image-preview-modern {
margin-top: 20px;
border: 1px solid #e5e7eb;
border-radius: 12px;
background: white;
overflow: hidden;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.preview-header {
display: flex;
justify-content: space-between;
align-items: center;
padding: 16px 20px;
background: #f8fafc;
border-bottom: 1px solid #e5e7eb;
}

.preview-title {
color: #374151;
font-weight: 600;
display: flex;
align-items: center;
}

.preview-content {
padding: 20px;
}

.preview-image-wrapper {
position: relative;
border-radius: 8px;
overflow: hidden;
background: #f9fafb;
min-height: 200px;
display: flex;
align-items: center;
justify-content: center;
}

.preview-image {
max-width: 100%;
max-height: 300px;
object-fit: contain;
border-radius: 8px;
transition: transform 0.3s ease;
}

.preview-image-wrapper:hover .preview-image {
transform: scale(1.02);
}

.preview-overlay {
position: absolute;
top: 0;
left: 0;
right: 0;
bottom: 0;
background: rgba(0, 0, 0, 0.5);
display: flex;
align-items: center;
justify-content: center;
opacity: 0;
transition: opacity 0.3s ease;
}

.preview-image-wrapper:hover .preview-overlay {
opacity: 1;
}

.preview-actions {
display: flex;
gap: 8px;
}

.preview-actions .btn {
border-radius: 6px;
padding: 8px 12px;
}

/* Current Image Styles for Edit Modal */
.current-image-modern {
border: 1px solid #e5e7eb;
border-radius: 12px;
background: white;
overflow: hidden;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.current-image-header {
padding: 16px 20px;
background: #f8fafc;
border-bottom: 1px solid #e5e7eb;
}

.current-image-title {
color: #374151;
font-weight: 600;
display: flex;
align-items: center;
}

.current-image-content {
padding: 20px;
display: flex;
gap: 20px;
flex-wrap: wrap;
}

.current-image-item {
flex: 0 0 auto;
}

.current-image-wrapper {
position: relative;
border-radius: 8px;
overflow: hidden;
background: #f9fafb;
width: 200px;
height: 200px;
display: flex;
align-items: center;
justify-content: center;
}

.current-image-preview {
max-width: 100%;
max-height: 100%;
object-fit: contain;
border-radius: 8px;
transition: transform 0.3s ease;
}

.current-image-wrapper:hover .current-image-preview {
transform: scale(1.02);
}

.current-image-overlay {
position: absolute;
top: 0;
left: 0;
right: 0;
bottom: 0;
background: rgba(0, 0, 0, 0.5);
display: flex;
align-items: center;
justify-content: center;
opacity: 0;
transition: opacity 0.3s ease;
}

.current-image-wrapper:hover .current-image-overlay {
opacity: 1;
}

/* Enhanced UX Elements */
.upload-icon-check {
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
width: 24px;
height: 24px;
background: #10b981;
border-radius: 50%;
display: flex;
align-items: center;
justify-content: center;
color: white;
font-size: 14px;
animation: checkPulse 0.6s ease-in-out;
}

@keyframes checkPulse {
0% {
transform: translate(-50%, -50%) scale(0);
opacity: 0;
}

50% {
transform: translate(-50%, -50%) scale(1.2);
opacity: 1;
}

100% {
transform: translate(-50%, -50%) scale(1);
opacity: 1;
}
}

.upload-progress {
margin-top: 15px;
animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
from {
opacity: 0;
transform: translateY(10px);
}

to {
opacity: 1;
transform: translateY(0);
}
}

.upload-area-modern.uploading {
pointer-events: none;
opacity: 0.7;
}

.upload-area-modern.uploading .upload-content-modern {
opacity: 0.5;
}

.upload-area-modern.success {
border-color: #10b981;
background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
}

.upload-area-modern.error {
border-color: #ef4444;
background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
animation: shake 0.5s ease-in-out;
}

@keyframes shake {

0%,
100% {
transform: translateX(0);
}

25% {
transform: translateX(-5px);
}

75% {
transform: translateX(5px);
}
}

.upload-format-info .badge {
transition: all 0.2s ease;
}

.upload-format-info .badge:hover {
transform: translateY(-1px);
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.upload-description .text-primary {
transition: all 0.2s ease;
}

.upload-area-modern:hover .upload-description .text-primary {
color: #1d4ed8 !important;
text-decoration: underline;
}
</style>
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id" value="{{ $product->id }}">
                <div class="modal-body">
                    <!-- Product Type Detection and Display -->
                    @php
                        $isVariantProduct = $product->variants && $product->variants->count() > 0;
                    @endphp

                    @if ($isVariantProduct)
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            This is a <strong>Multi-Color Variant Product</strong> with
                            {{ $product->variants->count() }} color variants.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            This is a <strong>Single Color Product</strong>. You can convert it to a variant product by
                            adding variants below.
                        </div>
                    @endif

                    <!-- Basic Product Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Hanger Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name"
                                value="{{ $product->name }}">
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
                            <label for="edit_manual_product_id" class="form-label">Hanger ID <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_manual_product_id"
                                name="manual_product_id" value="{{ $product->manual_product_id }}"
                                placeholder="Enter hanger ID" required>
                            <div class="form-text">
                                <i class="bx bx-info-circle text-info"></i>
                                Enter a unique hanger ID
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_product_base" class="form-label">Product Base <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_product_base" name="product_base" required>
                                <option value="">Select Product Base</option>
                                <option value="curtain" {{ $product->product_base == 'curtain' ? 'selected' : '' }}>
                                    Curtain</option>
                                <option value="upholstery"
                                    {{ $product->product_base == 'upholstery' ? 'selected' : '' }}>Upholstery</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="edit_description" class="form-label">Hanger Description <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required>{{ $product->description }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_dimensions" class="form-label">Width <span
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
                            <label for="edit_direction" class="form-label">Direction <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_direction" name="direction"
                                placeholder="Enter direction" value="{{ $product->direction }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_extra_data" class="form-label">Extra Data</label>
                            <textarea class="form-control" id="edit_extra_data" name="extra_data" rows="2"
                                placeholder="Enter extra data">{{ $product->extra_data }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_design_ids" class="form-label">Design/Category <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_design_ids" name="design_ids[]" multiple required>
                                @if (isset($designs))
                                    @foreach ($designs as $design)
                                        <option value="{{ $design->id }}"
                                            {{ $product->relationLoaded('designs') && $product->getRelation('designs')->contains($design->id) ? 'selected' : '' }}>
                                            {{ $design->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_material_ids" class="form-label">Materials <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_material_ids" name="material_ids[]" multiple
                                required>
                                @if (isset($materials))
                                    @foreach ($materials as $material)
                                        <option value="{{ $material->id }}"
                                            {{ $product->relationLoaded('materials') && $product->getRelation('materials')->contains($material->id) ? 'selected' : '' }}>
                                            {{ $material->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_transparency_id" class="form-label">Transparency</label>
                            <select class="form-select" id="edit_transparency_id" name="transparency_id">
                                <option value="">Select Transparency</option>
                                @if (isset($transparencies))
                                    @foreach ($transparencies as $transparency)
                                        <option value="{{ $transparency->id }}"
                                            {{ $product->transparency_id == $transparency->id ? 'selected' : '' }}>
                                            {{ $transparency->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_characteristic_ids" class="form-label">Characteristics <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_characteristic_ids" name="characteristic_ids[]"
                                multiple required>
                                @if (isset($characteristics))
                                    @foreach ($characteristics as $characteristic)
                                        <option value="{{ $characteristic->id }}"
                                            {{ $product->relationLoaded('characteristics') && $product->getRelation('characteristics')->contains($characteristic->id) ? 'selected' : '' }}>
                                            {{ $characteristic->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_usage_id" class="form-label">Usage <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_usage_id" name="usage_id" required>
                                <option value="">Select Usage</option>
                                @if (isset($usage))
                                    @foreach ($usage as $usageItem)
                                        <option value="{{ $usageItem->id }}"
                                            {{ $product->usage_id == $usageItem->id ? 'selected' : '' }}>
                                            {{ $usageItem->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_type" class="form-label">Product Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_type" name="type" required>
                                <option value="">Select Product Type</option>
                                <option value="product_listing"
                                    {{ $product->type == 'product_listing' ? 'selected' : '' }}>Product Listing
                                </option>
                                <option value="view_qualities"
                                    {{ $product->type == 'view_qualities' ? 'selected' : '' }}>View Qualities</option>
                                <option value="upholstery" {{ $product->type == 'upholstery' ? 'selected' : '' }}>
                                    Upholstery</option>
                                <option value="latest" {{ $product->type == 'latest' ? 'selected' : '' }}>
                                    Latest</option>
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
                                <input class="form-check-input" type="checkbox" id="edit_in_stock" name="in_stock"
                                    value="1" {{ $product->in_stock ? 'checked' : '' }}>
                                {{-- <label class="form-check-label" for="edit_in_stock">
                                    Toggle to set stock status
                                </label> --}}
                            </div>
                        </div>
                        {{-- <div class="col-md-6 mb-3">
                            <label for="edit_designs" class="form-label">Designs</label>
                            <textarea class="form-control" id="edit_designs" name="designs" rows="2"
                                placeholder="Enter designs (comma-separated or one per line)">{{ $product->designs }}</textarea>
                        </div> --}}
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

                        <!-- Single Product Fields (Legacy) -->
                        @if (!$isVariantProduct)
                            <div class="row">
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
                                    <label for="edit_color_id" class="form-label">Color <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_color_id" name="color_id" required>
                                        <option value="">Select Color</option>
                                        @if (isset($colors))
                                            @foreach ($colors as $color)
                                                <option value="{{ $color->id }}"
                                                    {{ $product->color_id == $color->id ? 'selected' : '' }}>
                                                    {{ $color->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif

                        <!-- Variant Management Section -->
                        @if ($isVariantProduct)
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Product Variants</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Existing Variants -->
                                            @foreach ($product->variants as $index => $variant)
                                                <div class="existing-variant-item"
                                                    data-variant-id="{{ $variant->id }}">
                                                    <div class="existing-variant-header">
                                                        <div class="existing-variant-title">
                                                            <span class="variant-color-preview"
                                                                style="background-color: {{ $variant->color->color_value ?? '#e5e7eb' }};"></span>
                                                            {{ $variant->color->name ?? 'Unknown Color' }}
                                                            @if ($variant->is_default)
                                                                <span class="default-variant-badge ms-2">Default</span>
                                                            @endif
                                                        </div>
                                                        <div class="existing-variant-actions">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary edit-variant"
                                                                data-variant-id="{{ $variant->id }}">
                                                                <i class="bx bx-edit"></i> Edit
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger delete-variant"
                                                                data-variant-id="{{ $variant->id }}">
                                                                <i class="bx bx-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <strong>Price:</strong>
                                                            €{{ number_format($variant->price, 2) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Stock:</strong> {{ $variant->stock_quantity ?? 0 }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>SKU:</strong> {{ $variant->sku }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Status:</strong>
                                                            <span
                                                                class="badge bg-{{ $variant->status == 'active' ? 'success' : ($variant->status == 'inactive' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($variant->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <!-- New Variants Container -->
                                            <div id="newVariantsContainer">
                                                <!-- New variants will be added here -->
                                            </div>

                                            <div class="mt-3 mb-3 text-end">
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    id="addVariantBtn">
                                                    <i class="bx bx-plus"></i> Add Variant
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Convert to Variant Product Section -->
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Convert to Variant Product</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">
                                                Convert this single-color product to a multi-color variant product to
                                                add more color options.
                                            </p>
                                            <button type="button" class="btn btn-outline-primary"
                                                id="convertToVariantBtn">
                                                <i class="bx bx-plus"></i> Convert to Variant Product
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- General Image Upload Section -->
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold mb-3">Product Image</label>

                            <!-- Current Image Display -->
                            @if ($product->media && $product->media->count() > 0)
                                <div class="current-image-modern mb-4">
                                    <div class="current-image-header">
                                        <h6 class="current-image-title mb-0">
                                            <i class="bx bx-image me-2"></i>Current Image
                                        </h6>
                                    </div>
                                    <div class="current-image-content">
                                        @foreach ($product->media as $media)
                                            <div class="current-image-item" data-media-id="{{ $media->id }}">
                                                <div class="current-image-wrapper">
                                                    <img src="{{ asset($media->file_path) }}"
                                                        alt="{{ $media->alt_text }}" class="current-image-preview">
                                                    <div class="current-image-overlay">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-existing-image"
                                                            data-media-id="{{ $media->id }}" title="Delete Image">
                                                            <i class="bx bx-trash me-1"></i>Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Single Image Upload -->
                            <div class="d-flex justify-content-center">
                                <div class="image-upload-wrapper" style="max-width: 500px; width: 100%;">
                                    <div class="upload-area-modern" id="editUploadArea">
                                        <input type="file" id="editImageInput" name="image" accept="image/*"
                                            class="file-input-overlay">
                                        <div class="upload-content-modern">
                                            <div class="upload-icon-wrapper">
                                                <i class="bx bx-cloud-upload upload-icon-modern"></i>
                                                <div class="upload-icon-check" style="display: none;">
                                                    <i class="bx bx-check"></i>
                                                </div>
                                            </div>
                                            <div class="upload-text-wrapper">
                                                <h6 class="upload-title">Upload New Image</h6>
                                                <p class="upload-description">Drag & drop your image here, or <span
                                                        class="text-primary fw-semibold">click to browse</span></p>
                                                <div class="upload-format-info">
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary me-2">PNG</span>
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary me-2">JPG</span>
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary me-2">JPEG</span>
                                                    <span class="text-muted small">Max 10MB</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="upload-progress" style="display: none;">
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                    role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <small class="text-muted mt-2">Uploading...</small>
                                        </div>
                                    </div>

                                    <!-- New Image Preview Container -->
                                    <div class="image-preview-modern" id="editImagePreviewContainer"
                                        style="display: none;">
                                        <div class="preview-header">
                                            <h6 class="preview-title mb-0">
                                                <i class="bx bx-image me-2"></i>New Image Preview
                                            </h6>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-new-image"
                                                title="Remove Image">
                                                <i class="bx bx-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                        <div class="preview-content">
                                            <div class="preview-image-wrapper">
                                                <img id="editImagePreview" src="" alt="Preview"
                                                    class="preview-image">
                                                <div class="preview-overlay">
                                                    <div class="preview-actions">
                                                        <button type="button"
                                                            class="btn btn-sm btn-light remove-new-image"
                                                            title="Remove Image">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
