@extends('layouts.auth')

@section('title', 'Login')
<style>
    body {
        background-color: #002357 !important;
    }

    .login-box {
        width: 360px;
        margin: 7% auto;
    }

    .login-card-body {
        padding: 20px;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    .login-logo img {
        width: 130px;
        margin-bottom: 10px;
    }

    .login-logo {
        text-align: center;
        margin-bottom: 10px;
    }

    .login-box-msg {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
</style>

@section('content')
<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">
            <div class="login-logo">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Multicom">
            </div>

            <h4 class="text-center mb-3">Multicom Group</h4>
            <p class="login-box-msg">Login untuk masuk ke dashboard</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="email" placeholder="Email" required value="{{ old('email') }}">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
