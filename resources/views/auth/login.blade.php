@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-tools"></i> MOTOKU</h2>
            <p class="text-muted small mb-0">Inventory Sparepart Motor</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">Masuk ke Sistem</h5>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>

                <hr>
                <small class="text-muted d-block text-center">
                    Default akun: <code>admin@motoku.test</code> / <code>password</code>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
