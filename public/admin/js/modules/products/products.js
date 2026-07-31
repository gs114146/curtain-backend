$(document).ready(function () {
    // Detect contexts
    const hasTable = $('#productsTable').length > 0;
    const hasCreateForm = $('#createProductForm').length > 0;
    const hasEditForm = $('#editProductForm').length > 0;

    let productsTable = null;
    let deleteProductId = null;
    let relatedData = null;

    /**
     * Show server validation errors in the right place.
     * Product image uses .product-image-error BELOW the upload box (not inside it).
     */
    function showProductFieldError(field, msg, formPrefix) {
        // Clarify generic Laravel upload failure
        if (/failed to upload/i.test(msg)) {
            msg = 'Image upload failed. Use a PNG, JPG, or GIF under 8MB.';
        }

        // Main product image → dedicated error slot under upload area
        if (field === 'image') {
            const $error = $('.product-image-error');
            $error.text(msg).show();
            $('.product-image-upload').css('border-color', '#dc3545');
            return;
        }

        // Variant image: variants.1.images.0
        const variantImageMatch = field.match(/^variants\.(\d+)\.images/);
        if (variantImageMatch) {
            const idx = variantImageMatch[1];
            const $variant = $(`.variant-item[data-variant-index="${idx}"]`);
            const $error = $variant.find('.variant-error-message');
            if ($error.length) {
                $error.text(msg).show();
            }
            $variant.find('.variant-image-upload').css('border-color', '#dc3545');
            return;
        }

        const bracketName = field.split('.').reduce(function (name, part, idx) {
            return idx === 0 ? part : name + '[' + part + ']';
        }, '');

        let $input = $('#' + formPrefix + '_' + field);
        if ($input.length === 0) {
            $input = $('[name="' + field + '"]');
        }
        if ($input.length === 0) {
            $input = $('[name="' + bracketName + '"]');
        }
        if ($input.length === 0) {
            $input = $('[name="' + bracketName + '[]"]');
        }
        if ($input.length) {
            $input.addClass('is-invalid');
            // Never inject feedback inside hidden file inputs sitting in upload boxes
            if ($input.attr('type') === 'file') {
                return;
            }
            if ($input.next('.invalid-feedback').length === 0) {
                $input.after('<div class="invalid-feedback d-block">' + msg + '</div>');
            }
            if ($input.hasClass('select2-hidden-accessible')) {
                $input.next('.select2-container').addClass('is-invalid');
            }
        }
    }

    // Debug: Check what's available
    console.log('Products.js loaded');
    console.log('DataTablesProductsConfig available:', typeof DataTablesProductsConfig);
    console.log('window.DataTablesProductsConfig available:', typeof window.DataTablesProductsConfig);

    if (hasTable) {
        if (typeof DataTablesProductsConfig === 'undefined' || typeof DataTablesProductsConfig.initProductsTable !== 'function') {
            console.error('DataTablesProductsConfig missing');
        } else {
            // Initialize DataTable
            productsTable = DataTablesProductsConfig.initProductsTable();
            // Initialize filters
            initializeFilters();
            console.log("🚀 ---------------------------------------------------🚀");
            console.log("🚀 ~ products.js ~ productsTable:", productsTable);
            console.log("🚀 ---------------------------------------------------🚀");
        }
    }


    // Load related data for dropdowns on pages that need it
    if (hasTable || hasCreateForm || hasEditForm) {
        loadRelatedData();
    }

    // Clear validation errors when modals are opened
    $('#createProductModal').on('show.bs.modal', function () {
        AdminUtils.clearValidationErrors();
        loadRelatedData(function () {
            // Initialize Select2 after data is loaded
            setTimeout(function () {
                initializeSelect2();
            }, 100);
        });
    });

    $('#editProductModal').on('show.bs.modal', function () {
        AdminUtils.clearValidationErrors();
        loadRelatedData(function () {
            // Initialize Select2 after data is loaded
            setTimeout(function () {
                initializeSelect2();
            }, 100);
        });
    });

    // Clear validation errors when user starts typing
    $('#createProductModal input, #createProductModal select, #createProductModal textarea').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#editProductModal input, #editProductModal select, #editProductModal textarea').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Generate SKU functionality on create page
    if (hasCreateForm) {
        $('#generateSkuBtn').on('click', function () {
            const productName = $('#create_name').val();
            if (!productName.trim()) {
                AdminUtils.showError('Please enter a product name first');
                return;
            }
            generateSku(productName, '#create_sku', this);
        });

        // Auto-generate SKU when product name changes (if SKU is empty)
        $('#create_name').on('blur', function () {
            const productName = $(this).val();
            const skuField = $('#create_sku');
            if (productName.trim() && !skuField.val().trim()) {
                generateSku(productName, '#create_sku', $('#generateSkuBtn')[0]);
            }
        });
    }

    // Auto-generate SKU for edit modal is disabled - SKU cannot be changed after creation

    // Image Upload Functionality
    if ((hasCreateForm || hasEditForm || hasTable) && typeof window.imageUploadInitialized === 'undefined') {
        // Initialize immediately and also when modals are shown
        console.log('Initializing image upload...');
        initializeImageUpload();
        window.imageUploadInitialized = true;

        // Also initialize when modals are shown
        $('#createProductModal').on('shown.bs.modal', function () {
            console.log('Create modal shown, re-initializing image upload...');

            // Clear any existing image previews
            clearCreateImagePreviews();

            initializeImageUpload();

            // Specifically re-bind the create image input
            $('#createImageInput').off('change.imageUpload').on('change.imageUpload', function (e) {
                console.log('Create image input changed (modal shown), files:', e.target.files.length);
                handleSingleFileSelect(e.target.files[0], $('#createImagePreviewContainer'), $('#createImagePreview'));
            });
        });

        $('#editProductModal').on('shown.bs.modal', function () {
            console.log('Edit modal shown, re-initializing image upload...');
            initializeImageUpload();

            // Specifically re-bind the edit image input
            $('#editImageInput').off('change.imageUpload').on('change.imageUpload', function (e) {
                console.log('Edit image input changed (modal shown), files:', e.target.files.length);
                handleSingleFileSelect(e.target.files[0], $('#editImagePreviewContainer'), $('#editImagePreview'));
            });
        });
    }

    // Function to generate SKU
    function generateSku(productName, targetInput, button) {
        // Show loading state
        AdminUtils.showButtonLoading(button, 'Generating...');

        $.ajax({
            url: window.routes.admin.products.generateSku,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: productName
            },
            success: function (response) {
                AdminUtils.hideButtonLoading(button);

                // Set the SKU value in the input field
                $(targetInput).val(response.sku);

                // Add visual feedback to show the SKU was generated
                $(targetInput).addClass('border-success');
                setTimeout(function () {
                    $(targetInput).removeClass('border-success');
                }, 2000);

                AdminUtils.showSuccess('SKU generated successfully: ' + response.sku);
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading(button);
                AdminUtils.showError('Error generating SKU');
            }
        });
    }

    // Load related data function
    function loadRelatedData(callback) {
        if (relatedData) {
            populateDropdowns();
            if (callback) callback();
            return;
        }

        $.ajax({
            url: window.routes.admin.products.relatedData,
            method: 'GET',
            success: function (response) {
                relatedData = response;
                populateDropdowns();
                if (callback) callback();
            },
            error: function (xhr) {
                console.error('Error loading related data:', xhr);
                AdminUtils.showError('Error loading form data');
            }
        });
    }

    // Populate dropdowns with related data
    function populateDropdowns() {
        if (!relatedData) return;

        const shouldPopulateCreate = hasCreateForm;
        const shouldPopulateEdit = hasEditForm;

        // Helper to (re)populate a select, preserving value if present
        const fillSelect = (selector, items, textKey = 'name') => {
            const $sel = $(selector);
            if ($sel.length === 0) return;
            // If on edit page and options already present, skip to preserve server-selected value
            if (shouldPopulateEdit && selector.includes('#edit_') && $sel.find('option').length > 1) {
                return;
            }
            const isMultiSelect = $sel.prop('multiple');
            const currentVal = isMultiSelect ? $sel.val() || [] : $sel.val();
            $sel.empty();

            // Only add empty option for optional single selects (not required)
            if (!isMultiSelect && !$sel.prop('required')) {
                $sel.append('<option value="">' + ($sel.data('placeholder') || 'Select') + '</option>');
            }

            items.forEach(function (item) {
                $sel.append(`<option value="${item.id}">${item[textKey]}</option>`);
            });

            // Restore selected values, or pick first option for required single selects
            if (currentVal && ((isMultiSelect && currentVal.length) || (!isMultiSelect && currentVal !== ''))) {
                if (isMultiSelect && Array.isArray(currentVal)) {
                    $sel.val(currentVal);
                } else if (!isMultiSelect) {
                    $sel.val(currentVal);
                }
            } else if (!isMultiSelect && $sel.prop('required') && items.length > 0) {
                $sel.val(String(items[0].id));
            }
        };

        // Colors
        if (shouldPopulateCreate) fillSelect('#create_color_id', relatedData.colors);
        if (shouldPopulateEdit) fillSelect('#edit_color_id', relatedData.colors);

        // Designs (multi-select)
        if (shouldPopulateCreate) fillSelect('#create_design_ids', relatedData.designs);
        if (shouldPopulateEdit) fillSelect('#edit_design_ids', relatedData.designs);

        // Materials (multi-select)
        if (shouldPopulateCreate) fillSelect('#create_material_ids', relatedData.materials);
        if (shouldPopulateEdit) fillSelect('#edit_material_ids', relatedData.materials);

        // Transparencies
        if (shouldPopulateCreate) fillSelect('#create_transparency_id', relatedData.transparencies);
        if (shouldPopulateEdit) fillSelect('#edit_transparency_id', relatedData.transparencies);

        // Characteristics (multi-select)
        if (shouldPopulateCreate) fillSelect('#create_characteristic_ids', relatedData.characteristics);
        if (shouldPopulateEdit) fillSelect('#edit_characteristic_ids', relatedData.characteristics);

        // Usage
        if (shouldPopulateCreate) fillSelect('#create_usage_id', relatedData.usage);
        if (shouldPopulateEdit) fillSelect('#edit_usage_id', relatedData.usage);

        // Initialize Select2 for multi-select fields
        initializeSelect2();
    }

    // Initialize Select2 for multi-select fields
    function initializeSelect2() {
        // Multi-select fields
        const multiSelectFields = [
            '#create_design_ids',
            '#create_material_ids',
            '#create_characteristic_ids',
            '#edit_design_ids',
            '#edit_material_ids',
            '#edit_characteristic_ids'
        ];

        multiSelectFields.forEach(function (selector) {
            const $select = $(selector);
            if ($select.length > 0) {
                // Destroy existing Select2 instance if any
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                // Get selected values before initializing Select2
                const selectedValues = $select.val() || [];

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
    }


    // Create Product (full page with AJAX)
    if (hasCreateForm) {
        // Clear validation on-the-fly
        $('#createProductForm input, #createProductForm select, #createProductForm textarea').on('input change', function () {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        });

        $('#createProductForm').on('submit', function (e) {
            e.preventDefault();

            // Validate variants (must have at least one)
            if (window.productVariantManager && !window.productVariantManager.validateVariants()) {
                return;
            }

            const submitBtn = $('#createProductBtn');
            AdminUtils.showButtonLoading(submitBtn, 'Creating...');

            // Ensure variant-level hidden fields (price, sale_price, stock_quantity, status)
            (function injectVariantHiddenFields() {
                const productPrice = $('#create_price').val();
                const productSalePrice = $('#create_sale_price').val();
                const productStock = $('#create_stock_quantity').val();

                const ensureHidden = ($container, name, value) => {
                    let $hidden = $container.find(`input[type="hidden"][name="${name}"]`);
                    if ($hidden.length === 0) {
                        $hidden = $('<input>', { type: 'hidden', name });
                        $container.append($hidden);
                    }
                    $hidden.val(value ?? '');
                };

                $('.variant-item').each((i, el) => {
                    const $variant = $(el);
                    const colorName = $variant.find('.variant-color-select').attr('name');
                    const match = colorName ? colorName.match(/variants\[(\d+)\]\[color_id\]/) : null;
                    const idx = match ? match[1] : (i + 1);
                    ensureHidden($variant, `variants[${idx}][price]`, productPrice);
                    ensureHidden($variant, `variants[${idx}][sale_price]`, productSalePrice);
                    ensureHidden($variant, `variants[${idx}][stock_quantity]`, productStock || 0);
                    ensureHidden($variant, `variants[${idx}][status]`, 'active');
                });
            })();

            const formData = new FormData(this);

            // Empty optional numbers → omit so Laravel nullable|numeric passes
            ['price', 'sale_price', 'stock_quantity', 'gst'].forEach(function (field) {
                const val = formData.get(field);
                if (val === '' || val === null) {
                    formData.delete(field);
                }
            });

            // Client-side image size check (max 8MB per file)
            const maxFileBytes = 8 * 1024 * 1024;
            let oversizedFile = null;
            for (const [key, value] of formData.entries()) {
                if (value instanceof File && value.size > 0 && value.size > maxFileBytes) {
                    oversizedFile = value;
                    break;
                }
            }
            if (oversizedFile) {
                AdminUtils.hideButtonLoading(submitBtn);
                AdminUtils.showError(`"${oversizedFile.name}" is too large. Max 8MB per image.`);
                return;
            }

            $.ajax({
                url: window.routes.admin.products.store,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    if (response && response.success) {
                        AdminUtils.showSuccess(response.message || 'Product created successfully');
                        window.location = window.routes.admin.products.index;
                        return;
                    }
                    AdminUtils.showError((response && response.message) || 'Error creating product');
                },
                error: function (xhr) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    if (xhr.status === 413) {
                        AdminUtils.showError(xhr.responseJSON?.message || 'Upload too large. Use smaller images.');
                        return;
                    }
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        AdminUtils.clearValidationErrors();
                        const messages = [];

                        Object.keys(errors).forEach(function (field) {
                            const msg = errors[field][0];
                            messages.push(/failed to upload/i.test(msg)
                                ? 'Image upload failed. Use a PNG, JPG, or GIF under 8MB.'
                                : msg);
                            showProductFieldError(field, msg, 'create');
                        });

                        AdminUtils.showError(messages.length ? messages.join('\n') : 'Please fix the validation errors');
                        const $firstError = $('.product-image-error:visible, #createProductForm .is-invalid, .variant-error-message:visible').first();
                        if ($firstError.length) {
                            $('html, body').animate({ scrollTop: $firstError.offset().top - 120 }, 300);
                        }
                    } else {
                        const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Error creating product';
                        AdminUtils.showError(message);
                    }
                }
            });
        });
    }

    // Create Product (modal-only). On index modal context keep AJAX for modal button
    if ($('#createProductModal').length > 0) {
        $('#createProductBtn').on('click', function (e) {
            e.preventDefault();
            AdminUtils.showButtonLoading(this, 'Creating...');
            const formData = new FormData($('#createProductForm')[0]);
            $.ajax({
                url: window.routes.admin.products.store,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    AdminUtils.hideButtonLoading('#createProductBtn');
                    $('#createProductModal').modal('hide');
                    $('#createProductForm')[0].reset();
                    AdminUtils.clearValidationErrors();
                    clearCreateImagePreviews();
                    DataTablesProductsConfig.refresh(productsTable);
                    AdminUtils.showSuccess('Product created successfully!');
                },
                error: function (xhr) {
                    AdminUtils.hideButtonLoading('#createProductBtn');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        AdminUtils.clearValidationErrors();
                        Object.keys(errors).forEach(function (field) {
                            const input = $(`#create_${field}`);
                            if (input.length) {
                                const msg = errors[field][0];
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${msg}</div>`);
                            }
                        });
                        AdminUtils.showError('Please fix the validation errors below');
                    } else {
                        AdminUtils.showError('Error creating product');
                    }
                }
            });
        });
    }

    // Edit Product now navigates to dedicated edit page (anchors added in table)

    // Update Product (full edit page via AJAX)
    if (hasEditForm) {
        // Clear validation on-the-fly
        $('#editProductForm input, #editProductForm select, #editProductForm textarea').on('input change', function () {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        });

        $('#editProductForm').on('submit', function (e) {
            e.preventDefault();
            const submitBtn = $(this).find('button[type="submit"]');
            AdminUtils.showButtonLoading(submitBtn, 'Updating...');
            const id = $(this).find('#edit_id').val() || (window.location.pathname.match(/\/(\d+)\/?$/) || [])[1];
            // Ensure hidden fields for any newly added variants on edit page as well
            (function injectVariantHiddenFieldsEdit() {
                const productPrice = $('#edit_price').val();
                const productSalePrice = $('#edit_sale_price').val();
                const productStock = $('#edit_stock_quantity').val();

                const ensureHidden = ($container, name, value) => {
                    let $hidden = $container.find(`input[type="hidden"][name="${name}"]`);
                    if ($hidden.length === 0) {
                        $hidden = $('<input>', { type: 'hidden', name });
                        $container.append($hidden);
                    }
                    $hidden.val(value ?? '');
                };

                // Only for new variants created via UI (those inside #newVariantsContainer or lacking an id field)
                $('.variant-item').each((i, el) => {
                    const $variant = $(el);
                    if ($variant.find('input[name^="variants["][name$="[id]"]').length > 0) {
                        return; // skip existing variants that already have server-side values
                    }
                    const colorName = $variant.find('.variant-color-select').attr('name');
                    const match = colorName ? colorName.match(/variants\[(\d+)\]\[color_id\]/) : null;
                    const idx = match ? match[1] : (i + 1);
                    ensureHidden($variant, `variants[${idx}][price]`, productPrice);
                    ensureHidden($variant, `variants[${idx}][sale_price]`, productSalePrice);
                    ensureHidden($variant, `variants[${idx}][stock_quantity]`, productStock || 0);
                    ensureHidden($variant, `variants[${idx}][status]`, 'active');
                });
            })();

            const formData = new FormData(this);

            ['price', 'sale_price', 'stock_quantity', 'gst'].forEach(function (field) {
                const val = formData.get(field);
                if (val === '' || val === null) {
                    formData.delete(field);
                }
            });

            const maxFileBytes = 8 * 1024 * 1024;
            let oversizedFile = null;
            for (const [key, value] of formData.entries()) {
                if (value instanceof File && value.size > 0 && value.size > maxFileBytes) {
                    oversizedFile = value;
                    break;
                }
            }
            if (oversizedFile) {
                AdminUtils.hideButtonLoading(submitBtn);
                $('.product-image-error').text('Image size must be less than 8MB.').show();
                $('.product-image-upload').css('border-color', '#dc3545');
                AdminUtils.showError(`"${oversizedFile.name}" is too large. Max 8MB per image.`);
                return;
            }

            $.ajax({
                url: window.routes.admin.products.update.replace(':id', id),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    AdminUtils.clearValidationErrors();
                    if (response && response.success !== false) {
                        AdminUtils.showSuccess((response && response.message) || 'Product updated successfully');
                        window.location = window.routes.admin.products.index;
                        return;
                    }
                    AdminUtils.showError((response && response.message) || 'Error updating product');
                },
                error: function (xhr) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    if (xhr.status === 413) {
                        AdminUtils.showError(xhr.responseJSON?.message || 'Upload too large. Use smaller images.');
                        return;
                    }
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        AdminUtils.clearValidationErrors();
                        const messages = [];
                        Object.keys(errors).forEach(function (field) {
                            const msg = errors[field][0];
                            messages.push(/failed to upload/i.test(msg)
                                ? 'Image upload failed. Use a PNG, JPG, or GIF under 8MB.'
                                : msg);
                            showProductFieldError(field, msg, 'edit');
                        });
                        AdminUtils.showError(messages.length ? messages.join('\n') : 'Please fix the validation errors');
                        const $firstError = $('.product-image-error:visible, #editProductForm .is-invalid, .variant-error-message:visible').first();
                        if ($firstError.length) {
                            $('html, body').animate({ scrollTop: $firstError.offset().top - 120 }, 300);
                        }
                    } else {
                        AdminUtils.showError(xhr.responseJSON?.message || xhr.responseJSON?.error || 'Error updating product');
                    }
                }
            });
        });
    }

    // Update Product (modal-only). On index modal context keep AJAX
    if ($('#editProductModal').length > 0) {
        $(document).on('submit', '#editProductForm', function (e) {
            e.preventDefault();
            const submitBtn = $(this).find('button[type="submit"]');
            AdminUtils.showButtonLoading(submitBtn, 'Updating...');
            const id = $('#edit_id').val();
            const formData = new FormData(this);
            $.ajax({
                url: window.routes.admin.products.update.replace(':id', id),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    AdminUtils.hideButtonLoading(submitBtn);
                    $('#editProductModal').modal('hide');
                    AdminUtils.clearValidationErrors();
                    DataTablesProductsConfig.refresh(productsTable);
                    AdminUtils.showSuccess('Product updated successfully!');
                },
                error: function (xhr) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        AdminUtils.clearValidationErrors();
                        Object.keys(errors).forEach(function (field) {
                            const input = $(`#edit_${field}`);
                            if (input.length) {
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                        });
                        AdminUtils.showError('Please fix the validation errors below');
                    } else {
                        AdminUtils.showError('Error updating product');
                    }
                }
            });
        });
    }

    // Delete Product (only relevant on table page)
    if (hasTable) {
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            deleteProductId = $(this).data('id');
            $('#deleteProductModal').modal('show');
        });

        $('#confirmDeleteProduct').on('click', function () {
            AdminUtils.showButtonLoading(this, 'Deleting...');
            $.ajax({
                url: window.routes.admin.products.destroy.replace(':id', deleteProductId),
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    AdminUtils.hideButtonLoading('#confirmDeleteProduct');
                    $('#deleteProductModal').modal('hide');
                    DataTablesProductsConfig.refresh(productsTable);
                    AdminUtils.showSuccess('Product deleted successfully!');
                },
                error: function () {
                    AdminUtils.hideButtonLoading('#confirmDeleteProduct');
                    AdminUtils.showError('Error deleting product');
                }
            });
        });
    }

    // Image Upload Functions
    function initializeImageUpload() {
        console.log('Setting up image upload handlers...');

        // Check if elements exist
        console.log('Create upload area exists:', $('#createUploadArea').length);
        console.log('Create image input exists:', $('#createImageInput').length);
        console.log('Edit upload area exists:', $('#editUploadArea').length);
        console.log('Edit image input exists:', $('#editImageInput').length);

        // File input is now overlaid on the upload area, so we don't need click handlers
        console.log('File inputs are now overlaid on upload areas - no click handlers needed');

        // File input change handlers - single image upload
        $('#createImageInput').off('change.imageUpload').on('change.imageUpload', function (e) {
            console.log('Create image input changed (direct), files:', e.target.files.length);
            console.log('Preview container exists:', $('#createImagePreviewContainer').length);
            console.log('Preview image exists:', $('#createImagePreview').length);
            handleSingleFileSelect(e.target.files[0], $('#createImagePreviewContainer'), $('#createImagePreview'));
        });

        $('#editImageInput').off('change.imageUpload').on('change.imageUpload', function (e) {
            console.log('Edit image input changed (direct), files:', e.target.files.length);
            handleSingleFileSelect(e.target.files[0], $('#editImagePreviewContainer'), $('#editImagePreview'));
        });

        // Also use event delegation as fallback
        $(document).off('change.imageUploadDelegated').on('change.imageUploadDelegated', '#createImageInput', function (e) {
            console.log('Create image input changed (delegated), files:', e.target.files.length);
            console.log('Delegated - Preview container exists:', $('#createImagePreviewContainer').length);
            console.log('Delegated - Preview image exists:', $('#createImagePreview').length);
            handleSingleFileSelect(e.target.files[0], $('#createImagePreviewContainer'), $('#createImagePreview'));
        });

        $(document).off('change.imageUploadDelegated').on('change.imageUploadDelegated', '#editImageInput', function (e) {
            console.log('Edit image input changed (delegated), files:', e.target.files.length);
            handleSingleFileSelect(e.target.files[0], $('#editImagePreviewContainer'), $('#editImagePreview'));
        });

        // Drag and drop handlers
        setupDragAndDrop();

        // Existing image management
        setupExistingImageManagement();
    }

    function setupDragAndDrop() {
        $(document).off('dragover.imageUpload dragover.imageUpload dragleave.imageUpload drop.imageUpload')
            .on('dragover.imageUpload', '#createUploadArea, #editUploadArea', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            })
            .on('dragleave.imageUpload', '#createUploadArea, #editUploadArea', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            })
            .on('drop.imageUpload', '#createUploadArea, #editUploadArea', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files && files.length > 0) {
                    const uploadArea = $(this);
                    const previewContainer = uploadArea.attr('id') === 'createUploadArea' ?
                        $('#createImagePreviewContainer') : $('#editImagePreviewContainer');
                    const previewImg = uploadArea.attr('id') === 'createUploadArea' ?
                        $('#createImagePreview') : $('#editImagePreview');

                    handleSingleFileSelect(files[0], previewContainer, previewImg);
                }
            });
    }


    function handleFileSelect(files, previewContainer, previews) {
        console.log('handleFileSelect called with files:', files ? files.length : 0);
        console.log('Preview container:', previewContainer.length, 'Previews:', previews.length);

        if (!files || !files.length) {
            console.log('No files to process');
            return;
        }

        const maxFiles = 10;
        const maxSize = 8 * 1024 * 1024; // 8MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        if (files.length > maxFiles) {
            AdminUtils.showError(`Maximum ${maxFiles} files allowed`);
            return;
        }

        Array.from(files).forEach(file => {
            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                AdminUtils.showError(`Invalid file type: ${file.name}. Only JPG, PNG, and GIF are allowed.`);
                return;
            }

            // Validate file size
            if (file.size > maxSize) {
                AdminUtils.showError(`File too large: ${file.name}. Maximum size is 8MB.`);
                return;
            }

            // Create preview
            createImagePreview(file, previewContainer, previews);
        });
    }

    function handleSingleFileSelect(file, previewContainer, previewImg) {
        console.log('handleSingleFileSelect called with file:', file ? file.name : 'null');
        console.log('Preview container length:', previewContainer.length, 'Preview img length:', previewImg.length);

        if (!file) {
            return;
        }

        // Get upload area for visual feedback
        const uploadArea = previewContainer.closest('.image-upload-wrapper').find('.upload-area-modern');

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showUploadError(uploadArea, `Invalid file type: ${file.name}. Only JPG, PNG, and GIF are allowed.`);
            AdminUtils.showError(`Invalid file type: ${file.name}. Only JPG, PNG, and GIF are allowed.`);
            return;
        }

        // Validate file size (8MB) — on select
        const maxSize = 8 * 1024 * 1024; // 8MB
        if (file.size > maxSize) {
            showUploadError(uploadArea, `File too large: ${file.name}. Maximum size is 8MB.`);
            AdminUtils.showError(`File too large: ${file.name}. Maximum size is 8MB.`);
            // Clear the file input that triggered this
            const $wrapper = previewContainer.closest('.image-upload-wrapper');
            $wrapper.find('input[type="file"]').val('');
            $('#productImageInput').val('');
            $('.product-image-error').text('Image size must be less than 8MB.').show();
            return;
        }

        // Show success state
        showUploadSuccess(uploadArea);

        // Show preview container
        previewContainer.show();

        // Create preview using FileReader
        const reader = new FileReader();
        reader.onload = function (e) {
            console.log('FileReader loaded, setting image src:', e.target.result.substring(0, 50) + '...');
            previewImg.attr('src', e.target.result);
            console.log('Image src set, current src:', previewImg.attr('src').substring(0, 50) + '...');
        };
        reader.readAsDataURL(file);
    }

    // Show upload success state
    function showUploadSuccess(uploadArea) {
        uploadArea.removeClass('error').addClass('success');
        uploadArea.find('.upload-icon-check').show();
        uploadArea.find('.upload-icon-modern').hide();

        // Reset after 2 seconds
        setTimeout(() => {
            uploadArea.removeClass('success');
            uploadArea.find('.upload-icon-check').hide();
            uploadArea.find('.upload-icon-modern').show();
        }, 2000);
    }

    // Show upload error state
    function showUploadError(uploadArea, message) {
        uploadArea.removeClass('success').addClass('error');

        // Reset after 3 seconds
        setTimeout(() => {
            uploadArea.removeClass('error');
        }, 3000);
    }

    function createImagePreview(file, previewContainer, previews) {
        console.log('createImagePreview called for file:', file.name);
        console.log('Preview container length:', previewContainer.length, 'Previews length:', previews.length);

        const reader = new FileReader();

        reader.onload = function (e) {
            console.log('FileReader loaded, creating preview HTML...');
            const previewHtml = `
                <div class="col-md-3 mb-2">
                    <div class="image-preview-item">
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail">
                        <div class="image-actions">
                            <button type="button" class="btn btn-sm btn-danger remove-image">
                                <i class="bx bx-trash"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary set-primary">
                                <i class="bx bx-star"></i>
                            </button>
                        </div>
                        <div class="image-info">
                            <small class="text-muted">${file.name}</small>
                            <br>
                            <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                        </div>
                    </div>
                </div>
            `;

            console.log('Appending preview HTML...');
            previews.append(previewHtml);
            previewContainer.show();
            console.log('Preview container shown');
        };

        reader.readAsDataURL(file);
    }

    function setupExistingImageManagement() {
        // Remove existing image
        $(document).on('click', '.remove-existing-image', function () {
            const mediaId = $(this).data('media-id');
            const imageContainer = $(this).closest('.current-image-item');

            if (confirm('Are you sure you want to delete this image?')) {
                // Add to hidden input for deletion
                const deleteInput = $('<input>').attr({
                    type: 'hidden',
                    name: 'delete_media_ids[]',
                    value: mediaId
                });
                $('form').append(deleteInput);

                imageContainer.fadeOut(300, function () {
                    $(this).remove();
                });

                AdminUtils.showSuccess('Image marked for deletion');
            }
        });

        // Set primary image
        $(document).on('click', '.set-primary-image', function () {
            const mediaId = $(this).data('media-id');

            // Remove primary badge from all images
            $('.badge.bg-success').remove();

            // Add primary badge to clicked image
            $(this).closest('.image-actions').append('<span class="badge bg-success">Primary</span>');

            // Add hidden input for primary image
            $('input[name="primary_media_id"]').remove();
            const primaryInput = $('<input>').attr({
                type: 'hidden',
                name: 'primary_media_id',
                value: mediaId
            });
            $('form').append(primaryInput);

            AdminUtils.showSuccess('Primary image updated');
        });
    }

    // Remove new image preview
    $(document).on('click', '.remove-image', function () {
        $(this).closest('.col-md-3').fadeOut(300, function () {
            $(this).remove();
        });
    });

    // Remove new single image preview
    $(document).on('click', '.remove-new-image', function () {
        const previewContainer = $(this).closest('.image-preview-modern');
        const input = previewContainer.closest('.image-upload-wrapper').find('input[type="file"]');

        // Clear the input
        input.val('');

        // Hide preview container
        previewContainer.hide();

        // Clear the image src
        previewContainer.find('img').attr('src', '');

        // Reset upload area state
        const uploadArea = previewContainer.closest('.image-upload-wrapper').find('.upload-area-modern');
        uploadArea.removeClass('success error');
        uploadArea.find('.upload-icon-check').hide();
        uploadArea.find('.upload-icon-modern').show();
    });

    // Clear create image previews
    function clearCreateImagePreviews() {
        $('#createImagePreviewContainer').hide();
        $('#createImagePreview').attr('src', '');
        $('#createImageInput').val('');

        // Reset upload area state
        const uploadArea = $('#createUploadArea');
        uploadArea.removeClass('success error');
        uploadArea.find('.upload-icon-check').hide();
        uploadArea.find('.upload-icon-modern').show();
    }

    // Initialize filters functionality
    function initializeFilters() {
        loadFilterOptions();
        bindFilterEvents();

        $('#clearFilters').on('click', function () {
            clearAllFilters();
        });

        $('#clearSearchBtn').on('click', function () {
            $('#searchFilter').val('');
            $(this).hide();
            applyFilters();
        });

        $('#activeFilterChipList').on('click', '.products-filter-chip', function () {
            const target = $(this).data('target');
            if (target === 'searchFilter') {
                $('#searchFilter').val('');
                $('#clearSearchBtn').hide();
            } else {
                $('#' + target).val('');
            }
            applyFilters();
        });

        updateFilterUiState();
    }

    // Load filter options (colors, materials, designs, etc.)
    function loadFilterOptions() {
        $.ajax({
            url: window.routes.admin.products.relatedData,
            method: 'GET',
            success: function (response) {
                const appendOptions = function ($select, items) {
                    if (!$select.length || !items) return;
                    items.forEach(function (item) {
                        $select.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                };

                appendOptions($('#colorFilter'), response.colors);
                appendOptions($('#materialFilter'), response.materials);
                appendOptions($('#designFilter'), response.designs);
                appendOptions($('#transparencyFilter'), response.transparencies);
                appendOptions($('#characteristicFilter'), response.characteristics);
                appendOptions($('#usageFilter'), response.usage);
            },
            error: function (xhr) {
                console.error('Error loading filter options:', xhr);
            }
        });
    }

    // Bind filter events
    function bindFilterEvents() {
        let searchTimeout;
        $('#searchFilter').on('input', function () {
            const hasValue = $.trim($(this).val()).length > 0;
            $('#clearSearchBtn').toggle(hasValue);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                applyFilters();
            }, 400);
        });

        $('#colorFilter, #materialFilter, #designFilter, #transparencyFilter, #characteristicFilter, #usageFilter, #typeFilter, #productBaseFilter, #statusFilter').on('change', function () {
            applyFilters();
        });
    }

    function getActiveFilters() {
        const defs = [
            { key: 'search_filter', id: 'searchFilter', label: 'Search', isSearch: true },
            { key: 'color_filter', id: 'colorFilter', label: 'Color' },
            { key: 'material_filter', id: 'materialFilter', label: 'Material' },
            { key: 'design_filter', id: 'designFilter', label: 'Design' },
            { key: 'transparency_filter', id: 'transparencyFilter', label: 'Transparency' },
            { key: 'characteristic_filter', id: 'characteristicFilter', label: 'Characteristic' },
            { key: 'usage_filter', id: 'usageFilter', label: 'Usage' },
            { key: 'type_filter', id: 'typeFilter', label: 'Type' },
            { key: 'product_base_filter', id: 'productBaseFilter', label: 'Base' },
            { key: 'status_filter', id: 'statusFilter', label: 'Status' }
        ];

        return defs.map(function (def) {
            const $el = $('#' + def.id);
            const value = $.trim($el.val() || '');
            if (!value) return null;
            const display = def.isSearch ? value : ($el.find('option:selected').text() || value);
            return Object.assign({}, def, { value: value, display: display });
        }).filter(Boolean);
    }

    function updateFilterUiState() {
        const active = getActiveFilters();
        const count = active.length;

        $('#clearFilters').toggle(count > 0);

        const $chipsWrap = $('#activeFilterChips');
        const $chipList = $('#activeFilterChipList');
        $chipList.empty();

        if (count === 0) {
            $chipsWrap.hide();
            return;
        }

        $('#activeFilterCount').text(count === 1 ? '1 filter' : count + ' filters');
        active.forEach(function (f) {
            const safeLabel = $('<div>').text(f.label).html();
            const safeDisplay = $('<div>').text(f.display).html();
            $chipList.append(
                `<button type="button" class="products-filter-chip" data-target="${f.id}" title="Remove ${safeLabel}">
                    <span>${safeLabel}: ${safeDisplay}</span>
                    <i class="bx bx-x"></i>
                </button>`
            );
        });
        $chipsWrap.show();
    }

    // Apply filters to DataTable
    function applyFilters() {
        updateFilterUiState();
        if (!productsTable) return;

        const filters = {
            search_filter: $('#searchFilter').val(),
            color_filter: $('#colorFilter').val(),
            material_filter: $('#materialFilter').val(),
            design_filter: $('#designFilter').val(),
            transparency_filter: $('#transparencyFilter').val(),
            characteristic_filter: $('#characteristicFilter').val(),
            usage_filter: $('#usageFilter').val(),
            type_filter: $('#typeFilter').val(),
            product_base_filter: $('#productBaseFilter').val(),
            status_filter: $('#statusFilter').val()
        };

        productsTable.ajax.url(buildFilteredUrl(filters)).load();
    }

    // Build filtered URL
    function buildFilteredUrl(filters) {
        let url = window.routes.admin.products.index + '?';
        const params = [];

        Object.keys(filters).forEach(function (key) {
            if (filters[key] && filters[key] !== '') {
                params.push(key + '=' + encodeURIComponent(filters[key]));
            }
        });

        return url + params.join('&');
    }

    // Clear all filters
    function clearAllFilters() {
        $('#searchFilter').val('');
        $('#clearSearchBtn').hide();
        $('#colorFilter').val('');
        $('#materialFilter').val('');
        $('#designFilter').val('');
        $('#transparencyFilter').val('');
        $('#characteristicFilter').val('');
        $('#usageFilter').val('');
        $('#typeFilter').val('');
        $('#productBaseFilter').val('');
        $('#statusFilter').val('');

        updateFilterUiState();

        if (productsTable) {
            productsTable.ajax.url(window.routes.admin.products.index).load();
        }
    }

    // Set primary for new images
    $(document).on('click', '.set-primary', function () {
        // Remove primary class from all new images
        $('.set-primary').removeClass('btn-success').addClass('btn-primary');
        $('.set-primary i').removeClass('bxs-star-fill').addClass('bx-star');

        // Set this image as primary
        $(this).removeClass('btn-primary').addClass('btn-success');
        $(this).find('i').removeClass('bx-star').addClass('bxs-star-fill');

        AdminUtils.showSuccess('Primary image selected');
    });
});