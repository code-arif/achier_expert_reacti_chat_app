<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Admin Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!-- First Row (Profile Image + Info) -->
                <div class="row mb-4">
                    <div class="col-md-4 d-flex justify-content-center align-items-center">
                        <div class="position-relative" style="width:170px; height:170px;">
                            <!-- Profile Image -->
                            <img src="{{ asset('assets/default/default_person.jpg') }}" alt="Profile"
                                class="rounded-circle img-fluid shadow w-100 h-100"
                                style="object-fit:cover; border: 2px solid #810eee;">

                            <!-- Hidden File Input -->
                            <input type="file" id="avatarInput" class="d-none">

                            <!-- Camera Overlay Button -->
                            <label for="avatarInput"
                                class="camera-overlay position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle p-3 d-flex align-items-center justify-content-center"
                                style="cursor:pointer; opacity:0; transition:0.3s;">
                                <i class="fas fa-camera text-white fs-4"></i>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-8 d-flex flex-column justify-content-center">
                        <h6>Email: user@example.com</h6>
                        <h6>Full Name: John Doe</h6>
                        <h6>Role: Admin</h6>
                        <button class="btn btn-outline-primary btn-sm mt-2">Change Avatar</button>
                    </div>
                </div>

                <!-- Second Row (Tabs) -->
                <ul class="nav nav-tabs mb-3" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                            type="button" role="tab">
                            Profile Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password"
                            type="button" role="tab">
                            Change Password
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabContent">

                    <!-- Profile Form -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName"
                                        placeholder="Enter first name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName"
                                        placeholder="Enter last name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="Enter email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" placeholder="Enter phone">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" rows="2" placeholder="Enter address"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                        </form>
                    </div>

                    <!-- Password Form -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <form>
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword"
                                    placeholder="Enter current password">
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword"
                                    placeholder="Enter new password">
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword"
                                    placeholder="Confirm new password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Change Password</button>
                        </form>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

@push('styles')
    <style>
        /* Hover করলে camera icon show হবে */
        .position-relative:hover .camera-overlay {
            opacity: 1 !important;
        }
    </style>
@endpush
