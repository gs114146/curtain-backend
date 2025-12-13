$(document).ready(function () {
    // Only run on characteristics page
    if ($('#characteristicsTable').length === 0) {
        return;
    }

    let characteristicsTable = null;
    let deleteCharacteristicId = null;

    // Debug: Check what's available
    console.log('Characteristics.js loaded');
    console.log('DataTablesCharacteristicsConfig available:', typeof DataTablesCharacteristicsConfig);
    console.log('window.DataTablesCharacteristicsConfig available:', typeof window.DataTablesCharacteristicsConfig);

    // Check if DataTablesCharacteristicsConfig is available
    if (typeof DataTablesCharacteristicsConfig === 'undefined') {
        console.error('DataTablesCharacteristicsConfig is not loaded');
        return;
    }

    // Check if initCharacteristicsTable method exists
    if (typeof DataTablesCharacteristicsConfig.initCharacteristicsTable !== 'function') {
        console.error('DataTablesCharacteristicsConfig.initCharacteristicsTable is not a function');
        console.log('Available methods:', Object.getOwnPropertyNames(DataTablesCharacteristicsConfig));
        return;
    }

    // Initialize DataTable
    characteristicsTable = DataTablesCharacteristicsConfig.initCharacteristicsTable();

    // Clear validation errors when modals are opened
    $('#createCharacteristicModal').on('show.bs.modal', function () {
        AdminUtils.clearValidationErrors();
    });

    // Clear validation errors when user starts typing
    $('#createCharacteristicModal input, #createCharacteristicModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#editCharacteristicModal input, #editCharacteristicModal select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Create Characteristic
    $('#createCharacteristicBtn').on('click', function (e) {
        e.preventDefault();

        // Show loading state
        AdminUtils.showButtonLoading(this, 'Creating...');

        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#create_name').val(),
            status: $('#create_status').val()
        };

        $.ajax({
            url: window.routes.admin.characteristics.store,
            method: 'POST',
            data: formData,
            success: function (response) {
                AdminUtils.hideButtonLoading('#createCharacteristicBtn');
                $('#createCharacteristicModal').modal('hide');
                $('#createCharacteristicForm')[0].reset();
                AdminUtils.clearValidationErrors();
                DataTablesCharacteristicsConfig.refresh(characteristicsTable);
                AdminUtils.showSuccess('Characteristic created successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#createCharacteristicBtn');
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
                    AdminUtils.showError('Error creating characteristic');
                }
            }
        });
    });

    // Edit Characteristic
    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.ajax({
            url: window.routes.admin.characteristics.show.replace(':id', id),
            method: 'GET',
            success: function (response) {
                AdminUtils.clearValidationErrors();
                $('#edit_id').val(response.id);
                $('#edit_name').val(response.name);
                $('#edit_status').val(response.status);
                $('#editCharacteristicModal').modal('show');
            }
        });
    });

    // Update Characteristic
    $('#editCharacteristicForm').on('submit', function (e) {
        e.preventDefault();

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        AdminUtils.showButtonLoading(submitBtn, 'Updating...');

        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: window.routes.admin.characteristics.update.replace(':id', id),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                AdminUtils.hideButtonLoading(submitBtn);
                $('#editCharacteristicModal').modal('hide');
                AdminUtils.clearValidationErrors();
                DataTablesCharacteristicsConfig.refresh(characteristicsTable);
                AdminUtils.showSuccess('Characteristic updated successfully!');
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
                    AdminUtils.showError('Error updating characteristic');
                }
            }
        });
    });

    // Delete Characteristic
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        deleteCharacteristicId = $(this).data('id');
        $('#deleteCharacteristicModal').modal('show');
    });

    $('#confirmDeleteCharacteristic').on('click', function () {
        // Show loading state
        AdminUtils.showButtonLoading(this, 'Deleting...');

        $.ajax({
            url: window.routes.admin.characteristics.destroy.replace(':id', deleteCharacteristicId),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                AdminUtils.hideButtonLoading('#confirmDeleteCharacteristic');
                $('#deleteCharacteristicModal').modal('hide');
                DataTablesCharacteristicsConfig.refresh(characteristicsTable);
                AdminUtils.showSuccess('Characteristic deleted successfully!');
            },
            error: function (xhr) {
                AdminUtils.hideButtonLoading('#confirmDeleteCharacteristic');
                AdminUtils.showError('Error deleting characteristic');
            }
        });
    });
});
