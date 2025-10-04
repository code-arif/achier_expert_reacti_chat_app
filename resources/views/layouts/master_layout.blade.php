<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title> @yield('title') - Stack Master </title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src=" {{ asset('assets/js/plugin/webfont/webfont.min.js') }} "></script>
    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('assets/css/fonts.min.css') }}"],
            },
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href=" {{ asset('assets/css/bootstrap.min.css') }} " />
    <link rel="stylesheet" href=" {{ asset('assets/css/plugins.min.css') }} " />
    <link rel="stylesheet" href=" {{ asset('assets/css/kaiadmin.min.css') }} " />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href=" {{ asset('assets/css/demo.css') }} " />

    {{-- dropify css link --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropify/0.2.2/css/dropify.min.css">

    @stack('styles')
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('includes.sidebar')

        <div class="main-panel">

            {{-- Navbar --}}
            @include('includes.navbar')

            <div class="container">
                <div class="page-inner">

                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Dashboard</h3>
                        </div>
                    </div>

                    {{-- inject content here --}}
                    @yield('content')

                </div>
            </div>

            {{-- footer --}}
            @include('includes.footer')
        </div>

        {{-- include theme switcher --}}
        @include('partials.theme_switcher')

    </div>

    <!--   Core JS Files   -->
    <script src=" {{ asset('assets/js/core/jquery-3.7.1.min.js') }} "></script>
    <script src=" {{ asset('assets/js/core/popper.min.js') }} "></script>
    <script src=" {{ asset('assets/js/core/bootstrap.min.js') }} "></script>

    <!-- jQuery Scrollbar -->
    <script src=" {{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }} "></script>

    <!-- Chart JS -->
    <script src=" {{ asset('assets/js/plugin/chart.js/chart.min.js') }} "></script>

    <!-- jQuery Sparkline -->
    <script src=" {{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }} "></script>

    <!-- Chart Circle -->
    <script src=" {{ asset('assets/js/plugin/chart-circle/circles.min.js') }} "></script>

    <!-- Datatables -->
    <script src=" {{ asset('assets/js/plugin/datatables/datatables.min.js') }} "></script>

    <!-- Bootstrap Notify -->
    <script src=" {{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }} "></script>

    <!-- jQuery Vector Maps -->
    <script src=" {{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }} "></script>
    <script src=" {{ asset('assets/js/plugin/jsvectormap/world.js') }} "></script>

    <!-- Sweet Alert -->
    <script src=" {{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }} "></script>

    <!-- Kaiadmin JS -->
    <script src=" {{ asset('assets/js/kaiadmin.min.js') }} "></script>

    {{-- dropify js link --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropify/0.2.2/js/dropify.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src=" {{ asset('assets/js/setting-demo.js') }} "></script>
    <script src=" {{ asset('assets/js/demo.js') }} "></script>
    <script>
        $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#177dff",
            fillColor: "rgba(23, 125, 255, 0.14)",
        });

        $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#f3545d",
            fillColor: "rgba(243, 84, 93, .14)",
        });

        $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#ffa534",
            fillColor: "rgba(255, 165, 52, .14)",
        });
    </script>
</body>

</html>
