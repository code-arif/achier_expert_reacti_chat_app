@extends('layouts.guest_layout')

@section('title', 'Login')

@push('styles')
    <style>
        body {
            background: #f5f6fa;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            max-width: 400px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .logo {
            margin: 0 auto 20px auto;
            display: block;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Input focus color */
        .form-control:focus {
            border-color: #6610f2;
            box-shadow: 0 0 0 0.2rem rgba(129, 14, 238, 0.25);
        }

        /* Button primary custom */
        .loginBtn {
            background-color: #6610f2;
            border-color: #6610f2;
            color: #fff;

        }

        .loginBtn:hover,
        .loginBtn:focus,
        .loginBtn:active {
            background-color: #6d0ccc !important;
            border-color: #6d0ccc !important;
            color: #f5f6fa
        }
    </style>
@endpush

@section('content')
    <div class="auth-container">
        <div class="card p-4">
            <div class="card-body">
                <!-- Logo -->
                <img src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="logo"
                    style="height: 30px; width: auto;" />


                <!-- Header -->
                <h2>Login</h2>

                <!-- Form -->
                <form>
                    <div class="mb-3">
                        <label for="email2" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email2" placeholder="Enter Email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Password">
                    </div>
                    {{-- <button type="submit" class="btn  w-100 loginBtn">Login</button> --}}
                    <a href="{{ route('show.dashboard.page') }}" type="submit" class="btn  w-100 loginBtn">Login</a>
                </form>
            </div>
        </div>
    </div>
@endsection
