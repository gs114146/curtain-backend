<!-- Create Characteristic Modal -->
<div class="modal fade" id="createCharacteristicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Characteristic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCharacteristicForm" onsubmit="return false;">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="create_name" class="form-label">Characteristic Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name"
                                placeholder="e.g., Fire Resistant, Water Repellent, UV Protection, Thermal Insulation"
                                required>
                            <div class="form-text">Enter the characteristic or feature description</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_status" class="form-label">Status</label>
                            <select class="form-select" id="create_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createCharacteristicBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Create Characteristic
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Characteristic Modal -->
<div class="modal fade" id="editCharacteristicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Characteristic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCharacteristicForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_name" class="form-label">Characteristic Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name"
                                placeholder="e.g., Fire Resistant, Water Repellent, UV Protection, Thermal Insulation"
                                required>
                            <div class="form-text">Enter the characteristic or feature description</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Update Characteristic
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCharacteristicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this characteristic? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="icon-base bx bx-info-circle me-1"></i>
                    <strong>Warning:</strong> Deleting this characteristic will affect all products using it.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCharacteristic">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    Delete Characteristic
                </button>
            </div>
        </div>
    </div>
</div>
