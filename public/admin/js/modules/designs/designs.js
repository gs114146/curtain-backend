$(document).ready(function () {
    // Only run on designs page
    if ($('#designsTable').length === 0) {
        return;
    }

    let designsTable = null;
    let deleteDesignId = null;

    // Debug: Check what's available
    console.log('Designs.js loaded');
    console.log('DataTablesDesignsConfig available:', typeof DataTablesDesignsConfig);
    console.log('window.DataTablesDesignsConfig available:', typeof window.DataTablesDesignsConfig);

    // Check if DataTablesDesignsConfig is available
    if (typeof DataTablesDesignsConfig === 'undefined') {
        console.error('DataTablesDesignsConfig is not loaded');
        return;
    }

    // Check if initDesignsTable method exists
    if (typeof DataTablesDesignsConfig.initDesignsTable !== 'function') {
        console.error('DataTablesDesignsConfig.initDesignsTable is not a function');
        console.log('Available methods:', Object.getOwnPropertyNames(DataTablesDesignsConfig));
        return;
    }

    // Initialize DataTable
    designsTable = DataTablesDesignsConfig.initDesignsTable();

    // Clear validation errors when modals are opened
    $('#createDesignModal').on('show.bs.modal', function () {
        AdminUtils.clearValidationErrors();
    });

    // Clear validation errors when user starts typing
    $('#createDesignModal input, #createDesignModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#editDesignModal input, #editDesignModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Create Design
    $('#createDesignBtn').on('click', function (e) {
        e.preventDefault();

        // Show loading state
        AdminUtils.showButtonLoading(this, 'Creating...');

        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#create_name').val(),
            status: $('#create_status').val()
        };

        $.ajax({
            url: window.routes.admin.designs.store,
            method: 'POST',
            data: formData,
            success: function (response) {
                AdminUtils.hideButtonLoading('#createDesignBtn');
                $('#createDesignModal').modal('hide');
                $('#createDesignForm')[0].reset();
                AdminUtils.clearValidationErrors();
                DataTablesDesignsConfig.refresh(designsTable);
                AdminUtils.showSuccess('Design created successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#createDesignBtn');
                if (xhr.status === 422) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    AdminUtils.clearValidationErrors();

                    // Show field-specific errors
                    Object.keys(errors).forEach(function (field) {
                        const input = $(`#create_${field}`);
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        }
                    });

                    AdminUtils.showError('Please fix the validation errors below');
                } else {
                    AdminUtils.showError('Error creating design');
                }
            }
        });
    });

    // Edit Design
    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.ajax({
            url: window.routes.admin.designs.show.replace(':id', id),
            method: 'GET',
            success: function (response) {
                AdminUtils.clearValidationErrors();
                $('#edit_id').val(response.id);
                $('#edit_name').val(response.name);
                $('#edit_status').val(response.status);
                $('#editDesignModal').modal('show');
            }
        });
    });

    // Update Design
    $('#editDesignForm').on('submit', function (e) {
        e.preventDefault();

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        AdminUtils.showButtonLoading(submitBtn, 'Updating...');

        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: window.routes.admin.designs.update.replace(':id', id),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                AdminUtils.hideButtonLoading(submitBtn);
                $('#editDesignModal').modal('hide');
                AdminUtils.clearValidationErrors();
                DataTablesDesignsConfig.refresh(designsTable);
                AdminUtils.showSuccess('Design updated successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading(submitBtn);
                if (xhr.status === 422) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    AdminUtils.clearValidationErrors();

                    // Show field-specific errors
                    Object.keys(errors).forEach(function (field) {
                        const input = $(`#edit_${field}`);
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        }
                    });

                    AdminUtils.showError('Please fix the validation errors below');
                } else {
                    AdminUtils.showError('Error updating design');
                }
            }
        });
    });

    // Delete Design
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        deleteDesignId = $(this).data('id');
        $('#deleteDesignModal').modal('show');
    });

    $('#confirmDeleteDesign').on('click', function () {
        // Show loading state
        AdminUtils.showButtonLoading(this, 'Deleting...');

        $.ajax({
            url: window.routes.admin.designs.destroy.replace(':id', deleteDesignId),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                AdminUtils.hideButtonLoading('#confirmDeleteDesign');
                $('#deleteDesignModal').modal('hide');
                DataTablesDesignsConfig.refresh(designsTable);
                AdminUtils.showSuccess('Design deleted successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#confirmDeleteDesign');
                AdminUtils.showError('Error deleting design');
            }
        });
    });
});