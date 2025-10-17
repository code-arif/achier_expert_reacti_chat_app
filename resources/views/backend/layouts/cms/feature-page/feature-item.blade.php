@extends('backend.app', ['title' => 'Feature item section'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Feature page - Feature item section</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Feature page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Items</li>
                        </ol>
                    </div>
                </div>
                {{-- PAGE-HEADER --}}


                <div class="row">
                    {{-- feature item header section --}}
                    <div class="col-lg-4 col-xl-4 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('cms.feature.items.hero.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        {{-- section title --}}
                                        <div class="form-group">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter title" id="title"
                                                value="{{ $item->title ?? (old('title') ?? '') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- section description --}}
                                        <div class="form-group">
                                            <label for="description" class="form-label">Description:</label>
                                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                                rows="5">{{ old('description', $item->description ?? '') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Save change</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- feature items section --}}
                    <div class="col-lg-8 col-xl-8 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <div class="card-header border-bottom">
                                    <h3 class="card-title mb-0">Feature Items List</h3>
                                    <div class="card-options ms-auto">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#itemModal" id="addItemBtn">Add Item</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Page</th>
                                                <th>Section</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Action</th>
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


    {{-- item add/edit modal --}}
    <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="itemForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="itemID">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemModalLabel">Create Feature Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <div class="row">
                            {{-- item title --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="item_title" class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" placeholder="Title"
                                        id="item_title">
                                    <span class="text-danger error-text title_error"></span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="item_description" class="form-label">Description</label>
                                    <textarea name="description" id="item_description" class="form-control" rows="3"></textarea>
                                    <span class="text-danger error-text description_error"></span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="item_status" class="form-label">Status</label>
                                    <select name="status" id="item_status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <span class="text-danger error-text status_error"></span>
                                </div>
                            </div>

                            {{-- item image --}}
                            <div class="col-md-12">
                                <div class="mb-3" id="image_show_section">
                                    <label class="form-label">Item Image</label>
                                    <input type="file" id="item_image" name="image" class="form-control dropify"
                                        accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                        url: "{{ route('cms.feature.items.index') }}",
                        type: "GET",
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'page',
                            name: 'page'
                        },
                        {
                            data: 'section',
                            name: 'section'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'image',
                            name: 'image',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ]
                });
            }


            // Reset form and open modal for adding new item
            $('#addItemBtn').click(function() {
                $('#itemModalLabel').text('Create Feature Item');
                $('#itemForm')[0].reset();
                $('#itemID').val('');
                $('.error-text').text('');

                // Rebuild the image field from scratch
                $('#image_show_section').html(`
                        <label class="form-label">Item Image</label>
                        <input type="file" id="item_image" name="image" class="form-control dropify"
                            data-default-file="{{ asset('default/placeholder-image.avif') }}" accept="image/*">
                    `);

                // Reinitialize dropify
                $('#item_image').dropify();
            });


            // Handle form submission
            $('#itemForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var id = $('#itemID').val();
                var url = id ? "{{ route('cms.feature.items.update', ':id') }}".replace(':id', id) :
                    "{{ route('cms.feature.items.store') }}";
                var method = id ? 'POST' : 'POST'; // Keep as POST for both create and update

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $(document).find('span.error-text').text('');
                        $('#submitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (response.status == 0) {
                            $.each(response.error, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            $('#itemModal').modal('hide');
                            $('#itemForm')[0].reset();
                            // Reset dropify after successful submission
                            $('.dropify').dropify('destroy');
                            $('#item_image').attr('data-default-file',
                                "{{ asset('default/placeholder-image.avif') }}").dropify();
                            $('#datatable').DataTable().ajax.reload();
                            toastr.success(response.message);
                        }
                        $('#submitBtn').prop('disabled', false).html('Save changes');
                    },
                    error: function(xhr, status, error) {
                        $('#submitBtn').prop('disabled', false).html('Save changes');
                        toastr.error('Something went wrong. Please try again.');
                    }
                });
            });

            // Edit item
            $(document).on('click', '.editItem', function() {
                var id = $(this).data('id');
                var url = "{{ route('cms.feature.items.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    $('#itemModalLabel').text('Edit Feature Item');
                    $('#itemID').val(response.data.id);
                    $('#item_title').val(response.data.title);
                    $('#item_description').val(response.data.description);
                    $('#item_status').val(response.data.status);

                    if (response.data.image) {
                        let imageUrl = response.data.image ? "{{ asset('') }}" + response
                            .data.image : '';
                        // Remove existing image input section
                        $('#image_show_section').remove();

                        // Create new image input section dynamically
                        let newImageInput = `
                                <div class="mb-3" id="image_show_section">
                                    <label class="form-label">Item Image</label>
                                    <input type="file" id="item_image" class="form-control dropify" name="image"
                                    data-default-file="${imageUrl}">
                                </div>
                            `;

                        // Append the new input field inside the modal body
                        $('#modal-body').append(newImageInput);

                        // Initialize Dropify on the new input
                        $('#item_image').dropify();
                    }

                    $('#itemModal').modal('show');
                });
            });
        });

        // Status Change Confirm Alert
        function showStatusChangeAlert(id) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to update the status?',
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
            let url = "{{ route('cms.feature.items.status', ':id') }}";
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

        // delete Confirm
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
            let url = "{{ route('cms.feature.items.destroy', ':id') }}";
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
