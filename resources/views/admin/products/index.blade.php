@extends('admin.layouts.app')

@section('title', 'Products Management')

@section('content')
    <div class="container-fluid">
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
                        <!-- Professional filter toolbar -->
                        <div class="products-filter-toolbar mb-4" id="productsFilterToolbar">
                            <div class="products-filter-main">
                                <div class="products-search-wrap">
                                    <i class="bx bx-search products-search-icon"></i>
                                    <input type="text" class="form-control products-search-input" id="searchFilter"
                                        placeholder="Search by name, SKU, or hanger ID..." autocomplete="off">
                                    <button type="button" class="products-search-clear" id="clearSearchBtn" title="Clear search" style="display:none;">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>

                                <div class="products-filter-primary">
                                    <select class="form-select form-select-sm" id="colorFilter" data-filter-label="Color">
                                        <option value="">Color</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="materialFilter" data-filter-label="Material">
                                        <option value="">Material</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="designFilter" data-filter-label="Design">
                                        <option value="">Design</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="transparencyFilter" data-filter-label="Transparency">
                                        <option value="">Transparency</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="characteristicFilter" data-filter-label="Characteristic">
                                        <option value="">Characteristics</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="usageFilter" data-filter-label="Usage">
                                        <option value="">Usage</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="typeFilter" data-filter-label="Type">
                                        <option value="">Type</option>
                                        <option value="product_listing">Product Listing</option>
                                        <option value="view_qualities">View Qualities</option>
                                        <option value="upholstery">Upholstery</option>
                                        <option value="latest">Latest</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="productBaseFilter" data-filter-label="Base">
                                        <option value="">Base</option>
                                        <option value="curtain">Curtain</option>
                                        <option value="upholstery">Upholstery</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="statusFilter" data-filter-label="Status">
                                        <option value="">Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>

                                <div class="products-filter-actions">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearFilters" style="display:none;">
                                        <i class="bx bx-x me-1"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <div class="products-filter-chips" id="activeFilterChips" style="display:none;">
                                <span class="products-filter-count" id="activeFilterCount">0 filters</span>
                                <div class="products-filter-chip-list" id="activeFilterChipList"></div>
                            </div>
                        </div>

                        <div class="products-table-wrap">
                            <table class="table table-bordered w-100" id="productsTable">
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
