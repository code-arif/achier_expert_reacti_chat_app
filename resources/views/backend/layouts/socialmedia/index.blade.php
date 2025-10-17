@extends('backend.app', ['title' => 'Social Media'])

{{-- @push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />  
@endpush --}}

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Social Media</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Social Media</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">
                <div class="col-12 col-sm-12">
                    <div class="card product-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Social Media</h3>
                        
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="bg-transparent border-bottom-0 wp-5">ID</th>
                                            <th class="bg-transparent border-bottom-0 wp-10">Social Media</th>
                                            {{-- <th class="bg-transparent border-bottom-0 wp-10">Social Media Icon</th> --}}
                                            <th class="bg-transparent border-bottom-0 wp-20">Profile Link</th>
                                            <th class="bg-transparent border-bottom-0 wp-5">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>
            <!-- ROW-4 END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script>
  $(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    let dTable = $('#datatable').DataTable({
        order: [],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        processing: true,
        responsive: true,
        serverSide: true,
        language: {
            processing: `<div class="text-center">
                <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
            </div>`,
        },
        pagingType: "full_numbers",
        dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
        ajax: {
            url: "{{ route('admin.social_media.index') }}",
            type: "GET",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'social_media', name: 'social_media' },
            // { data: 'social_media_icon', name: 'social_media_icon' },
            { data: 'profile_link', name: 'profile_link' },
            {
                data: 'action',
                name: 'action',
               
            },
        ],
    });
});

</script>
@endpush

