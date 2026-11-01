{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login - FPCI UNEJ')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            @php $logo = App\Models\Setting::get('site_logo', 'assets/img/logo.png'); @endphp
            <img src="{{ asset($logo) }}" width="80" alt="Logo">
            <h2>Login</h2>
            <p>Masuk ke akun FPCI UNEJ Anda</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success" role="status">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope"></i> Email
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email') }}" 
                       required autofocus autocomplete="email" inputmode="email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock"></i> Password
                </label>
                <div class="password-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           required autocomplete="current-password">
                    <i class="bi bi-eye-slash toggle-password" role="button" tabindex="0" aria-label="Tampilkan password" onclick="togglePassword()" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();togglePassword();}"></i>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
            </div>
            
            <button type="submit" class="btn-login-submit">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const password = document.getElementById('password');
    const toggle = document.querySelector('.toggle-password');
    if (password.type === 'password') {
        password.type = 'text';
        toggle.classList.remove('bi-eye-slash');
        toggle.classList.add('bi-eye');
        toggle.setAttribute('aria-label', 'Sembunyikan password');
    } else {
        password.type = 'password';
        toggle.classList.remove('bi-eye');
        toggle.classList.add('bi-eye-slash');
        toggle.setAttribute('aria-label', 'Tampilkan password');
    }
}

// cegah tombol diklik dua kali saat form sedang dikirim
document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
        const tombol = form.querySelector('button[type="submit"]');
        if (!tombol) return;
        tombol.disabled = true;
        tombol.dataset.teksAsli = tombol.innerHTML;
        tombol.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
    });
});
// kalau pengguna menekan tombol kembali di browser, tombol dikembalikan seperti semula
window.addEventListener('pageshow', function () {
    document.querySelectorAll('button[type="submit"][data-teks-asli]').forEach(function (t) {
        t.disabled = false;
        t.innerHTML = t.dataset.teksAsli;
    });
});
</script>
@endpush
@endsection