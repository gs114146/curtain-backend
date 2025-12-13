@php
    // $currentRoute = request()->segments()[1];
    $activeRoute = Route::currentRouteName();
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <img src="{{ asset('logo.png') }}" alt="" class="w-100">
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        {{-- <li class="menu-item active open">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboards">Dashboards</div>
                <span class="badge rounded-pill bg-danger ms-auto">5</span>
            </a>
            <ul class="menu-sub">
                <li class="menu-item active">
                    <a href="index.html" class="menu-link">
                        <div class="text-truncate" data-i18n="Analytics">Analytics</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/html/vertical-menu-template/dashboards-crm.html"
                        target="_blank" class="menu-link">
                        <div class="text-truncate" data-i18n="CRM">CRM</div>
                        <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto">Pro</div>
                    </a>
                </li>
            </ul>
        </li> --}}


        <!-- Apps & Pages -->
        {{-- <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Apps &amp; Pages</span>
        </li> --}}
        <li class="menu-item {{ $activeRoute == 'dashboard' ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- Product Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Product Management</span>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.products') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div class="text-truncate" data-i18n="Products">Products</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ $activeRoute == 'admin.products.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.products.index') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="All Products">All Products</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Product Attributes -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Product Attributes</span>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.colors') ? 'active' : '' }}">
            <a href="{{ route('admin.colors.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-palette"></i>
                <div class="text-truncate" data-i18n="Colors">Colors</div>
            </a>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.designs') ? 'active' : '' }}">
            <a href="{{ route('admin.designs.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shape-polygon"></i>
                <div class="text-truncate" data-i18n="Designs">Designs</div>
            </a>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.materials') ? 'active' : '' }}">
            <a href="{{ route('admin.materials.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-fabric"></i>
                <div class="text-truncate" data-i18n="Materials">Materials</div>
            </a>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.transparency') ? 'active' : '' }}">
            <a href="{{ route('admin.transparency.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-show"></i>
                <div class="text-truncate" data-i18n="Transparency">Transparency</div>
            </a>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.characteristics') ? 'active' : '' }}">
            <a href="{{ route('admin.characteristics.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-star"></i>
                <div class="text-truncate" data-i18n="Characteristics">Characteristics</div>
            </a>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.usage') ? 'active' : '' }}">
            <a href="{{ route('admin.usage.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div class="text-truncate" data-i18n="Usage">Usage</div>
            </a>
        </li>

        <!-- Customer Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Customer Management</span>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.consultations') ? 'active' : '' }}">
            <a href="{{ route('admin.consultations.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div class="text-truncate" data-i18n="Appointments">Appointments</div>
            </a>
        </li>

        <li class="menu-item {{ str_starts_with($activeRoute, 'admin.contact-us') ? 'active' : '' }}">
            <a href="{{ route('admin.contact-us.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div class="text-truncate" data-i18n="Contact Us">Contact Us</div>
            </a>
        </li>

    </ul>
</aside>
