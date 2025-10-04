<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title> @yield('title') - Stack Master </title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src=" {{ asset('assets/js/plugin/webfont/webfont.min.js') }} "></script>

    <!-- CSS Files -->
    <link rel="stylesheet" href=" {{ asset('assets/css/bootstrap.min.css') }} " />
    <link rel="stylesheet" href=" {{ asset('assets/css/plugins.min.css') }} " />
    <link rel="stylesheet" href=" {{ asset('assets/css/kaiadmin.min.css') }} " />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href=" {{ asset('assets/css/demo.css') }} " />

    @stack('styles')
</head>

<body>
    <div class="wrapper">
        {{-- inject content --}}
        @yield('content')
    </div>
    </div>
</body>

</html>
