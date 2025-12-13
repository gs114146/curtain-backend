@php
    $folder = '/admin';
    $files = [
        '/assets/vendor/js/helpers.js',
        '/assets/js/config.js',
        '/assets/vendor/libs/jquery/jquery.js',
        '/assets/vendor/libs/popper/popper.js',
        '/assets/vendor/js/bootstrap.js',
        '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
        '/assets/vendor/js/menu.js',
        '/assets/vendor/libs/apex-charts/apexcharts.js',
        '/assets/js/main.js',
        // '/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js',
        '/assets/js/dashboards-analytics.js',
        '/assets/js/ui-toasts.js',
        '/assets/js/pages-account-settings-account.js',
        '/assets/vendor/js/custom.js',
    ];
@endphp
{{-- @dd(Route::currentRouteName()) --}}
<!-- @if (Route::current() != null && Route::current()->getName() == 'live-chat')
@php
    array_push($files, '/assets/js/app-chat.js');
@endphp
@endif -->
@foreach ($files as $file)
    <script src="{{ asset($folder . $file) }}"></script>
@endforeach

@include('admin.layouts.jquery')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Dark Mode JS -->
{{-- <script src="{{ asset('admin/js/modules/common/dark-mode.js') }}"></script> --}}
