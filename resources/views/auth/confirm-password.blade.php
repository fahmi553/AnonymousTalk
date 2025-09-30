@extends('layouts.auth')

@section('content')
<div class="card shadow-sm w-100" style="max-width: 400px;">
    <div class="card-body">
        <h3 class="card-title text-center mb-4">Confirm Password</h3>
        <p class="text-muted text-center">Please confirm your password before continuing.</p>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100">Confirm</button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </div>
</div>
@endsection
