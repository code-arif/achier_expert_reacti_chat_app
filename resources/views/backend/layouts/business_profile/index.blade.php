@extends('backend.app', ['title' => 'Business Profiles'])

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
                    <h1 class="page-title">Business Profiles</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Business Profiles</a></li>
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
                            <h3 class="card-title mb-0">Pending Business Profiles</h3>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="bg-transparent border-bottom-0 wp-5">ID</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">User Name</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">User Email</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">Establishment Name</th>
                                            <th class="bg-success border-bottom-0">Approve Merchant Profile</th>
                                            <th class="bg-danger border-bottom-0">Cancel Merchant Profile</th>
                                            <th class="bg-info border-bottom-0">View</th>
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
                    url: "{{ route('admin.business_profile.pending') }}",
                    type: "GET",
                },

                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'user_name', name: 'user_name', orderable: true, searchable: true },
                    { data: 'user_email', name: 'user_email', orderable: true, searchable: true },
                    { data: 'establishment_name', name: 'establishment_name', orderable: true, searchable: true },
                    { data: 'status', name: 'status', orderable: false, searchable: false, className: 'dt-center text-center' },
                    { data: 'cancel_status', name: 'cancancel_status', orderable: false, searchable: false, className: 'dt-center text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-center text-center' },
                ],
            });
        }
    });

       // Status Change Confirm Alert
       function showStatusChangeAlert(id) {
        event.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to approve the product status?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                statusChange(id);
            }
        });
    }

    // Status Change
    function statusChange(id) {
        NProgress.start();
        let url = "{{ route('admin.business_profile.approve', ':id') }}";
        $.ajax({
            type: "POST",
            url: url.replace(':id', id),
            success: function(resp) {
                NProgress.done();
                toastr.success(resp.message);
                $('#datatable').DataTable().ajax.reload();
            },
            error: function(error) {
                NProgress.done();
                toastr.error(error.message);
            }
        });
    }

    // Cancel Request Confirm Alert
    function showCancelRequestAlert(id) {
        event.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to cancel the product approval request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                cancelRequest(id);
            }
        });
    }

    // Cancel Request
    function cancelRequest(id) {
        NProgress.start();
        let url = "{{ route('admin.business_profile.cancel', ':id') }}";
        $.ajax({
            type: "POST",
            url: url.replace(':id', id),
            success: function(resp) {
                NProgress.done();
                toastr.success(resp.message);
                $('#datatable').DataTable().ajax.reload();
            },
            error: function(error) {
                NProgress.done();
                toastr.error(error.message);
            }
        });
    }
</script>
@endpush