<!-- Variant Edit Modal -->
<div class="modal fade" id="variantEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Variant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="variantEditForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="variant_id" name="variant_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="variant_color_id" class="form-label">Color <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="variant_color_id" name="color_id" required>
                                <option value="">Select Color</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="variant_article" class="form-label">Article/Color No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="variant_article" name="article" placeholder="Enter article/color number" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="variant_article_description" class="form-label">Article/Design Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="variant_article_description" name="article_description" placeholder="Enter article/design description" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="variant_sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="variant_sku" name="sku" readonly>
                            <div class="form-text">
                                <i class="bx bx-info-circle text-info"></i>
                                SKU cannot be changed after variant creation
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="variant_price" class="form-label">Price <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="variant_price" name="price"
                                    step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="variant_sale_price" class="form-label">Sale Price</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="variant_sale_price" name="sale_price"
                                    step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="variant_stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="variant_stock_quantity" name="stock_quantity"
                                min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="variant_status" class="form-label">Status</label>
                            <select class="form-select" id="variant_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="variant_is_default"
                                    name="is_default" value="1">
                                <label class="form-check-label">Set as Default Variant</label>
                            </div>
                        </div>
                    </div>

                    <!-- Variant Images -->
                    <div class="mb-3">
                        <label class="form-label">Variant Images</label>

                        <!-- Current Images -->
                        <div id="currentVariantImages" class="mb-3">
                            <!-- Current images will be loaded here -->
                        </div>

                        <!-- Upload New Images -->
                        <div class="variant-image-upload" id="variantImageUpload">
                            <i class="bx bx-cloud-upload me-2"></i>
                            <span>Click to upload new images or drag & drop</span>
                            <input type="file" id="variantImageInput" name="images[]" accept="image/*" multiple
                                style="display: none;">
                        </div>
                        <div class="variant-image-preview" id="variantImagePreview"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Variant</button>
                </div>
            </form>
        </div>
    </div>
</div>
