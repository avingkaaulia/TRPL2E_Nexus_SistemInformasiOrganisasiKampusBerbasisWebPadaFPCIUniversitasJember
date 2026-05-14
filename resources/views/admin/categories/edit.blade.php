{{-- resources/views/admin/categories/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Kategori - Admin FPCI UNEJ')
@section('page-title', 'Edit Kategori')

@section('content')
<div class="form-card mx-auto" style="max-width: 600px;">
    <div class="form-card-head">
        <h6><i class="bi bi-pencil-square me-2"></i> Edit Kategori: {{ $category->category_name }}</h6>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.categories.update', $category->id_category) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="category_name" class="form-control @error('category_name') is-invalid @enderror" 
                       value="{{ old('category_name', $category->category_name) }}" required>
                @error('category_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Parent Kategori</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">-- Tanpa Parent (Kategori Utama) --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_category }}" {{ old('parent_id', $category->parent_id) == $cat->id_category ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih parent jika ingin membuat sub-kategori</small>
                @error('parent_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong>
                <ul class="mb-0 mt-1">
                    <li>Perubahan parent dapat mempengaruhi struktur hirarki</li>
                    <li>Pastikan tidak membuat siklus parent-child</li>
                </ul>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn-save">
                    <i class="bi bi-save me-1"></i> Update Kategori
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection