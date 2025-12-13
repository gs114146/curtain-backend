/**
 * Simple Colors CRUD JavaScript
 */

$(document).ready(function () {
    // Only run on colors page
    if ($('#colorsTable').length === 0) {
        return;
    }

    let colorsTable = null;
    let deleteColorId = null;

    // Debug: Check what's available
    console.log('Colors.js loaded');
    console.log('DataTablesConfig available:', typeof DataTablesConfig);
    console.log('window.DataTablesConfig available:', typeof window.DataTablesConfig);

    // Test toast functionality
    console.log('AdminUtils available:', typeof AdminUtils);
    if (typeof AdminUtils !== 'undefined') {
        // Test toast after a short delay to ensure everything is loaded
        setTimeout(() => {
            // AdminUtils.showSuccess('Toast system is working!');
        }, 500);
    }

    // Check if DataTablesConfig is available
    if (typeof DataTablesConfig === 'undefined') {
        console.error('DataTablesConfig is not loaded');
        return;
    }

    // Check if initColorsTable method exists
    if (typeof DataTablesConfig.initColorsTable !== 'function') {
        console.error('DataTablesConfig.initColorsTable is not a function');
        console.log('Available methods:', Object.getOwnPropertyNames(DataTablesConfig));
        return;
    }

    // Initialize DataTable
    colorsTable = DataTablesConfig.initColorsTable();

    // Color picker sync
    $('#create_color_picker').on('change', function () {
        $('#create_color_value').val($(this).val());
    });

    $('#edit_color_picker').on('change', function () {
        $('#edit_color_value').val($(this).val());
    });

    // Clear validation errors when modals are opened
    $('#createColorModal').on('show.bs.modal', function () {
        AdminUtils.clearValidationErrors();
    });

    // Clear validation errors when user starts typing
    $('#createColorModal input, #createColorModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();

        // For color_value field, also remove error after input-group
        if ($(this).attr('id') === 'create_color_value') {
            $(this).closest('.input-group').siblings('.invalid-feedback').remove();
        }
    });

    $('#editColorModal input, #editColorModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();

        // For color_value field, also remove error after input-group
        if ($(this).attr('id') === 'edit_color_value') {
            $(this).closest('.input-group').siblings('.invalid-feedback').remove();
        }
    });

    // Create Color
    $('#createColorBtn').on('click', function (e) {
        e.preventDefault();

        // Show loading state
        AdminUtils.showButtonLoading(this, 'Creating...');

        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#create_name').val(),
            color_value: $('#create_color_value').val(),
            status: $('#create_status').val()
        };

        $.ajax({
            url: window.routes.admin.colors.store,
            method: 'POST',
            data: formData,
            success: function (response) {
                AdminUtils.hideButtonLoading('#createColorBtn');
                $('#createColorModal').modal('hide');
                $('#createColorForm')[0].reset();
                AdminUtils.clearValidationErrors();
                DataTablesConfig.refresh(colorsTable);
                AdminUtils.showSuccess('Color created successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#createColorBtn');
                if (xhr.status === 422) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    AdminUtils.clearValidationErrors();

                    // Show field-specific errors
                    Object.keys(errors).forEach(function (field) {
                        const input = $(`#create_${field}`);
                        if (input.length) {
                            input.addClass('is-invalid');

                            // For color_value field, insert error after the input-group
                            if (field === 'color_value') {
                                input.closest('.input-group').after(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                            } else {
                                input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                        }
                    });

                    AdminUtils.showError('Please fix the validation errors below');
                } else {
                    AdminUtils.showError('Error creating color');
                }
            }
        });
    });

    // Edit Color
    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.ajax({
            url: window.routes.admin.colors.show.replace(':id', id),
            method: 'GET',
            success: function (response) {
                AdminUtils.clearValidationErrors();
                $('#edit_id').val(response.id);
                $('#edit_name').val(response.name);
                $('#edit_color_value').val(response.color_value);
                $('#edit_color_picker').val(response.color_value);
                $('#edit_status').val(response.status);
                $('#editColorModal').modal('show');
            }
        });
    });

    // Update Color
    $('#editColorForm').on('submit', function (e) {
        e.preventDefault();

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        AdminUtils.showButtonLoading(submitBtn, 'Updating...');

        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: window.routes.admin.colors.update.replace(':id', id),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                AdminUtils.hideButtonLoading(submitBtn);
                $('#editColorModal').modal('hide');
                AdminUtils.clearValidationErrors();
                DataTablesConfig.refresh(colorsTable);
                AdminUtils.showSuccess('Color updated successfully!');
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

                            // For color_value field, insert error after the input-group
                            if (field === 'color_value') {
                                input.closest('.input-group').after(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                            } else {
                                input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                        }
                    });

                    AdminUtils.showError('Please fix the validation errors below');
                } else {
                    AdminUtils.showError('Error updating color');
                }
            }
        });
    });

    // Delete Color
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        deleteColorId = $(this).data('id');
        $('#deleteColorModal').modal('show');
    });

    $('#confirmDeleteColor').on('click', function () {
        // Show loading state
        AdminUtils.showButtonLoading(this, 'Deleting...');

        $.ajax({
            url: window.routes.admin.colors.destroy.replace(':id', deleteColorId),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                AdminUtils.hideButtonLoading('#confirmDeleteColor');
                $('#deleteColorModal').modal('hide');
                DataTablesConfig.refresh(colorsTable);
                AdminUtils.showSuccess('Color deleted successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#confirmDeleteColor');
                AdminUtils.showError('Error deleting color');
            }
        });
    });
});