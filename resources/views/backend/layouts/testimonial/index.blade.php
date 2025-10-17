@extends('backend.app', ['title' => 'FAQs'])

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
                    <h1 class="page-title">Testimonials</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Testimonial</a></li>
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
                            <h3 class="card-title mb-0">Testimonials</h3>
                        
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="bg-transparent border-bottom-0 wp-2">ID</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">User Name</th>
                                            <th class="bg-transparent border-bottom-0 wp-5">User Avatar</th>
                                            <th class="bg-transparent border-bottom-0 wp-5">Ratings</th>
                                            <th class="bg-transparent border-bottom-0 wp-30">Comment</th>
                                            <th class="bg-transparent border-bottom-0 wp-5">Status</th>
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
            url: "{{ route('admin.testimonial.index') }}",
            type: "GET",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_name', name: 'user_name' },
            { data: 'user_avatar', name: 'user_avatar' },
            { data: 'rating', name: 'rating' },
            { data: 'comment', name: 'comment' , render: function(data, type, row) {
                            if (data.length > 20) {
                                return data.substring(0, 110) + '...';
                            } else {
                                return data;
                            }
                        }},
            { data: 'status', name: 'status' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'dt-center text-center',
            },
        ],
    });
});

// Status Change
function statusChange(id) {
    let url = "{{ route('admin.testimonial.status', ':id') }}";
    $.ajax({
        type: "POST",
        url: url.replace(':id', id),
        success: function (resp) {
            toastr.success(resp.message);
            $('#datatable').DataTable().ajax.reload();
        },
        error: function (error) {
            toastr.error("Error occurred!");
        },
    });
}

function showDeleteConfirm(id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure you want to delete this record?',
            text: 'If you delete this, it will be gone forever.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItem(id);
            }
        });
    }

    // Delete Button
    function deleteItem(id) {
        NProgress.start();
        let url = "{{ route('admin.testimonial.destroy', ':id') }}";
        let csrfToken = '{{ csrf_token() }}';
        $.ajax({
            type: "DELETE",
            url: url.replace(':id', id),
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
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