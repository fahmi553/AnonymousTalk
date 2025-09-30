@extends('layouts.auth')

@section('content')
<div class="card shadow-sm w-100" style="max-width: 400px;">
    <div class="card-body">
        <h3 class="card-title text-center mb-4">Login</h3>
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3 text-end">
                <a href="{{ route('password.request') }}">Forgot your password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('register') }}">Don't have an account? Register here</a>
        </div>
    </div>
</div>
@endsection
