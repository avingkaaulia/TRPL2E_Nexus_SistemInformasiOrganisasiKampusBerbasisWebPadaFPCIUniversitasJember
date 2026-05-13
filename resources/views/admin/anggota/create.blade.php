{{-- resources/views/admin/anggota/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Anggota - Admin FPCI UNEJ')
@section('page-title', 'Tambah Anggota Baru')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-person-plus me-2"></i> Form Tambah Anggota</h4>
        <a href="{{ route('admin.anggota.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    <form action="{{ route('admin.anggota.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    <small class="text-muted">Minimal 6 karakter</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Divisi <span class="text-danger">*</span></label>
                    <select name="id_divisi" class="form-select @error('id_divisi') is-invalid @enderror" required>
                        <option value="">Pilih Divisi</option>
                        @foreach($divisiList as $divisi)
                        <option value="{{ $divisi->id_divisi }}" {{ old('id_divisi') == $divisi->id_divisi ? 'selected' : '' }}>
                            {{ $divisi->nama_divisi }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_divisi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Jabatan <span class="text-danger">*</span></label>
                    <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan') }}" required>
                    @error('jabatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label>Periode <span class="text-danger">*</span></label>
                    <select name="periode" class="form-select @error('periode') is-invalid @enderror" required>
                        <option value="">Pilih Periode</option>
                        @foreach($periodeList as $periode)
                        <option value="{{ $periode }}" {{ old('periode') == $periode ? 'selected' : '' }}>
                            {{ $periode }}
                        </option>
                        @endforeach
                    </select>
                    @error('periode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label>No Urut <span class="text-danger">*</span></label>
                    <input type="number" name="no_urut" class="form-control @error('no_urut') is-invalid @enderror" value="{{ old('no_urut') }}" required>
                    @error('no_urut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label>Link Instagram/LinkedIn</label>
            <input type="text" name="link" class="form-control" placeholder="https://instagram.com/..." value="{{ old('link') }}">
        </div>
        
        <div class="mb-3">
            <label>Foto Profil</label>
            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
            <div id="imagePreview" class="mt-2" style="display: none;">
                <img id="previewImg" src="#" width="100" class="img-thumbnail">
            </div>
            @error('foto')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i> Simpan Anggota
            </button>
            <a href="{{ route('admin.anggota.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
document.querySelector('input[name="foto"]')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
        
        // Validasi ukuran file
        if (file.size > 4 * 4024 * 4024) {
            alert('Ukuran file terlalu besar! Maksimal 4MB');
            this.value = '';
            document.getElementById('imagePreview').style.display = 'none';
        }
    }
});
</script>
@endsection