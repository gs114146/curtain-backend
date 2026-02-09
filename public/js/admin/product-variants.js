/**
 * Product Variants Management JavaScript
 * Handles dynamic variant creation, editing, and management in admin forms
 */

class ProductVariantManager {
    constructor() {
        this.variantCounter = 0;
        this.colors = [];
        this.init();
    }

    init() {
        this.loadColors();
        this.bindEvents();
        // If on create page and no variants yet, add one by default
        $(document).ready(() => {
            const hasContainer = $('#variantsContainer').length > 0;
            const hasExisting = $('.variant-item').length > 0;
            if (hasContainer && !hasExisting) {
                this.addVariant().then(() => {
                    // Mark first as default for convenience
                    const $first = $('#variantsContainer .variant-item').first();
                    if ($first.length) {
                        this.setDefaultVariant($first);
                    }
                });
            }
        });
    }

    async loadColors() {
        try {
            // Use admin products related-data JSON endpoint to fetch colors
            const url = window?.routes?.admin?.products?.relatedData;
            if (!url) {
                console.error('Related data route not found on window.routes');
                return;
            }
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            this.colors = Array.isArray(data.colors) ? data.colors : [];
        } catch (error) {
            console.error('Error loading colors:', error);
        }
    }

    bindEvents() {
        // No product type toggle anymore; variants are always required

        // Add variant button
        $('#addVariantBtn').on('click', async () => {
            await this.addVariant();
        });

        // Variant container events (delegated)
        $(document).on('click', '.remove-variant', (e) => {
            this.removeVariant($(e.target).closest('.variant-item'));
        });

        $(document).on('click', '.set-default-variant', (e) => {
            this.setDefaultVariant($(e.target).closest('.variant-item'));
        });

        $(document).on('change', '.variant-color-select', (e) => {
            this.updateVariantColor($(e.target).closest('.variant-item'));
        });

        $(document).on('change', '.variant-default-checkbox', (e) => {
            this.handleDefaultVariantChange($(e.target).closest('.variant-item'));
        });

        // Image upload for variants
        $(document).on('change', '.variant-image-input', (e) => {
            this.handleVariantImageUpload($(e.target).closest('.variant-item'), e.target.files);
        });

        $(document).on('click', '.variant-image-upload', (e) => {
            // Avoid recursive click when the click originates from the hidden input itself
            if ($(e.target).is('.variant-image-input')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).closest('.variant-item').find('.variant-image-input').trigger('click');
        });

        $(document).on('click', '.variant-image-remove', (e) => {
            this.removeVariantImage($(e.target).closest('.variant-image-item'));
        });

        // Drag and drop for variant images
        $(document).on('dragover', '.variant-image-upload', (e) => {
            e.preventDefault();
            $(e.target).addClass('dragover');
        });

        $(document).on('dragleave', '.variant-image-upload', (e) => {
            $(e.target).removeClass('dragover');
        });

        $(document).on('drop', '.variant-image-upload', (e) => {
            e.preventDefault();
            $(e.target).removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            this.handleVariantImageUpload($(e.target).closest('.variant-item'), files);
        });

        // Edit variant button
        $(document).on('click', '.edit-variant', (e) => {
            const variantId = $(e.target).closest('.edit-variant').data('variant-id');
            this.editVariant(variantId);
        });

        // Delete variant button
        $(document).on('click', '.delete-variant', (e) => {
            const variantId = $(e.target).closest('.delete-variant').data('variant-id');
            this.deleteVariant(variantId);
        });

        // Convert to variant button
        $(document).on('click', '#convertToVariantBtn', () => {
            this.convertToVariant();
        });

        // Variant edit form submission
        $(document).on('submit', '#variantEditForm', (e) => {
            e.preventDefault();
            this.updateVariant();
        });

        // Variant image upload in edit modal
        $(document).on('click', '#variantImageUpload', () => {
            $('#variantImageInput').click();
        });

        $(document).on('change', '#variantImageInput', (e) => {
            this.handleVariantImageUpload($('#variantEditModal'), e.target.files);
        });
    }

