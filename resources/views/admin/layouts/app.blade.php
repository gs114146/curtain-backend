<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->

    <link rel="icon" href="{{ asset('admin/assets/img/favicon/favicon.ico') }}" sizes="32x32">
    <title>{{ env('APP_NAME') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />

    @include('admin.layouts.css')

    <!-- Dark Mode CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('admin/css/dark-mode.css') }}"> --}}

    <style>
        html {
            scroll-behavior: smooth;
        }

        /* Modern Notification Animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Enhanced notification styling */
        .notification-container .alert {
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .notification-container .alert:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <input type="hidden" id="utp" value="{{ auth()->user()?->id }}">
</head>

<body>
    <div class="loader-container d-none" id="loader-container">
        <div class="loader"></div>
    </div>
    @guest
        @yield('content')
    @endguest

    @auth
        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                <!-- Menu -->
                @include('admin.layouts.sidebar')
                <!-- / Menu -->
                <!-- Layout container -->
                <div class="layout-page">
                    @include('admin.layouts.toasts')
                    <!-- Navbar -->
                    @include('admin.layouts.navbar')
                    <!-- / Navbar -->

                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        <div class="container-fluid flex-grow-1 container-p-y">
                            @yield('content')
                        </div>
                    </div>
                    <!-- / Content wrapper -->

                    <!-- Footer -->
                    @include('admin.layouts.footer')
                    <!-- /Footer -->
                </div>

            </div>
        </div>
    @endauth

    @include('admin.layouts.js')
    @stack('scripts')
    <!-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
     -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

</body>

</html>
