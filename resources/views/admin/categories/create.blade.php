{{-- resources/views/admin/categories/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Kategori - Admin FPCI UNEJ')
@section('page-title', 'Tambah Kategori Baru')

@section('content')
<div class="form-card mx-auto" style="max-width: 600px;">
    <div class="form-card-head">
        <h6><i class="bi bi-plus-circle me-2"></i> Tambah Kategori Baru</h6>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="category_name" class="form-control @error('category_name') is-invalid @enderror" 
                       value="{{ old('category_name') }}" required placeholder="Contoh: Berita Terbaru">
                @error('category_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Parent Kategori</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">-- Tanpa Parent (Kategori Utama) --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_category }}" {{ old('parent_id') == $cat->id_category ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih parent jika ingin membuat sub-kategori</small>
                @error('parent_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Tips:</strong>
                <ul class="mb-0 mt-1">
                    <li>Kategori utama adalah kategori tanpa parent</li>
                    <li>Sub-kategori akan muncul di dropdown parent</li>
                    <li>Kategori yang sudah memiliki postingan tidak bisa dihapus</li>
                </ul>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn-save">
                    <i class="bi bi-save me-1"></i> Simpan Kategori
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection