@php
    $folder = 'admin';
    $files = [
        ['path' => '/assets/vendor/fonts/iconify-icons.css', 'class' => ''],
        ['path' => '/assets/vendor/css/core.css', 'class' => 'template-customizer-core-css'],
        // ['path' => '/assets/vendor/css/theme-default.css', 'class' => 'template-customizer-theme-css'],
        ['path' => '/assets/css/demo.css', 'class' => ''],
        ['path' => '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css', 'class' => ''],
        ['path' => '/assets/vendor/css/pages/page-auth.css', 'class' => ''],
        ['path' => '/assets/vendor/libs/apex-charts/apex-charts.css', 'class' => ''],
        // ['path' => '/assets/vendor/css/pages/app-chat.css', 'class' => ''],
        ['path' => '/assets/css/custom.css', 'class' => ''],
        ['path' => '/assets/vendor/css/pages/page-misc.css', 'class' => ''],
        ['path' => '/assets/css/custom.css', 'class' => ''],
        // ['path' => '/assets/vendor/css/rtl/core.css', 'class' => ''],
    ];
@endphp
@foreach ($files as $file)
    <link rel="stylesheet" href="{{ asset($folder . $file['path']) }}" class="{{ $file['class'] }}" />
@endforeach
