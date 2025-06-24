@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>Multicom Group</b></a>
    </div>

    <div class="card">
        <div class="card-body login-card-body">
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
                    <input type="email" class="form-control" name="email" placeholder="Email" required value="{{ old('email') }}">
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
