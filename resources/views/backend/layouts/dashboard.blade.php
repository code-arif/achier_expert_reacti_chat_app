@extends('backend.app')

@section('title', 'Admin || Dashboard')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">{{ $totalUsers }}</h3>
                                        <p class="text-muted fs-13 mb-0">Total Users</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-primary dash ms-auto box-shadow-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
                                                <path
                                                    d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">{{ $totalEvents }}</h3>
                                        <p class="text-muted fs-13 mb-0">Total Events</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M4.5 11.5A.5.5 0 0 1 5 11h10a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5m-2-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m-2-4A.5.5 0 0 1 1 3h10a.5.5 0 0 1 0 1H1a.5.5 0 0 1-.5-.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">{{ $totalVenues }}</h3>
                                        <p class="text-muted fs-13 mb-0">Total Venues</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-info dash ms-auto box-shadow-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path
                                                    d="M4 16s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-5.95a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                                                <path
                                                    d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.4 5.4 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">{{ $totalPosts }}</h3>
                                        <p class="text-muted fs-13 mb-0">Total Posts</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-warning dash ms-auto box-shadow-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M6 1h6v7a.5.5 0 0 1-.757.429L9 7.083 6.757 8.43A.5.5 0 0 1 6 8z" />
                                                <path
                                                    d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2" />
                                                <path
                                                    d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-1 END-->

                <!-- ROW-2 -->
                <div class="row">
                    <!-- User Role Chart -->
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title"> User role distribution</h3>
                            </div>
                            <div class="card-body">
                                <div id="user-piechart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- User activity chart --}}
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">New users activity</h3>
                            </div>
                            <div class="card-body">
                                <div id="user-linechart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-2 END -->

                {{-- ROW-3 start --}}
                <div class="row">
                    <!-- New Events Creation Line Chart -->
                    <div class="col-sm-12 col-md-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">New Events Over Time</h3>
                            </div>
                            <div class="card-body">
                                <div id="event-linechart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Event Status Pie Chart -->
                    <div class="col-sm-12 col-md-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Event Status Distribution</h3>
                            </div>
                            <div class="card-body">
                                <div id="event-piechart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ROW-3 end --}}
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <!-- Load required libraries -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.36.3/dist/apexcharts.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load Google Charts for pie charts
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(fetchPaymentData);

            function fetchPaymentData() {
                fetch('/admin/dashboard/data')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error("Response wasn't JSON");
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Payment Charts
                        drawUserPieChart(data.user_roles);
                        drawNewUserRegistrationLineChart(data.new_user_registrations);
                        drawEventPieChart(data.event_statuses);
                        drawNewEventLineChart(data.new_event_creations);
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        alert('Failed to load dashboard data. Please try again later.');
                    });
            }

            // Users role pie chart
            function drawUserPieChart(pieData) {
                var chartData = [
                    ['Status', 'Count'],
                    ['User', pieData.user],
                    ['DJ', pieData.dj],
                    ['Artist', pieData.artist],
                    ['Promoter', pieData.promoter],
                    ['Venue', pieData.venue]
                ];

                var data = google.visualization.arrayToDataTable(chartData);

                var options = {
                    pieHole: 0.4,
                    colors: ['#00E396', '#FFB55A', '#FF4560', '#008FFB', '#775DD0'],
                    chartArea: {
                        width: '90%',
                        height: '80%'
                    },
                    legend: {
                        position: 'bottom'
                    }
                };

                var chart = new google.visualization.PieChart(document.getElementById('user-piechart'));
                chart.draw(data, options);
            }

            // New user registration line chart
            function drawNewUserRegistrationLineChart(lineData) {
                const categories = Object.keys(lineData);
                const counts = Object.values(lineData);
                const options = {
                    series: [{
                        name: "New Users",
                        data: counts,
                        color: '#008FFB'
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    title: {
                        text: 'New User Registrations Over Time',
                        align: 'center'
                    },
                    xaxis: {
                        categories: categories
                    },
                    yaxis: {
                        title: {
                            text: 'Number of New Users'
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };
                const chart = new ApexCharts(document.querySelector("#user-linechart"), options);
                chart.render();
            }

            // Event Status Pie Chart
            function drawEventPieChart(pieData) {
                var chartData = [
                    ['Status', 'Count'],
                    ['Going Live', pieData.going_live],
                    ['Pending', pieData.pending],
                    ['Postponed', pieData.postponed],
                    ['Cancelled', pieData.cancelled],
                    ['Completed', pieData.completed],
                ];
                var data = google.visualization.arrayToDataTable(chartData);

                var options = {
                    pieHole: 0.4,
                    colors: ['#00BFFF', '#FFA500', '#FF6347', '#808080', '#32CD32'], // Color variant
                    chartArea: {
                        width: '90%',
                        height: '80%'
                    },
                    legend: {
                        position: 'bottom'
                    }
                };

                var chart = new google.visualization.PieChart(document.getElementById('event-piechart'));
                chart.draw(data, options);
            }

            // New Event Creation Line Chart
            function drawNewEventLineChart(lineData) {
                const categories = Object.keys(lineData);
                const counts = Object.values(lineData);

                const options = {
                    series: [{
                        name: "New Events",
                        data: counts,
                        color: '#FF4500'
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    title: {
                        text: 'New Events Over Time',
                        align: 'center'
                    },
                    xaxis: {
                        categories: categories
                    },
                    yaxis: {
                        title: {
                            text: 'Number of Events'
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };

                const chart = new ApexCharts(document.querySelector("#event-linechart"), options);
                chart.render();
            }
        });
    </script>
@endpush
