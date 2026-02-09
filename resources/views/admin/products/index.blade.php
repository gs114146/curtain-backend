@extends('admin.layouts.app')

@section('title', 'Products Management')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Products Management</h5>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Add Product
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="bx bx-filter"></i> Filters
                                            <button class="btn btn-sm btn-outline-secondary ms-2" id="clearFilters">
                                                <i class="bx bx-x"></i> Clear All
                                            </button>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-5">
                                            <!-- Search -->
                                            {{-- <div class="col-md-4">
                                                <label for="searchFilter" class="form-label">Search</label>
                                                <input type="text" class="form-control" id="searchFilter"
                                                    placeholder="Search by name, SKU...">
                                            </div> --}}
                                        </div>
                                        <div class="row g-3">
                                            <!-- Color Filter -->
                                            <div class="col-md-3">
                                                <label for="colorFilter" class="form-label">Color</label>
                                                <select class="form-select" id="colorFilter">
                                                    <option value="">All Colors</option>
                                                </select>
                                            </div>

                                            <!-- Material Filter -->
                                            <div class="col-md-3">
                                                <label for="materialFilter" class="form-label">Material</label>
                                                <select class="form-select" id="materialFilter">
                                                    <option value="">All Materials</option>
                                                </select>
                                            </div>

                                            <!-- Design Filter -->
                                            <div class="col-md-3">
                                                <label for="designFilter" class="form-label">Design</label>
                                                <select class="form-select" id="designFilter">
                                                    <option value="">All Designs</option>
                                                </select>
                                            </div>

                                            <!-- Product Type -->
                                            <div class="col-md-3">
                                                <label for="typeFilter" class="form-label">Product Type</label>
                                                <select class="form-select" id="typeFilter">
                                                    <option value="">All Types</option>
                                                    <option value="product_listing">Product Listing</option>
                                                    <option value="view_qualities">View Qualities</option>
                                                    <option value="upholstery">Upholstery</option>
                                                    <option value="latest">Latest</option>
                                                </select>
                                            </div>

                                            <!-- Product Base -->
                                            <div class="col-md-3">
                                                <label for="productBaseFilter" class="form-label">Product Base</label>
                                                <select class="form-select" id="productBaseFilter">
                                                    <option value="">All Bases</option>
                                                    <option value="curtain">Curtain</option>
                                                    <option value="upholstery">Upholstery</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="productsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Variants</th>
                                        <th>Price Range</th>
                                        <th>Total Stock</th>
                                        <th>Status</th>
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

    @include('admin.products.modals')
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/product-variants.js') }}"></script>
@endpush
