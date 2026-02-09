<!-- Image Upload Styles -->
<style>
    .image-upload-container {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background-color: #f8f9fa;
    }

    .upload-area {
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 6px;
        padding: 20px;
    }

    .upload-area:hover {
        background-color: #e9ecef;
        border-color: #007bff;
    }

    .upload-area.dragover {
        background-color: #e3f2fd;
        border-color: #2196f3;
    }

    .upload-icon {
        font-size: 48px;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .upload-text {
        font-size: 16px;
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
    }

    .upload-subtext {
        font-size: 14px;
        color: #6c757d;
        margin: 0;
    }

    .image-preview-container {
        margin-top: 20px;
    }

    .image-preview-item {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
        background: white;
    }

    .image-preview-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    .image-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        display: flex;
        gap: 5px;
    }

    .image-actions .btn {
        padding: 4px 8px;
        font-size: 12px;
    }

    .existing-images {
        margin-top: 20px;
    }

    .existing-images h6 {
        color: #495057;
        margin-bottom: 15px;
    }

    .image-preview-item .badge {
        position: absolute;
        top: 5px;
        left: 5px;
    }

    .image-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px;
        font-size: 11px;
    }

    .image-preview-item:hover .image-actions {
        opacity: 1;
    }

    .image-actions {
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }

    .upload-area.dragover .upload-icon {
        color: #2196f3;
        transform: scale(1.1);
    }

    .upload-area.dragover .upload-text {
        color: #2196f3;
    }

    .file-input-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .upload-area {
        position: relative;
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

    /* Variant Management Styles */
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
        justify-content: between;
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

    .variant-images {
        margin-top: 15px;
    }

    .variant-image-upload {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .variant-image-upload:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .variant-image-upload.dragover {
        border-color: #3b82f6;
        background: #dbeafe;
    }

    .variant-image-preview {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .variant-image-item {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }

    .variant-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .variant-image-remove {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 20px;
        height: 20px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .default-variant-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .variant-sort-handle {
        cursor: move;
        color: #6b7280;
        font-size: 16px;
    }

    .variant-sort-handle:hover {
        color: #374151;
    }

    .no-variants-message {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .no-variants-message i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    /* Animation for variant addition/removal */
    .variant-item-enter {
        opacity: 0;
        transform: translateY(-20px);
        animation: variantEnter 0.3s ease forwards;
    }

    .variant-item-exit {
        opacity: 1;
        transform: translateY(0);
        animation: variantExit 0.3s ease forwards;
    }

    @keyframes variantEnter {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes variantExit {
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    /* Form validation styles for variants */
    .variant-item.has-error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .variant-error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .variant-header {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .variant-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .variant-content .row>div {
            margin-bottom: 15px;
        }
    }
</style>

<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createProductForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Basic Product Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_name" class="form-label">Product Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_sku" class="form-label">SKU</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="create_sku" name="sku"
                                    placeholder="Auto-generated from product name">
                                <button type="button" class="btn btn-outline-secondary" id="generateSkuBtn">
                                    <i class="bx bx-refresh"></i> Generate
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bx bx-info-circle text-info"></i>
                                Leave empty to auto-generate from product name
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_manual_product_id" class="form-label">Product ID</label>
                            <input type="text" class="form-control" id="create_manual_product_id" name="manual_product_id"
                                placeholder="Enter product ID manually">
                            <div class="form-text">
                                <i class="bx bx-info-circle text-info"></i>
                                Optional: Enter a custom product ID
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="create_description" class="form-label">Description</label>
                            <textarea class="form-control" id="create_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_dimensions" class="form-label">Dimensions <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_dimensions" name="dimensions"
                                placeholder="e.g., 120cm x 150cm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_weight" class="form-label">Weight <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_weight" name="weight"
                                placeholder="e.g., 2.5kg" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_direction" class="form-label">Direction</label>
                            <input type="text" class="form-control" id="create_direction" name="direction"
                                placeholder="Enter direction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_design_ids" class="form-label">Designs <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_design_ids" name="design_ids[]" multiple required>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_material_ids" class="form-label">Materials <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_material_ids" name="material_ids[]" multiple required>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_transparency_id" class="form-label">Transparency <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_transparency_id" name="transparency_id" required>
                                <option value="">Select Transparency</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_characteristic_ids" class="form-label">Characteristics <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_characteristic_ids" name="characteristic_ids[]" multiple required>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_usage_id" class="form-label">Usage <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_usage_id" name="usage_id" required>
                                <option value="">Select Usage</option>
                            </select>
                        </div>
                        <!-- Product-level pricing/stock (applies to all variants) -->
                        <div class="col-md-4 mb-3">
                            <label for="create_price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="create_price" name="price"
                                    step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_sale_price" class="form-label">Sale Price</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="create_sale_price" name="sale_price"
                                    step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="create_stock_quantity"
                                name="stock_quantity" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_type" class="form-label">Product Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_type" name="type" required>
                                <option value="">Select Product Type</option>
                                <option value="product_listing" selected>Product Listing</option>
                                <option value="view_qualities">View Qualities</option>
                                <option value="upholstery">Upholstery</option>
                                <option value="latest">Latest</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_status" class="form-label">Status</label>
                            <select class="form-select" id="create_status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">In Stock</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="create_in_stock" name="in_stock"
                                    value="1" checked>
                            </div>
                        </div>
                    </div>
                    <!-- Variant Product Fields (required: at least one) -->
                    <div id="variantProductFields">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">Product Variants</h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">
                                            <i class="bx bx-plus"></i> Add Variant
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="variantsContainer">
                                            <!-- Variants will be added here dynamically -->
                                        </div>
                                        <div class="text-center text-muted" id="noVariantsMessage">
                                            <i class="bx bx-info-circle"></i>
                                            Add at least one color variant. Each variant only needs color and images.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createProductBtn">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal Container (will be populated via AJAX) -->
<div id="editProductModalContainer"></div>

<!-- Variant Edit Modal -->
@include('admin.products.variant-edit-modal')

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProduct">Delete Product</button>
            </div>
        </div>
    </div>
</div>
