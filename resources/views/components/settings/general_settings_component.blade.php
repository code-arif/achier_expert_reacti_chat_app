<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">General Settings</div>
            </div>
            <div class="card-body">
                <form id="settingsForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <!-- Site Name & Title -->
                        <div class="col-md-6 mb-3">
                            <label for="name">Site Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter site name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="title">Site Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="Enter site title" required>
                        </div>

                        <!-- Description (full width) -->
                        <div class="col-12 mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Short description"></textarea>
                        </div>

                        <!-- Keywords & Author -->
                        <div class="col-md-6 mb-3">
                            <label for="keywords">Keywords</label>
                            <input type="text" class="form-control" id="keywords" name="keywords"
                                placeholder="SEO keywords (comma separated)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="author">Author</label>
                            <input type="text" class="form-control" id="author" name="author"
                                placeholder="Author name">
                        </div>

                        <!-- Phones -->
                        <div class="col-md-6 mb-3">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Primary phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="additional_phone">Additional Phone</label>
                            <input type="text" class="form-control" id="additional_phone" name="additional_phone"
                                placeholder="Optional phone">
                        </div>

                        <!-- Emails -->
                        <div class="col-md-6 mb-3">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Enter email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="additional_email">Additional Email</label>
                            <input type="email" class="form-control" id="additional_email" name="additional_email"
                                placeholder="Optional email">
                        </div>

                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter address"></textarea>
                        </div>

                        <!-- Copyright -->
                        <div class="col-12 mb-3">
                            <label for="copyright">Copyright</label>
                            <input type="text" class="form-control" id="copyright" name="copyright"
                                placeholder="© 2025 Your Company. All rights reserved.">
                        </div>

                        <!-- Logo & Favicon -->
                        <div class="col-md-6 mb-3">
                            <label for="logo">Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="favicon">Favicon</label>
                            <input type="file" class="form-control" id="favicon" name="favicon">
                        </div>

                        <!-- Signature -->
                        <div class="col-12 mb-3">
                            <label for="signature">Signature</label>
                            <textarea class="form-control" id="signature" name="signature" rows="2"
                                placeholder="System signature or footer text"></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




{{-- intigrate dropity dopify --}}
<script>
    $(document).ready(function() {
        // Logo & Favicon file input with Dropify
        $('#logo').dropify({
            messages: {
                'default': 'Drag and drop a logo or click',
                'replace': 'Drag and drop or click to replace',
                'remove': 'Remove',
                'error': 'Oops, something went wrong.'
            }
        });

        $('#favicon').dropify({
            messages: {
                'default': 'Drag and drop a favicon or click',
                'replace': 'Drag and drop or click to replace',
                'remove': 'Remove',
                'error': 'Oops, something went wrong.'
            }
        });
    });
</script>
