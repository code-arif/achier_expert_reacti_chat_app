@extends('backend.app', ['title' => 'Create FAQ'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">FAQs</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">FAQs</a></li>
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
                                    <form class="form-horizontal" method="post" action="{{ route('admin.faq.store') }}">
                                        @csrf
                                        <div class="row mb-4">

                                            <div class="form-group">
                                                <label for="question" class="form-label">Question:</label>
                                                <input type="text" class="form-control @error('question') is-invalid @enderror" name="question" placeholder="Question" id="" value="{{ old('question') }}">
                                                @error('question')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="answer" class="form-label">Answer:</label>
                                                <textarea class="form-control @error('answer') is-invalid @enderror" name="answer" placeholder="Answer">{{ old('answer') }}</textarea>
                                                @error('answer')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- <div class="form-group">
                                                <label for="faq_type" class="form-label">Type:</label>
                                                <select class="form-control @error('faq_type') is-invalid @enderror" name="faq_type">
                                                    <option value="">Select Type</option>
                                                    <option value="buyer" {{ old('faq_type') == 'buyer' ? 'selected' : '' }}>For Buyer</option>
                                                    <option value="seller" {{ old('faq_type') == 'seller' ? 'selected' : '' }}>For Seller</option>
                                                </select>
                                                @error('faq_type')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}

                                            <div class="form-group">
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
