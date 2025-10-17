@extends('backend.app', ['title' => 'Venue List'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">User Venue Lists</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">User</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Venue</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    {{-- venue table section --}}
                    <div class="col-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="card-header border-bottom mb-3">
                                    <div class="card-options ms-auto">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#venueModal" id="addVenueBtn">Add Venue</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Capacity</th>
                                                <th>Location</th>
                                                <th>Service Start</th>
                                                <th>Service End</th>
                                                <th>Ticket Price</th>
                                                <th>Phone</th>
                                                <th>Email</th>
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

    {{-- Add/Edit Venue Modal --}}
    <div class="modal fade" id="venueModal" tabindex="-1" aria-labelledby="venueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="venueForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="venueID">

                    <div class="modal-header">
                        <h5 class="modal-title" id="venueModalLabel">Create Venue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            {{-- Title --}}
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="venue_title"
                                    placeholder="Venue Title">
                                <span class="text-danger error-text title_error"></span>
                            </div>

                            {{-- Capacity --}}
                            <div class="col-md-6">
                                <label class="form-label">Seat Capacity</label>
                                <input type="number" class="form-control" name="capacity" id="venue_capacity"
                                    placeholder="Capacity">
                                <span class="text-danger error-text capacity_error"></span>
                            </div>

                            {{-- Map Picker --}}
                            <div class="col-md-12 mt-3">
                                <label class="form-label">Pick Location on Map</label>
                                <div id="map" style="height: 250px; width: 100%;"></div>
                            </div>

                            {{-- Location --}}
                            <div class="col-md-6 mt-3">
                                <label class="form-label">Location Address</label>
                                <input type="text" class="form-control" name="location" id="venue_location"
                                    placeholder="Venue Location" readonly>
                                <span class="text-danger error-text location_error"></span>
                            </div>

                            {{-- Latitude --}}
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" class="form-control" name="latitude" id="venue_latitude"
                                    placeholder="Latitude" readonly>
                                <span class="text-danger error-text latitude_error"></span>
                            </div>

                            {{-- Longitude --}}
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" class="form-control" name="longitude" id="venue_longitude"
                                    placeholder="Longitude" readonly>
                                <span class="text-danger error-text longitude_error"></span>
                            </div>

                            {{-- Service Start Time --}}
                            <div class="col-md-4">
                                <label class="form-label">Service Start Time</label>
                                <input type="time" class="form-control" name="service_start_time" id="venue_start_time">
                                <span class="text-danger error-text service_start_time_error"></span>
                            </div>

                            {{-- Service End Time --}}
                            <div class="col-md-4">
                                <label class="form-label">Service End Time</label>
                                <input type="time" class="form-control" name="service_end_time" id="venue_end_time">
                                <span class="text-danger error-text service_end_time_error"></span>
                            </div>

                            {{-- Ticket Price --}}
                            <div class="col-md-4">
                                <label class="form-label">Ticket Price</label>
                                <input type="number" class="form-control" name="ticket_price" id="venue_ticket_price"
                                    placeholder="Ticket Price">
                                <span class="text-danger error-text ticket_price_error"></span>
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" id="venue_phone"
                                    placeholder="Phone Number">
                                <span class="text-danger error-text phone_error"></span>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="venue_email"
                                    placeholder="Email Address">
                                <span class="text-danger error-text email_error"></span>
                            </div>

                            {{-- Description --}}
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="venue_description" rows="3"
                                    placeholder="Venue Description"></textarea>
                                <span class="text-danger error-text description_error"></span>
                            </div>

                            {{-- Features --}}
                            <div class="col-md-12">
                                <label class="form-label">Features</label>
                                <textarea class="form-control" name="features" id="venue_features" rows="2"
                                    placeholder="Comma separated features"></textarea>
                                <span class="text-danger error-text features_error"></span>
                            </div>

                            {{-- Images --}}
                            <div class="col-md-12">
                                <label class="form-label">Images</label>
                                <input type="file" name="images[]" id="venue_images" class="form-control dropify"
                                    multiple accept="image/*">
                                <span class="text-danger error-text images_error"></span>
                            </div>

                            {{-- Videos --}}
                            <div class="col-md-12 mt-2">
                                <label class="form-label">Videos</label>
                                <input type="file" name="videos[]" id="venue_videos" class="form-control dropify"
                                    multiple accept="video/*">
                                <span class="text-danger error-text videos_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="venueSubmitBtn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        #map {
            height: 300px;
        }

        .leaflet-control-geocoder-form input {
            width: 200px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        // Global variables
        let map, marker, geocoder;

        // Function to initialize the map
        function initializeMap() {
            // Default to Dhaka coordinates
            const defaultLocation = [23.8103, 90.4125];

            // Initialize map
            map = L.map('map').setView(defaultLocation, 13);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add marker
            marker = L.marker(defaultLocation, {
                draggable: true
            }).addTo(map);

            // Initialize geocoder
            geocoder = L.Control.Geocoder.nominatim();

            // Add search control
            L.Control.geocoder({
                defaultMarkGeocode: false,
                geocoder: geocoder,
                position: 'topright',
                placeholder: 'Search location...',
                errorMessage: 'Location not found.'
            }).on('markgeocode', function(e) {
                const {
                    center,
                    name
                } = e.geocode;
                updateLocation(center.lat, center.lng, name);
            }).addTo(map);

            // Handle marker drag
            marker.on('dragend', function() {
                const position = marker.getLatLng();
                reverseGeocode(position.lat, position.lng);
            });

            // Handle click on map
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            // Handle search box
            $('#search-button').click(function() {
                const query = $('#search-box').val();
                if (query) {
                    geocoder.geocode(query, function(results) {
                        if (results && results.length > 0) {
                            const {
                                center,
                                name
                            } = results[0];
                            updateLocation(center.lat, center.lng, name);
                        } else {
                            toastr.error('Location not found');
                        }
                    });
                }
            });

            // Also trigger search on Enter key
            $('#search-box').keypress(function(e) {
                if (e.which === 13) {
                    $('#search-button').click();
                }
            });
        }

        // Update location fields
        function updateLocation(lat, lng, address) {
            $('#venue_latitude').val(lat);
            $('#venue_longitude').val(lng);
            $('#venue_location').val(address || '');

            // Move marker and center map
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 15);
        }

        // Reverse geocode coordinates to get address
        function reverseGeocode(lat, lng) {
            geocoder.reverse({
                    lat: lat,
                    lng: lng
                },
                map.getZoom(),
                function(results) {
                    if (results && results.length > 0) {
                        updateLocation(lat, lng, results[0].name);
                    } else {
                        updateLocation(lat, lng, '');
                    }
                }
            );
        }

        // When modal opens
        $('#venueModal').on('shown.bs.modal', function() {
            // Initialize map if not already done
            if (!map) {
                initializeMap();
            } else {
                // Reset map view if already initialized
                setTimeout(function() {
                    map.invalidateSize();
                    if (marker) {
                        map.setView(marker.getLatLng(), map.getZoom());
                    }
                }, 300);
            }
        });


        //document ready functionq
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

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
                ajax: {
                    url: "{{ route('admin.venue.index') }}",
                    type: "GET",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'capacity'
                    },
                    {
                        data: 'location'
                    },
                    {
                        data: 'service_start_time'
                    },
                    {
                        data: 'service_end_time'
                    },
                    {
                        data: 'ticket_price'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });


            // Open modal for new venue
            $('#addVenueBtn').click(function() {
                $('#venueModalLabel').text('Create Venue');
                $('#venueForm')[0].reset();
                $('#venueSubmitBtn').prop('disabled', false).html('Save changes');
                $('#venue_images').dropify().clearElement();
                $('#venue_videos').dropify().clearElement();
                $('#venueID').val('');
                $('.error-text').text('');
                $('#venueModal').modal('show');
            });

            // Handle form submission
            $('#venueForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#venueID').val();
                let url = id ?
                    "{{ route('admin.venue.update', ':id') }}".replace(':id', id) :
                    "{{ route('admin.venue.store') }}";

                if (id) {
                    formData.append('_method', 'POST');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('span.error-text').text('');
                        $('#venueSubmitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (response.status == 0) {
                            $.each(response.errors, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });

                        } else {
                            $('#venueModal').modal('hide');
                            $('#venueForm')[0].reset();

                            let imageInput = $('#venue_images').data('dropify');
                            if (imageInput) {
                                imageInput.resetPreview();
                                imageInput.clearElement();
                            }

                            let videoInput = $('#venue_videos').data('dropify');
                            if (videoInput) {
                                videoInput.resetPreview();
                                videoInput.clearElement();
                            }

                            toastr.success(response.message);
                            $('#datatable').DataTable().ajax.reload();
                        }
                        $('#venueSubmitBtn').prop('disabled', false).html('Save changes');
                    },


                    error: function(xhr) {
                        $('#venueSubmitBtn').prop('disabled', false).html('Save changes');
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(prefix, val) {
                                prefix = prefix.replace(/\./g, '_');
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            toastr.error(xhr.responseJSON.message ||
                                'Something went wrong. Please try again.');
                        }
                    }
                });
            });


            // Edit Venue - Load existing data
            $(document).on('click', '.editVenue', function() {
                var id = $(this).data('id');
                var url = "{{ route('admin.venue.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    $('#venueModalLabel').text('Edit Venue');
                    $('#venueID').val(response.data.id);

                    // Fill form fields
                    $('#venue_title').val(response.data.title);
                    $('#venue_capacity').val(response.data.capacity);
                    $('#venue_location').val(response.data.location);
                    $('#venue_latitude').val(response.data.latitude);
                    $('#venue_longitude').val(response.data.longitude);
                    $('#venue_start_time').val(response.data.service_start_time);
                    $('#venue_end_time').val(response.data.service_end_time);
                    $('#venue_ticket_price').val(response.data.ticket_price);
                    $('#venue_phone').val(response.data.phone);
                    $('#venue_email').val(response.data.email);
                    $('#venue_description').val(response.data.detail.description);
                    $('#venue_features').val(response.data.detail.features);

                    let baseUrl = "{{ asset('') }}";

                    // Reset Dropify for images
                    let imageInput = $('#venue_images').dropify();
                    imageInput = imageInput.data('dropify');
                    imageInput.resetPreview();
                    imageInput.clearElement();

                    // collect all image urls
                    let imageFiles = response.data.media
                        .filter(m => m.image_url !== null)
                        .map(m => baseUrl + m.image_url);

                    // শুধু প্রথম image preview দেখাবো
                    if (imageFiles.length > 0) {
                        imageInput.settings.defaultFile = imageFiles[0];
                        imageInput.destroy();
                        imageInput.init();
                    }

                    // Reset Dropify for videos
                    let videoInput = $('#venue_videos').dropify();
                    videoInput = videoInput.data('dropify');
                    videoInput.resetPreview();
                    videoInput.clearElement();

                    // collect all video urls
                    let videoFiles = response.data.media
                        .filter(m => m.video_url !== null)
                        .map(m => baseUrl + m.video_url);

                    if (videoFiles.length > 0) {
                        videoInput.settings.defaultFile = videoFiles[0];
                        videoInput.destroy();
                        videoInput.init();
                    }


                    // Set map position
                    if (response.data.latitude && response.data.longitude) {
                        const lat = parseFloat(response.data.latitude);
                        const lng = parseFloat(response.data.longitude);

                        // Update after modal is shown
                        $('#venueModal').modal('show').on('shown.bs.modal', function() {
                            updateLocation(lat, lng, response.data.location);
                        });
                    } else {
                        $('#venueModal').modal('show');
                    }
                });
            });
        });


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
            let url = "{{ route('admin.venue.status', ':id') }}";
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
                title: 'Are you sure you want to delete this venue?',
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
            let url = "{{ route('admin.venue.destroy', ':id') }}";
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
