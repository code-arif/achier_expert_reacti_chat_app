@extends('backend.app', ['title' => 'Splashes'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Splashes</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Splashes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage</li>
                    </ol>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Manage Splash</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.splash.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="title" class="form-label">Title:</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="title" placeholder="Title" 
                                    value="{{ old('title', $splash?->title) }}">
                                    @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="subtitle" class="form-label">Subtitle:</label>
                                    <input type="text" class="form-control @error('subtitle') is-invalid @enderror" name="subtitle" id="subtitle" placeholder="Subtitle" 
                                    value="{{ old('subtitle', $splash?->subtitle) }}">
                                    @error('subtitle')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Save Splash</button>
                                </div>
                            </form>
                        </div> 
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>
@endsection
