@extends('backend.app', ['title' => 'Posts List'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">User Posts Lists</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">User</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Posts</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    {{-- feature items section --}}
                    <div class="col-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Content</th>
                                                <th>Like</th>
                                                <th>Comment</th>
                                                <th>Share</th>
                                                <th>Published At</th>
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
                        url: "{{ route('admin.post.index') }}",
                        type: "GET",
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'user',
                            name: 'user'
                        },
                        {
                            data: 'content',
                            name: 'content'
                        },
                        {
                            data: 'like',
                            name: 'like'
                        },
                        {
                            data: 'comment',
                            name: 'comment'
                        },
                        {
                            data: 'share',
                            name: 'share'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                    ]
                });
            }
        });
    </script>
@endpush
