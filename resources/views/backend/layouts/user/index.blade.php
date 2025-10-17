@extends('backend.app', ['title' => 'Users'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Users</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Index</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-4 -->
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <div class="card product-sales-main">

                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">User List</h3>

                                <div>
                                    <select id="roleFilter" class="form-select px-5">
                                        <option value="all">All</option>
                                        <option value="user">User</option>
                                        <option value="dj">DJ</option>
                                        <option value="venue">Venue</option>
                                        <option value="promoter">Promoter</option>
                                        <option value="artist">Artist</option>
                                    </select>
                                </div>
                            </div>


                            <div class="card-body">
                                <div class="table-reponsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Profession</th>
                                                <th>Address</th>
                                                <th>Country</th>
                                                <th>City</th>
                                                <th>Joined At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });
            if (!$.fn.DataTable.isDataTable('#datatable')) {
                let dTable = $('#datatable').DataTable({
                    order: [],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    processing: true,
                    responsive: true,
                    serverSide: true,

                    language: {
                        processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                        </div>`
                    },

                    scroller: {
                        loadingIndicator: false
                    },
                    pagingType: "full_numbers",
                    dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",

                    ajax: {
                        url: "{{ route('admin.user.index') }}",
                        type: "GET",
                        data: function(d) {
                            d.role = $('#roleFilter').val();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'role',
                            name: 'role',
                        },
                        {
                            data: 'profession',
                            name: 'profession',
                            render: function(data, type, row) {
                                return data && data.length > 12 ? data.substring(0, 12) + '...' :
                                    data;
                            }
                        },
                        {
                            data: 'address',
                            name: 'address',
                            render: function(data, type, row) {
                                return data && data.length > 12 ? data.substring(0, 12) + '...' :
                                    data;
                            }
                        },
                        {
                            data: 'country',
                            name: 'country'
                        },
                        {
                            data: 'city',
                            name: 'city'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                    ],
                });


                // This is the missing piece
                $('#roleFilter').on('change', function() {
                    dTable.ajax.reload();
                });
            }
        });
    </script>
@endpush
