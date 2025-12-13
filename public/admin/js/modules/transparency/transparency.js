$(document).ready(function () {
    // Only run on transparency page
    if ($('#transparencyTable').length === 0) {
        return;
    }

    let transparencyTable = null;
    let deleteTransparencyId = null;

    // Debug: Check what's available
    console.log('Transparency.js loaded');
    console.log('DataTablesTransparencyConfig available:', typeof DataTablesTransparencyConfig);
    console.log('window.DataTablesTransparencyConfig available:', typeof window.DataTablesTransparencyConfig);

    // Check if DataTablesTransparencyConfig is available
    if (typeof DataTablesTransparencyConfig === 'undefined') {
        console.error('DataTablesTransparencyConfig is not loaded');
        return;
    }

    // Check if initTransparencyTable method exists
    if (typeof DataTablesTransparencyConfig.initTransparencyTable !== 'function') {
        console.error('DataTablesTransparencyConfig.initTransparencyTable is not a function');
        console.log('Available methods:', Object.getOwnPropertyNames(DataTablesTransparencyConfig));
        return;
    }

    // Initialize DataTable
    transparencyTable = DataTablesTransparencyConfig.initTransparencyTable();

    // Clear validation errors when modals are opened
    $('#createTransparencyModal').on('show.bs.modal', function () {
        AdminUtils.clearValidationErrors();
    });

    // Clear validation errors when user starts typing
    $('#createTransparencyModal input, #createTransparencyModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#editTransparencyModal input, #editTransparencyModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Create Transparency
    $('#createTransparencyBtn').on('click', function (e) {
        e.preventDefault();

        // Show loading state
        AdminUtils.showButtonLoading(this, 'Creating...');

        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#create_name').val(),
            status: $('#create_status').val()
        };

        $.ajax({
            url: window.routes.admin.transparency.store,
            method: 'POST',
            data: formData,
            success: function (response) {
                AdminUtils.hideButtonLoading('#createTransparencyBtn');
                $('#createTransparencyModal').modal('hide');
                $('#createTransparencyForm')[0].reset();
                AdminUtils.clearValidationErrors();
                DataTablesTransparencyConfig.refresh(transparencyTable);
                AdminUtils.showSuccess('Transparency created successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#createTransparencyBtn');
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
                    AdminUtils.showError('Error creating transparency');
                }
            }
        });
    });

    // Edit Transparency
    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.ajax({
            url: window.routes.admin.transparency.show.replace(':id', id),
            method: 'GET',
            success: function (response) {
                AdminUtils.clearValidationErrors();
                $('#edit_id').val(response.id);
                $('#edit_name').val(response.name);
                $('#edit_status').val(response.status);
                $('#editTransparencyModal').modal('show');
            }
        });
    });

    // Update Transparency
    $('#editTransparencyForm').on('submit', function (e) {
        e.preventDefault();

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        AdminUtils.showButtonLoading(submitBtn, 'Updating...');

        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: window.routes.admin.transparency.update.replace(':id', id),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                AdminUtils.hideButtonLoading(submitBtn);
                $('#editTransparencyModal').modal('hide');
                AdminUtils.clearValidationErrors();
                DataTablesTransparencyConfig.refresh(transparencyTable);
                AdminUtils.showSuccess('Transparency updated successfully!');
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
                    AdminUtils.showError('Error updating transparency');
                }
            }
        });
    });

    // Delete Transparency
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        deleteTransparencyId = $(this).data('id');
        $('#deleteTransparencyModal').modal('show');
    });

    $('#confirmDeleteTransparency').on('click', function () {
        // Show loading state
        AdminUtils.showButtonLoading(this, 'Deleting...');

        $.ajax({
            url: window.routes.admin.transparency.destroy.replace(':id', deleteTransparencyId),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                AdminUtils.hideButtonLoading('#confirmDeleteTransparency');
                $('#deleteTransparencyModal').modal('hide');
                DataTablesTransparencyConfig.refresh(transparencyTable);
                AdminUtils.showSuccess('Transparency deleted successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#confirmDeleteTransparency');
                AdminUtils.showError('Error deleting transparency');
            }
        });
    });
});
