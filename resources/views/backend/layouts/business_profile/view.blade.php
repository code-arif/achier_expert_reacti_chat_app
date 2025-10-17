@extends('backend.app', ['title' => 'Business Profile Details'])

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Business Profile Details</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Business Profiles</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">
                <div class="col-12 col-sm-12">
                    <div class="card product-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ $profile->venue_name }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>User:</strong> {{ $profile->user->f_name }} {{ $profile->user->l_name }}</p>
                                    <p><strong>Email:</strong> {{ $profile->user->email }}</p>
                                    <p><strong>Establishment:</strong> {{ $profile->establishment->name }}</p>
                                    <p><strong>Status:</strong> {{ $profile->status }}</p>
                                    <p><strong>Open Hour:</strong> {{ $profile->open_hour }}</p>
                                    <p><strong>Close Hour:</strong> {{ $profile->close_hour }}</p>
                                    <p><strong>Menu:</strong> {{ $profile->menu }}</p>
                                    <p><strong>Is Premium:</strong> {{ $profile->is_premium ? 'Yes' : 'No' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <img src="{{ $profile->cover }}" alt="Cover Image" class="img-fluid">
                                </div>
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