@extends('admin.layouts.app')

@section('title', 'Create Product')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create Product</h5>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Products
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="createProductForm" action="{{ route('admin.products.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="create_name" class="form-label">Hanger Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_sku" class="form-label">SKU</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="create_sku" name="sku"
                                            placeholder="Auto-generated from product name">
                                        <button type="button" class="btn btn-outline-secondary" id="generateSkuBtn">
                                            <i class="bx bx-refresh"></i> Generate
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <i class="bx bx-info-circle text-info"></i>
                                        Leave empty to auto-generate from product name
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_manual_product_id" class="form-label">Hanger ID <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_manual_product_id"
                                        name="manual_product_id" placeholder="Enter hanger ID" required>
                                    <div class="form-text">
                                        <i class="bx bx-info-circle text-info"></i>
                                        Enter a unique hanger ID
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_product_base" class="form-label">Product Base <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="create_product_base" name="product_base" required>
                                        <option value="">Select Product Base</option>
                                        <option value="curtain">Curtain</option>
                                        <option value="upholstery">Upholstery</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Product Image</label>
                                    <div class="product-image-upload p-3 rounded border d-flex align-items-center justify-content-center"
                                        style="cursor:pointer; min-height:120px; background-color: #f8f9fa;">
                                        <div class="text-center">
                                            <i class="bx bx-cloud-upload me-2" style="font-size: 24px; color: #6c757d;"></i>
                                            <div>Click to upload image or drag & drop</div>
                                            <small class="text-muted">PNG, JPG, GIF up to 10MB (Optional)</small>
                                        </div>
                                        <input type="file" id="productImageInput" name="image" accept="image/*"
                                            style="display: none;">
                                    </div>
                                    <div class="product-image-error text-danger small mt-1" style="display:none;"></div>
                                    <div class="product-image-preview d-flex gap-2 flex-wrap mt-2"></div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="create_description" class="form-label">Hanger Description <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="create_description" name="description" rows="3" required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_dimensions" class="form-label">Width <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_dimensions" name="dimensions"
                                        placeholder="e.g., 120cm x 150cm" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_weight" class="form-label">Weight <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_weight" name="weight"
                                        placeholder="e.g., 2.5kg" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_horizontal_repeat" class="form-label">Horizontal Repeat</label>
                                    <input type="text" class="form-control" id="create_horizontal_repeat"
                                        name="horizontal_repeat" placeholder="Enter horizontal repeat">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_vertical_repeat" class="form-label">Vertical Repeat</label>
                                    <input type="text" class="form-control" id="create_vertical_repeat"
                                        name="vertical_repeat" placeholder="Enter vertical repeat">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_direction" class="form-label">Direction</label>
                                    <input type="text" class="form-control" id="create_direction" name="direction"
                                        placeholder="Enter direction">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_extra_data" class="form-label">Extra Data</label>
                                    <textarea class="form-control" id="create_extra_data" name="extra_data" rows="2"
                                        placeholder="Enter extra data"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_design_ids" class="form-label">Design/Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="create_design_ids" name="design_ids[]" multiple
                                        required>
                                        @foreach ($designs as $design)
                                            <option value="{{ $design->id }}">{{ $design->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_material_ids" class="form-label">Materials <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="create_material_ids" name="material_ids[]" multiple
                                        required>
                                        @foreach ($materials as $material)
                                            <option value="{{ $material->id }}">{{ $material->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_transparency_id" class="form-label">Transparency</label>
                                    <select class="form-select" id="create_transparency_id" name="transparency_id">
                                        <option value="">Select Transparency</option>
                                        @foreach ($transparencies as $transparency)
                                            <option value="{{ $transparency->id }}">{{ $transparency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_characteristic_ids" class="form-label">Characteristics <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="create_characteristic_ids"
                                        name="characteristic_ids[]" multiple required>
                                        @foreach ($characteristics as $characteristic)
                                            <option value="{{ $characteristic->id }}">{{ $characteristic->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_usage_id" class="form-label">Usage <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="create_usage_id" name="usage_id" required>
                                        <option value="">Select Usage</option>
                                        @foreach ($usage as $usageItem)
                                            <option value="{{ $usageItem->id }}">{{ $usageItem->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="create_price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="create_price" name="price"
                                            step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="create_sale_price" class="form-label">Sale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="create_sale_price"
                                            name="sale_price" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="create_stock_quantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="create_stock_quantity"
                                        name="stock_quantity" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_type" class="form-label">Product Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="create_type" name="type" required>
                                        <option value="">Select Product Type</option>
                                        <option value="product_listing" selected>Product Listing</option>
                                        <option value="view_qualities">View Qualities</option>
                                        <option value="upholstery">Upholstery</option>
                                        <option value="latest">Latest</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_status" class="form-label">Status</label>
                                    <select class="form-select" id="create_status" name="status">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">In Stock</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="create_in_stock"
                                            name="in_stock" value="1" checked>
                                    </div>
                                </div>
                                {{-- <div class="col-md-6 mb-3">
                                    <label for="create_designs" class="form-label">Designs</label>
                                    <textarea class="form-control" id="create_designs" name="designs" rows="2"
                                        placeholder="Enter designs (comma-separated or one per line)"></textarea>
                                </div> --}}
                                <div class="col-md-6 mb-3">
                                    <label for="create_compositions" class="form-label">Compositions</label>
                                    <textarea class="form-control" id="create_compositions" name="compositions" rows="2"
                                        placeholder="Enter compositions"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_hsn_code" class="form-label">HSN Code</label>
                                    <input type="text" class="form-control" id="create_hsn_code" name="hsn_code"
                                        placeholder="Enter HSN code">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="create_gst" class="form-label">GST (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="create_gst" name="gst"
                                            step="0.01" min="0" max="100"
                                            placeholder="Enter GST percentage">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Product Variants</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="variantsContainer"></div>
                                            <div class="text-center text-muted" id="noVariantsMessage">
                                                <i class="bx bx-info-circle"></i>
                                                Add at least one color variant. Each variant only needs color and images.
                                            </div>
                                            <div class="mt-3 mb-3 text-end">
                                                <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">
                                                    <i class="bx bx-plus"></i> Add Variant
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="createProductBtn">Create
                                    Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .product-image-upload {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .product-image-upload:hover {
            border-color: #3b82f6;
            background-color: #eff6ff !important;
        }

        .product-image-upload.dragover {
            border-color: #3b82f6;
            background-color: #dbeafe !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/product-variants.js') }}"></script>
@endpush
