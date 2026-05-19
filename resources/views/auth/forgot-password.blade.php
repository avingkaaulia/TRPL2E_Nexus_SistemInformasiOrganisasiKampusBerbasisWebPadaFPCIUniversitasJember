{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Lupa Password - FPCI UNEJ')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('assets/img/logo.png') }}" width="80" alt="Logo">
            <h2>Lupa Password</h2>
            <p>Masukkan email Anda untuk mereset password</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope"></i> Email
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-control" 
                       value="{{ old('email') }}" 
                       required autofocus>
            </div>
            
            <button type="submit" class="btn-login-submit">
                <i class="bi bi-send"></i> Kirim Link Reset
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="{{ route('login') }}">← Kembali ke Login</a></p>
        </div>
    </div>
</div>
@endsection