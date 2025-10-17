@extends('backend.app', ['title' => 'Hero section'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home page - Hero section</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Hero section</li>
                        </ol>
                    </div>
                </div>
                {{-- PAGE-HEADER --}}


                <div class="row">
                    <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <form class="form-horizontal" method="post" action="{{ route('cms.home.hero.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        {{-- section title --}}
                                        <div class="form-group">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter title" id="title"
                                                value="{{ $data->title ?? (old('title') ?? '') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- section description --}}
                                        <div class="form-group">
                                            <label for="description" class="form-label">Description:</label>
                                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                                rows="5">{{ old('description', $data->description ?? '') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- status field --}}
                                        {{-- <div class="form-group">
                                            <label for="status" class="form-label">Status</label>
                                            <select name="status" id="status"
                                                class="form-control @error('status') is-invalid @enderror">
                                                <option value="active"
                                                    {{ old('status', $data->status ?? '') == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="inactive"
                                                    {{ old('status', $data->status ?? '') == 'inactive' ? 'selected' : '' }}>
                                                    Inactive</option>
                                            </select>
                                            @error('status')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div> --}}


                                        {{-- hero image --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="image" class="form-label">Image</label>
                                                    <input type="file"
                                                        class="dropify form-control @error('image') is-invalid @enderror"
                                                        data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                                                        name="image" id="image">
                                                    @error('image')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Save change</button>
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
