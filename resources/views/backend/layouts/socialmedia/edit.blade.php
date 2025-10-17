@extends('backend.app', ['title' => 'Edit Social Media'])

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
                    <h1 class="page-title">Edit Social Media</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Social Media</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">
                <div class="col-12 col-sm-12">
                    <div class="card product-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Edit Social Media</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.social_media.update', $socialMedia->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="profile_link">Profile Link</label>
                                    <input type="url" class="form-control" id="profile_link" name="profile_link" value="{{ $socialMedia->profile_link }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="social_media_icon">Social Media Icon</label>
                                    <input type="file" class="form-control" id="social_media_icon" name="social_media_icon">
                                    @if($socialMedia->social_media_icon)
                                        <img src="{{ asset($socialMedia->social_media_icon) }}" alt="icon" width="50px" height="50px" style="margin-top: 10px;">
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </form>
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

