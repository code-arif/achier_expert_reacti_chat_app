@extends('backend.app')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            {{-- PAGE-HEADER --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">Google Client Settings <i class="fa-solid fa-triangle-exclamation text-danger" title="Warning"></i></h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Google</li>
                    </ol>
                </div>
            </div>
            {{-- PAGE-HEADER --}}


            <div class="row">
                <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                    <h2 class="card-header">Google Settings</h2>
                    <div class="card box-shadow-0">
                        <div class="card-body">
                            <form method="post" action="{{ route('setting.social.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')

                                <div class="row mb-4">
                                    <label for="google_client_id" class="col-md-3 form-label">Google Client ID</label>
                                    <div class="col-md-9">
                                        <input class="form-control @error('google_client_id') is-invalid @enderror" id="google_client_id"
                                            name="google_client_id" placeholder="Enter your google client ID" type="text"
                                            value="{{ config('services.google.client_id') ?? old('google_client_id') }}">
                                        @error('google_client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="google_client_secret" class="col-md-3 form-label">Google Client Secret</label>
                                    <div class="col-md-9">
                                        <input class="form-control @error('google_client_secret') is-invalid @enderror" id="google_client_secret"
                                            name="google_client_secret" placeholder="Enter your google client secret" type="text"
                                            value="{{ config('services.google.client_secret') ?? old('google_client_secret') }}">
                                        @error('google_client_secret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="google_redirect_url" class="col-md-3 form-label">Google Redirect Url</label>
                                    <div class="col-md-9">
                                        <input class="form-control @error('google_redirect_url') is-invalid @enderror" id="google_redirect_url"
                                            name="google_redirect_url" placeholder="Enter your google redirect url" type="text"
                                            value="{{ config('services.google.redirect') ?? old('google_redirect_url') }}">
                                        @error('google_redirect_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row justify-content-end">
                                    <div class="col-sm-9">
                                        <div>
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
@endpush