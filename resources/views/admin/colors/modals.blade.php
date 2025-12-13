<!-- Create Color Modal -->
<div class="modal fade" id="createColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createColorForm" onsubmit="return false;">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="create_name" class="form-label">Color Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name"
                                placeholder="e.g., Navy Blue, Forest Green, Crimson Red" required>
                            <div class="form-text">Enter a descriptive name for the color</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_status" class="form-label">Status</label>
                            <select class="form-select" id="create_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_color_value" class="form-label">Color Value <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="create_color_value" name="color_value"
                                placeholder="#007bff" value="#007bff" required>
                            <input type="color" class="form-control form-control-color" id="create_color_picker"
                                style="width: 50px;" value="#007bff">
                        </div>
                        <div class="form-text">Enter hex color code or use the color picker</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createColorBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Create Color
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Color Modal -->
<div class="modal fade" id="editColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editColorForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_name" class="form-label">Color Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name"
                                placeholder="e.g., Navy Blue, Forest Green, Crimson Red" required>
                            <div class="form-text">Enter a descriptive name for the color</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_color_value" class="form-label">Color Value <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="edit_color_value" name="color_value"
                                placeholder="#000000" required>
                            <input type="color" class="form-control form-control-color" id="edit_color_picker"
                                style="width: 50px;">
                        </div>
                        <div class="form-text">Enter hex color code or use the color picker</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Update Color
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this color? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="icon-base bx bx-info-circle me-1"></i>
                    <strong>Warning:</strong> Deleting this color will affect all products using it.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteColor">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    Delete Color
                </button>
            </div>
        </div>
    </div>
</div>
