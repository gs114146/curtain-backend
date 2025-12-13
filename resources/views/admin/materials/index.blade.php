@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Materials Management</h4>
                        <p class="text-muted mb-0">Manage curtain materials and fabrics</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createMaterialModal">
                            <i class="icon-base bx bx-plus me-1"></i>
                            Add New Material
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materials Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Materials List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="materialsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Products Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    @include('admin.materials.modals')
@endsection
