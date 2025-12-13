@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Colors Management</h4>
                        <p class="text-muted mb-0">Manage curtain colors and their values</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createColorModal">
                            <i class="icon-base bx bx-plus me-1"></i>
                            Add New Color
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colors Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Colors List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="colorsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Color Value</th>
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
    @include('admin.colors.modals')
@endsection
