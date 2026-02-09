$(document).ready(function () {
    // Detect contexts
    const hasTable = $('#productsTable').length > 0;
    const hasCreateForm = $('#createProductForm').length > 0;
    const hasEditForm = $('#editProductForm').length > 0;

    let productsTable = null;
    let deleteProductId = null;
    let relatedData = null;

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

            // Only add empty option for single select
            if (!isMultiSelect) {
                $sel.append('<option value="">' + ($sel.data('placeholder') || 'Select') + '</option>');
            }

            items.forEach(function (item) {
                $sel.append(`<option value="${item.id}">${item[textKey]}</option>`);
            });

            // Restore selected values
            if (currentVal) {
                if (isMultiSelect && Array.isArray(currentVal)) {
                    $sel.val(currentVal);
                } else if (!isMultiSelect) {
                    $sel.val(currentVal);
                }
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

            $.ajax({
                url: window.routes.admin.products.store,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    AdminUtils.hideButtonLoading(submitBtn);
                    AdminUtils.showSuccess('Product created successfully');
                    // Redirect to index
                    window.location = window.routes.admin.products.index;
                },
                error: function (xhr) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        AdminUtils.clearValidationErrors();
                        // Field-specific errors
                        Object.keys(errors).forEach(function (field) {
                            // Support nested fields like variants.0.color_id
                            const normalized = field.replace(/\.(\d+)\./g, '[$1][').replace(/\.(\w+)$/g, '][$1]');
                            let $input = $(`#create_${field}`);
                            if ($input.length === 0) {
                                $input = $(`[name="${field}"]`);
                            }
                            if ($input.length === 0) {
                                $input = $(`[name="${normalized}"]`);
                            }
                            if ($input.length) {
                                $input.addClass('is-invalid');
                                $input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                        });
                        AdminUtils.showError('Please fix the highlighted errors');
                    } else {
                        AdminUtils.showError('Error creating product');
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

            $.ajax({
                url: window.routes.admin.products.update.replace(':id', id),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    AdminUtils.hideButtonLoading(submitBtn);
                    AdminUtils.clearValidationErrors();
                    AdminUtils.showSuccess('Product updated successfully');
                    // Redirect back to index
                    window.location = window.routes.admin.products.index;
                },
                error: function (xhr) {
                    AdminUtils.hideButtonLoading(submitBtn);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        AdminUtils.clearValidationErrors();
                        Object.keys(errors).forEach(function (field) {
                            const normalized = field.replace(/\.(\d+)\./g, '[$1][').replace(/\.(\w+)$/g, '][$1]');
                            let $input = $(`#edit_${field}`);
                            if ($input.length === 0) {
                                $input = $(`[name="${field}"]`);
                            }
                            if ($input.length === 0) {
                                $input = $(`[name="${normalized}"]`);
                            }
                            if ($input.length) {
                                $input.addClass('is-invalid');
                                $input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                        });
                        AdminUtils.showError('Please fix the highlighted errors');
                    } else {
                        AdminUtils.showError('Error updating product');
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
        const maxSize = 10 * 1024 * 1024; // 10MB
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
                AdminUtils.showError(`File too large: ${file.name}. Maximum size is 10MB.`);
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

        // Validate file size (10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            showUploadError(uploadArea, `File too large: ${file.name}. Maximum size is 10MB.`);
            AdminUtils.showError(`File too large: ${file.name}. Maximum size is 10MB.`);
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
        // Load filter options
        loadFilterOptions();

        // Bind filter events
        bindFilterEvents();

        // Clear filters functionality
        $('#clearFilters').on('click', function () {
            clearAllFilters();
        });
    }

    // Load filter options (colors, materials, designs)
    function loadFilterOptions() {
        $.ajax({
            url: window.routes.admin.products.relatedData,
            method: 'GET',
            success: function (response) {
                // Populate color filter
                const colorSelect = $('#colorFilter');
                response.colors.forEach(function (color) {
                    colorSelect.append(`<option value="${color.id}">${color.name}</option>`);
                });

                // Populate material filter
                const materialSelect = $('#materialFilter');
                response.materials.forEach(function (material) {
                    materialSelect.append(`<option value="${material.id}">${material.name}</option>`);
                });

                // Populate design filter
                const designSelect = $('#designFilter');
                response.designs.forEach(function (design) {
                    designSelect.append(`<option value="${design.id}">${design.name}</option>`);
                });
            },
            error: function (xhr) {
                console.error('Error loading filter options:', xhr);
            }
        });
    }

    // Bind filter events
    function bindFilterEvents() {
        // Search filter with debounce
        let searchTimeout;
        $('#searchFilter').on('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                applyFilters();
            }, 500);
        });

        // All other filters
        $('#colorFilter, #materialFilter, #designFilter, #typeFilter, #productBaseFilter').on('change', function () {
            applyFilters();
        });
    }

    // Apply filters to DataTable
    function applyFilters() {
        if (!productsTable) return;

        // Get filter values
        const filters = {
            search_filter: $('#searchFilter').val(),
            color_filter: $('#colorFilter').val(),
            material_filter: $('#materialFilter').val(),
            design_filter: $('#designFilter').val(),
            type_filter: $('#typeFilter').val(),
            product_base_filter: $('#productBaseFilter').val()
        };

        // Update DataTable with filters
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
        $('#colorFilter').val('');
        $('#materialFilter').val('');
        $('#designFilter').val('');
        $('#typeFilter').val('');
        $('#productBaseFilter').val('');

        // Reset DataTable to original URL
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