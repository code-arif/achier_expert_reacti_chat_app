@extends('backend.app', ['title' => 'Update Dynamic Page'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Dynamic Pages</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dynamic Pages</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">

                    <div class="tab-content">
                        <div class="tab-pane active show" id="editProfile">
                            <div class="card">
                                <div class="card-body border-0">
                                    <form class="form-horizontal" method="POST" action="{{ route('admin.dynamic_page.update', $data->id) }}">
                                        @csrf
                                        @method('PUT') <!-- Ensure this directive is included -->
                                        <div class="row mb-4">

                                            <div class="form-group">
                                                <label for="page_title" class="form-label">Page Title:</label>
                                                <input type="text" class="form-control @error('page_title') is-invalid @enderror" name="page_title" placeholder="Page Title" id="" value="{{ old('page_title', $data->page_title) }}">
                                                @error('page_title')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="page_content" class="form-label">Page Content:</label>
                                                <textarea placeholder="Type here..." id="page_content" name="page_content"
                                                 class="form-control @error('page_content') is-invalid @enderror">
                                                 {{ old('page_content', $data->page_content) }}
                                             </textarea>
                                                @error('page_content')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit">Submit</button>
                                                <a href="{{ route('admin.dynamic_page.index') }}" class="btn btn-danger">Cancel</a>
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

<script>
    ClassicEditor
        .create(document.querySelector('#page_content'))
        .catch(error => {
            console.error(error);
        });
</script>
@endpush