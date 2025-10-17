@extends('backend.app', ['title' => 'Create Specialize'])

@section('title', 'Dashboard || Create Specialize')
@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Specializes</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Specializes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">

                    <div class="tab-content">
                        <div class="tab-pane active show" id="editProfile">
                            <div class="card">
                                <div class="card-body border-0">
                                    <form class="form-horizontal" method="post" action="{{ route('admin.specialize.store') }}" enctype="multipart/form-data"> >
                                        @csrf
                                        <div class="row mb-4">

                                            <div class="form-group">
                                                <label for="name" class="form-label">Type:</label>

                                                <select name="type" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="company">Company</option>
                                                    <option value="employee">Employee</option>

                                                </select>
                                            </div>




                                            <div class="form-group">
                                                <label for="name" class="form-label">Name:</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="name" id="" value="{{ old('name') }}">
                                                @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="mt-3">
                                                <label for="image" class="form-label">Image</label>
                                                <input type="file" name="image" id="image"
                                                    class="form-control @error('image') is-invalid @enderror"
                                                    value="{{ old('image') }}">
                                                @error('image')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>



                                            <div class="form-group mt-4">
                                                <button class="btn btn-primary" type="submit">Submit</button>
                                                <a href="{{ route('admin.faq.index') }}" class="btn btn-danger">Cancel</a>
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
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
@push('scripts')

@endpush