    // Product type toggle removed

    async addVariant() {
        if (!this.colors || this.colors.length === 0) {
            await this.loadColors();
        }

        // Calculate next index based on existing variants to ensure correct numbering
        const existingVariants = $('.variant-item').length;
        const nextIndex = existingVariants + 1;

        // Update counter to match the next index
        this.variantCounter = nextIndex;

        const variantHtml = this.createVariantHtml(nextIndex);
        $('#variantsContainer').append(variantHtml);
        $('#noVariantsMessage').hide();

        // Add animation
        const $variant = $('#variantsContainer .variant-item').last();
        $variant.addClass('variant-item-enter');

        // Don't auto-focus to prevent unwanted scrolling
        // Focus only when user manually adds a variant (not on page load)
        const isPageLoad = !$('#variantsContainer .variant-item').length || $('#variantsContainer .variant-item').length === 1;
        if (!isPageLoad) {
            // Only focus if user manually clicked add variant button
            $variant.find('.variant-color-select').focus();
        }
    }

    createVariantHtml(index) {
        const colorOptions = this.colors.map(color =>
            `<option value="${color.id}" data-color-value="${color.color_value}">${color.name}</option>`
        ).join('');

        return `
            <div class="variant-item card border-0 shadow-sm mb-3" data-variant-index="${index}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-grip-vertical variant-sort-handle me-2"></i>
                        <h6 class="variant-title mb-0">
                            <span class="variant-color-preview" style="background-color: #e5e7eb;"></span>
                            Variant ${index}
                        </h6>
                        <span class="default-variant-badge ms-2" style="display: none;">Default</span>
                    </div>
                    <div class="variant-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary set-default-variant">
                            <i class="bx bx-star"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-variant">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body variant-content">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <select class="form-select variant-color-select" name="variants[${index}][color_id]" required>
                                <option value="">Select Color</option>
                                ${colorOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Article/Color No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control variant-article" name="variants[${index}][article]" placeholder="Enter article/color number" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Article/Design Description <span class="text-danger">*</span></label>
                            <textarea class="form-control variant-article-description" name="variants[${index}][article_description]" placeholder="Enter article/design description" required></textarea>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input variant-default-checkbox" type="checkbox" name="variants[${index}][is_default]" value="1">
                                <label class="form-check-label ms-2">Default Variant</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Variant Image <span class="text-danger">*</span></label>
                            <div class="variant-image-upload p-3 rounded border d-flex align-items-center justify-content-center" style="cursor:pointer; min-height:60px;">
                                <i class="bx bx-cloud-upload me-2"></i>
                                <span>Select an image or drag & drop</span>
                                <input type="file" class="variant-image-input" name="variants[${index}][images][]" accept="image/*" style="display: none;">
                            </div>
                            <div class="variant-error-message text-danger small mt-1" style="display:none;">An image is required for this variant.</div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="variant-image-preview d-flex gap-2 flex-wrap mt-2 w-100 justify-content-start"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    removeVariant($variant) {
        if (confirm('Are you sure you want to remove this variant?')) {
            $variant.addClass('variant-item-exit');
            setTimeout(() => {
                $variant.remove();
                this.updateVariantNumbers();
                this.checkNoVariantsMessage();

                // Reset counter if all variants are deleted
                if ($('.variant-item').length === 0) {
                    this.variantCounter = 0;
                }
            }, 300);
        }
    }

    setDefaultVariant($variant) {
        // Remove default from all variants
        $('.variant-item').each((index, element) => {
            const $item = $(element);
            $item.find('.variant-default-checkbox').prop('checked', false);
            $item.find('.default-variant-badge').hide();
            $item.find('.set-default-variant').show();
        });

        // Set this variant as default
        $variant.find('.variant-default-checkbox').prop('checked', true);
        $variant.find('.default-variant-badge').show();
        $variant.find('.set-default-variant').hide();
    }

    updateVariantColor($variant) {
        const selectedColor = $variant.find('.variant-color-select option:selected');
        const colorValue = selectedColor.data('color-value');
        const colorName = selectedColor.text();

        if (colorValue) {
            $variant.find('.variant-color-preview').css('background-color', colorValue);
            $variant.find('.variant-title').html(`
                <span class="variant-color-preview" style="background-color: ${colorValue};"></span>
                ${colorName}
            `);
        } else {
            $variant.find('.variant-color-preview').css('background-color', '#e5e7eb');
            $variant.find('.variant-title').html(`
                <span class="variant-color-preview" style="background-color: #e5e7eb;"></span>
                Variant ${$variant.data('variant-index')}
            `);
        }

        // Optional: could auto-generate variant SKU on backend; skip here
    }

    generateVariantSku($variant) { }

    createSku(productName, colorName) {
        const cleanProductName = productName.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        const cleanColorName = colorName.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        const randomDigits = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        const timestamp = Date.now().toString().slice(-4);

        return `${cleanProductName}-${cleanColorName}-${randomDigits}${timestamp}`;
    }

    handleDefaultVariantChange($variant) {
        if ($variant.find('.variant-default-checkbox').is(':checked')) {
            this.setDefaultVariant($variant);
        } else {
            $variant.find('.default-variant-badge').hide();
            $variant.find('.set-default-variant').show();
        }
    }

    handleVariantImageUpload($variant, files) {
        const $preview = $variant.find('.variant-image-preview');

        // Accept only the first image; replace any existing preview
        const file = files && files.length ? files[0] : null;
        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const imageHtml = `
                <div class="variant-image-item" style="position:relative;width:96px;height:96px;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;">
                    <img src="${e.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;display:block;">
                    <button type="button" class="variant-image-remove" title="Remove" style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,0.95);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:12px;">
                        <i class="bx bx-x" style="line-height:1;"></i>
                    </button>
                </div>
            `;
            $preview.empty().append(imageHtml);
            $variant.find('.variant-error-message').hide();
        };
        reader.readAsDataURL(file);
    }

    removeVariantImage($imageItem) {
        $imageItem.remove();
    }

    updateVariantNumbers() {
        $('.variant-item').each((index, element) => {
            const $item = $(element);
            const newIndex = index + 1;
            $item.attr('data-variant-index', newIndex);

            // Update form field names
            $item.find('input, select').each((fieldIndex, field) => {
                const $field = $(field);
                const name = $field.attr('name');
                if (name && name.includes('variants[')) {
                    const newName = name.replace(/variants\[\d+\]/, `variants[${newIndex}]`);
                    $field.attr('name', newName);
                }
            });

            // Update title
            const colorName = $item.find('.variant-color-select option:selected').text();
            if (colorName && colorName !== 'Select Color') {
                $item.find('.variant-title').html(`
                    <span class="variant-color-preview" style="background-color: ${$item.find('.variant-color-select option:selected').data('color-value')};"></span>
                    ${colorName}
                `);
            } else {
                $item.find('.variant-title').html(`
                    <span class="variant-color-preview" style="background-color: #e5e7eb;"></span>
                    Variant ${newIndex}
                `);
            }
        });
    }

    checkNoVariantsMessage() {
        if ($('.variant-item').length === 0) {
            $('#noVariantsMessage').show();
        }
    }

    // Validation methods
    validateVariants() {
        let isValid = true;
        const errors = [];

        const variantCount = $('.variant-item').length;
        if (variantCount === 0) {
            errors.push('Add at least one color variant');
            isValid = false;
        }

        $('.variant-item').each((index, element) => {
            const $variant = $(element);
            const colorId = $variant.find('.variant-color-select').val();
            const filesCount = ($variant.find('.variant-image-input')[0]?.files?.length) || 0;

            if (!colorId) {
                errors.push(`Variant ${index + 1}: Color is required`);
                $variant.addClass('has-error');
                isValid = false;
            } else {
                $variant.removeClass('has-error');
            }

            if (filesCount === 0) {
                errors.push(`Variant ${index + 1}: At least one image is required`);
                $variant.find('.variant-error-message').show();
                isValid = false;
            } else {
                $variant.find('.variant-error-message').hide();
            }
        });

        if (!isValid) {
            alert('Please fix the following errors:\n' + errors.join('\n'));
        }

        return isValid;
    }

    // Get variant data for form submission
    getVariantsData() {
        const variants = [];
        const productPrice = $('#create_price').val();
        const productSalePrice = $('#create_sale_price').val();
        const productStock = $('#create_stock_quantity').val();

        $('.variant-item').each((index, element) => {
            const $variant = $(element);
            const variantData = {
                color_id: $variant.find('.variant-color-select').val(),
                article: $variant.find('.variant-article').val(),
                article_description: $variant.find('.variant-article-description').val(),
                price: productPrice || null,
                sale_price: productSalePrice || null,
                stock_quantity: productStock || 0,
                status: 'active',
                is_default: $variant.find('.variant-default-checkbox').is(':checked') ? 1 : 0,
                images: $variant.find('.variant-image-input')[0].files
            };
            variants.push(variantData);
        });

        return variants;
    }

    // Edit variant functionality
    editVariant(variantId) {
        // Load variant data and populate edit modal
        $.ajax({
            url: `/admin/variants/${variantId}/edit`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const variant = response.variant;

                    // Populate form fields
                    $('#variant_id').val(variant.id);
                    $('#variant_color_id').val(variant.color_id);
                    $('#variant_article').val(variant.article || '');
                    $('#variant_article_description').val(variant.article_description || '');
                    $('#variant_sku').val(variant.sku);
                    $('#variant_price').val(variant.price);
                    $('#variant_sale_price').val(variant.sale_price);
                    $('#variant_stock_quantity').val(variant.stock_quantity);
                    $('#variant_status').val(variant.status);
                    $('#variant_is_default').prop('checked', variant.is_default);

                    // Load current images
                    this.loadVariantImages(variant.media);

                    // Show modal
                    $('#variantEditModal').modal('show');
                }
            }.bind(this),
            error: function (xhr) {
                console.error('Error loading variant:', xhr.responseText);
                alert('Error loading variant data');
            }
        });
    }

    // Load variant images
    loadVariantImages(media) {
        const $container = $('#currentVariantImages');
        $container.empty();

        if (media && media.length > 0) {
            media.forEach(function (image) {
                const imageHtml = `
                    <div class="variant-image-item" data-image-id="${image.id}" style="position:relative;width:96px;height:96px;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;">
                        <img src="${image.file_path}" alt="Variant Image" style="width:100%;height:100%;object-fit:cover;display:block;">
                        <button type="button" class="variant-image-remove" data-image-id="${image.id}" title="Remove" style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,0.95);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:12px;">
                            <i class="bx bx-x" style="line-height:1;"></i>
                        </button>
                    </div>
                `;
                $container.append(imageHtml);
            });
        }
    }

    // Delete variant
    deleteVariant(variantId) {
        if (confirm('Are you sure you want to delete this variant? This action cannot be undone.')) {
            $.ajax({
                url: `/admin/variants/${variantId}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        // Remove variant from UI - handle both selectors
                        const $variantItem = $(`.existing-variant-item[data-variant-id="${variantId}"], .variant-item[data-variant-id="${variantId}"]`);
                        
                        // Remove all hidden input fields related to this variant
                        $(`input[name*="variants"][value="${variantId}"]`).remove();
                        $(`input[name*="keep_${variantId}"]`).remove();
                        
                        $variantItem.fadeOut(300, function () {
                            $(this).remove();
                            
                            // Update variant count after removal
                            const remainingVariants = $('.variant-item.existing-variant-item, .existing-variant-item').length;
                            
                            // Update the info alert if it exists
                            const $infoAlert = $('.alert-info').filter(function() {
                                return $(this).text().indexOf('Multi-Color Variant Product') !== -1;
                            });
                            if ($infoAlert.length) {
                                $infoAlert.html(`<i class="bx bx-info-circle me-2"></i>This is a <strong>Multi-Color Variant Product</strong> with ${remainingVariants} color variant${remainingVariants !== 1 ? 's' : ''}.`);
                            }
                            
                            // Update variant count display if it exists
                            if ($('.variant-count').length) {
                                $('.variant-count').text(remainingVariants);
                            }
                        });

                        // Show success message
                        this.showNotification('Variant deleted successfully', 'success');
                    }
                }.bind(this),
                error: function (xhr) {
                    // Handle 404 - variant already deleted (desired state)
                    if (xhr.status === 404) {
                        // Remove variant from UI since it's already gone - handle both selectors
                        const $variantItem = $(`.existing-variant-item[data-variant-id="${variantId}"], .variant-item[data-variant-id="${variantId}"]`);
                        
                        // Remove all hidden input fields related to this variant
                        $(`input[name*="variants"][value="${variantId}"]`).remove();
                        $(`input[name*="keep_${variantId}"]`).remove();
                        
                        $variantItem.fadeOut(300, function () {
                            $(this).remove();
                            
                            // Update variant count after removal
                            const remainingVariants = $('.variant-item.existing-variant-item, .existing-variant-item').length;
                            
                            // Update the info alert if it exists
                            const $infoAlert = $('.alert-info').filter(function() {
                                return $(this).text().indexOf('Multi-Color Variant Product') !== -1;
                            });
                            if ($infoAlert.length) {
                                $infoAlert.html(`<i class="bx bx-info-circle me-2"></i>This is a <strong>Multi-Color Variant Product</strong> with ${remainingVariants} color variant${remainingVariants !== 1 ? 's' : ''}.`);
                            }
                            
                            // Update variant count display if it exists
                            if ($('.variant-count').length) {
                                $('.variant-count').text(remainingVariants);
                            }
                        });
                        this.showNotification('Variant was already deleted', 'info');
                    } else {
                        // Handle other errors
                        console.error('Error deleting variant:', xhr.responseText);
                        let errorMessage = 'Error deleting variant';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            // Use default error message
                        }
                        this.showNotification(errorMessage, 'error');
                    }
                }.bind(this)
            });
        }
    }

    // Convert single product to variant product
    convertToVariant() {
        if (confirm('Convert this single-color product to a multi-color variant product? This will create a variant from the current product data.')) {
            // This would typically make an AJAX call to convert the product
            // For now, we'll just show a message
            alert('Conversion functionality would be implemented here. This would create a variant from the current product data.');
        }
    }

    // Update variant
    updateVariant() {
        const formData = new FormData($('#variantEditForm')[0]);
        const variantId = $('#variant_id').val();

        $.ajax({
            url: `/admin/variants/${variantId}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#variantEditModal').modal('hide');
                    this.showNotification('Variant updated successfully', 'success');

                    // Refresh the page or update the variant in the UI
                    location.reload();
                }
            }.bind(this),
            error: function (xhr) {
                console.error('Error updating variant:', xhr.responseText);
                const errors = xhr.responseJSON?.errors || {};
                this.showValidationErrors(errors);
            }.bind(this)
        });
    }

    // Show validation errors
    showValidationErrors(errors) {
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Show new errors
        Object.keys(errors).forEach(field => {
            const $field = $(`[name="${field}"]`);
            $field.addClass('is-invalid');
            $field.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
        });
    }

    // Show notification
    showNotification(message, type = 'info') {
        // Simple notification - you can replace with your preferred notification system
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        // Insert at the top of the modal body
        $('.modal-body').prepend(notification);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            notification.alert('close');
        }, 3000);
    }
}

// Product Image Upload Handler
function handleProductImageUpload(files) {
    const $preview = $('.product-image-preview');
    const $error = $('.product-image-error');
    const $input = $('#productImageInput');

    // Accept only the first image; replace any existing preview
    const file = files && files.length ? files[0] : null;
    if (!file || !file.type.startsWith('image/')) {
        $error.text('Please select a valid image file.').show();
        return;
    }

    // Check file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
        $error.text('Image size must be less than 10MB.').show();
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const imageHtml = `
            <div class="product-image-item" style="position:relative;width:120px;height:120px;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;">
                <img src="${e.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;display:block;">
                <button type="button" class="product-image-remove" title="Remove" style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,0.95);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer;">
                    <i class="bx bx-x" style="line-height:1;"></i>
                </button>
            </div>
        `;
        $preview.empty().append(imageHtml);
        $error.hide();
    };
    reader.readAsDataURL(file);
}

function removeProductImage($imageItem) {
    const $input = $('#productImageInput');
    const $preview = $('.product-image-preview');
    const $error = $('.product-image-error');

    $imageItem.remove();
    $input.val(''); // Clear the file input

    // Mark that image was removed (for edit forms)
    $input.data('removed', true);

    // Image is optional, so no error needed
    $error.hide();
    $('.product-image-upload').css('border-color', '');
}

// Initialize when document is ready
$(document).ready(function () {
    window.productVariantManager = new ProductVariantManager();

    // Product image upload handling
    $(document).on('change', '#productImageInput', function (e) {
        if (this.files && this.files.length > 0) {
            $(this).data('removed', false); // Clear removed flag when new image is selected
        }
        handleProductImageUpload(e.target.files);
    });

    $(document).on('click', '.product-image-upload', function (e) {
        // Avoid recursive click when the click originates from the hidden input itself
        if ($(e.target).is('#productImageInput')) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        $('#productImageInput').trigger('click');
    });

    $(document).on('click', '.product-image-remove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        removeProductImage($(this).closest('.product-image-item'));
    });

    // Drag and drop for product image
    $(document).on('dragover', '.product-image-upload', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    $(document).on('dragleave', '.product-image-upload', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    $(document).on('drop', '.product-image-upload', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        if (files && files.length > 0) {
            const input = document.getElementById('productImageInput');
            if (input) {
                // Create a new FileList-like object
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                input.files = dataTransfer.files;
                handleProductImageUpload(files);
            }
        }
    });

    // Form validation for product image
    $('#createProductForm, #editProductForm').on('submit', function (e) {
        const $preview = $('.product-image-preview');
        const $input = $('#productImageInput');
        const $error = $('.product-image-error');

        // Check if image is selected (either new upload or existing)
        const hasPreview = $preview.find('.product-image-item').length > 0;
        const hasFile = $input[0].files && $input[0].files.length > 0;
        const wasRemoved = $input.data('removed') === true;

        // Image is optional, so no validation needed
        // Clear any previous errors
        $error.hide();
        $('.product-image-upload').css('border-color', '');

        $error.hide();
        $('.product-image-upload').css('border-color', '');
    });

    // Enforce at least one variant and inject product-level fields per variant
    $('#createProductForm').on('submit', function (e) {
        if (!window.productVariantManager.validateVariants()) {
            e.preventDefault();
            return false;
        }

        const productPrice = $('#create_price').val();
        const productSalePrice = $('#create_sale_price').val();
        const productStock = $('#create_stock_quantity').val();

        $('.variant-item').each((index, element) => {
            const idx = index + 1;
            const $variant = $(element);

            const ensureHidden = (name, value) => {
                let $hidden = $variant.find(`input[type="hidden"][name="variants[${idx}][${name}]"]`);
                if ($hidden.length === 0) {
                    $hidden = $('<input>', { type: 'hidden', name: `variants[${idx}][${name}]` }).appendTo($variant);
                }
                $hidden.val(value ?? '');
            };

            ensureHidden('price', productPrice);
            ensureHidden('sale_price', productSalePrice);
            ensureHidden('stock_quantity', productStock || 0);
            ensureHidden('status', 'active');
        });
    });
});
