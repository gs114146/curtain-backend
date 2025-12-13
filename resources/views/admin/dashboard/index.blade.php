@extends('admin.layouts.app')
@section('content')
    <div class="row">
        <div class="col-xxl-12 mb-6 order-0">
            <div class="card">
                <div class="d-flex align-items-start row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">Welcome back, {{ $user->name }}! 🎉</h5>
                            <p class="mb-6">
                                You have <strong>{{ $totalProducts }}</strong> products in your catalog.<br />
                                <strong>{{ $activeProducts }}</strong> are currently active and
                                <strong>{{ $featuredProducts }}</strong> are featured.
                            </p>

                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">Manage
                                Products</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-6">
                            <img src="{{ asset('admin/assets/img/illustrations/man-with-laptop.png') }}" height="175"
                                alt="View Badge User" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Row -->
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 mb-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between mb-4">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base bx bx-palette"></i>
                            </span>
                        </div>
                    </div>
                    <p class="mb-1">Total Colors</p>
                    <h4 class="card-title mb-3">{{ $totalColors }}</h4>
                    <a href="{{ route('admin.colors.index') }}" class="btn btn-sm btn-outline-primary">Manage Colors</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between mb-4">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base bx bx-shape-polygon"></i>
                            </span>
                        </div>
                    </div>
                    <p class="mb-1">Total Designs</p>
                    <h4 class="card-title mb-3">{{ $totalDesigns }}</h4>
                    <a href="{{ route('admin.designs.index') }}" class="btn btn-sm btn-outline-primary">Manage
                        Designs</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between mb-4">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base bx bx-cube"></i>
                            </span>
                        </div>
                    </div>
                    <p class="mb-1">Total Materials</p>
                    <h4 class="card-title mb-3">{{ $totalMaterials }}</h4>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-sm btn-outline-primary">Manage
                        Materials</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between mb-4">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base bx bx-show"></i>
                            </span>
                        </div>
                    </div>
                    <p class="mb-1">Total Characteristics</p>
                    <h4 class="card-title mb-3">{{ $totalCharacteristics }}</h4>
                    <a href="{{ route('admin.characteristics.index') }}" class="btn btn-sm btn-outline-primary">Manage
                        Characteristics</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Categories Row -->
    <div class="row">
        <div class="col-12 col-md-4 mb-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Colors</h5>
                </div>
                <div class="card-body">
                    @if ($topColors->count() > 0)
                        @foreach ($topColors as $color)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-2"
                                    style="width: 20px; height: 20px; background-color: {{ $color->color_value }}; border: 1px solid #ddd; border-radius: 3px;">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $color->name }}</h6>
                                    <small class="text-muted">{{ $color->products_count }} products</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No colors with products yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Designs</h5>
                </div>
                <div class="card-body">
                    @if ($topDesigns->count() > 0)
                        @foreach ($topDesigns as $design)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $design->name }}</h6>
                                    <small class="text-muted">{{ $design->products_count }} products</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No designs with products yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Materials</h5>
                </div>
                <div class="card-body">
                    @if ($topMaterials->count() > 0)
                        @foreach ($topMaterials as $material)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $material->name }}</h6>
                                    <small class="text-muted">{{ $material->products_count }} products</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No materials with products yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
