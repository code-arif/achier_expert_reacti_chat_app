@extends('backend.app', ['title' => 'Edit Specialize'])

@section('title', 'Dashboard || Edit Specialize')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Edit Specialize</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.specialize.index') }}">Specializes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="tab-content">
                        <div class="tab-pane active show" id="editProfile">
                            <div class="card">
                                <div class="card-body border-0">
                                    <form class="form-horizontal" method="post" action="{{ route('admin.specialize.update', $specialize->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')


                                        <div class="mb-4">
                                            <div class="form-group">
                                                <label for="type" class="form-label">Type:</label>
                                                <select name="type" class="form-select @error('type') is-invalid @enderror">
                                                    
                                                    <option value="company" {{ $specialize->type == 'company' ? 'selected' : '' }}>Company</option>
                                                    <option value="employee" {{ $specialize->type == 'employee' ? 'selected' : '' }}>Employee</option>
                                                </select>
                                                @error('type')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-3">
                                                <label for="name" class="form-label">Name:</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Enter name" value="{{ old('name', $specialize->name) }}">
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-3">
                                                <label for="image" class="form-label">Image:</label>
                                                <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                                                @error('image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror

                                                @if ($specialize->image_url)
                                                    <div class="mt-2">
                                                        <img src="{{ asset($specialize->image_url) }}" alt="Current Image" style="width: 120px; height: auto;">
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="form-group mt-4">
                                                <button class="btn btn-primary" type="submit">Update</button>
                                                <a href="{{ route('admin.specialize.index') }}" class="btn btn-danger">Cancel</a>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div> <!-- tab-pane -->
                    </div> <!-- tab-content -->
                </div> <!-- col -->
            </div> <!-- row -->

        </div> <!-- main-container -->
    </div> <!-- side-app -->
</div> <!-- app-content -->
@endsection

@push('scripts')
<!-- You can add page-specific scripts here if needed -->
@endpush
