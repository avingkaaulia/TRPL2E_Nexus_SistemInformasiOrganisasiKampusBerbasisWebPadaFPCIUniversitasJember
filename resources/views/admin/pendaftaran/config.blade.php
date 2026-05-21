{{-- resources/views/admin/pendaftaran/config.blade.php --}}
@extends('layouts.admin')

@section('title', 'Konfigurasi Pendaftaran - Admin FPCI UNEJ')
@section('page-title', 'Konfigurasi Pendaftaran')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-gear me-2"></i> Pengaturan Pendaftaran</h4>
        <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Catatan:</strong> Pendaftaran akan tampil di halaman publik jika:
        <ul class="mb-0 mt-2">
            <li>✅ Tombol "Buka Pendaftaran" diaktifkan</li>
            <li>✅ Ada periode pendaftaran yang aktif (status "Aktif")</li>
            <li>✅ Tanggal periode pendaftaran masih berlangsung</li>
        </ul>
    </div>
    
    <form action="{{ route('admin.pendaftaran.config.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" name="is_open" value="1" class="form-check-input" id="is_open" 
                       {{ ($config->is_open ?? 0) == 1 ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="is_open">
                    <i class="bi bi-unlock"></i> Buka Pendaftaran
                </label>
            </div>
            <small class="text-muted">✅ Jika diaktifkan, form pendaftaran akan tersedia untuk umum (dengan periode aktif)</small>
            <small class="text-muted d-block">⚠️ Jika dinonaktifkan, semua periode pendaftaran akan otomatis dinonaktifkan</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Teks Selamat Datang</label>
            <textarea name="welcome_text" class="form-control" rows="4">{{ $config->welcome_text ?? '' }}</textarea>
            <small class="text-muted">Teks yang ditampilkan di halaman pendaftaran</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Teks Penutupan</label>
            <textarea name="closing_text" class="form-control" rows="3">{{ $config->closing_text ?? '' }}</textarea>
            <small class="text-muted">Teks yang ditampilkan ketika pendaftaran ditutup</small>
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>

<script>
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const isOpen = document.getElementById('is_open')?.checked;
        if (!isOpen) {
            if (confirm('Menonaktifkan pendaftaran akan otomatis menonaktifkan semua periode. Lanjutkan?')) {
                return true;
            }
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection