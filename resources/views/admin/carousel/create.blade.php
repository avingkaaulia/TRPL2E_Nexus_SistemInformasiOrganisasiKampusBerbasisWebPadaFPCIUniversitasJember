{{-- resources/views/admin/carousel/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Slide - Admin FPCI UNEJ')
@section('page-title', 'Tambah Slide - ' . $category->category_name)

@section('content')
<div class="admin-card">
    <!-- Tampilkan error jika ada -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> Ada kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('admin.carousel.store', $category->id_category) }}" 
          method="POST" enctype="multipart/form-data" id="carouselForm">
        @csrf
        
        <div class="mb-3">
            <label class="form-label">Judul Slide <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                   value="{{ old('title') }}" required maxlength="150">
            <small class="text-muted">Maksimal 150 karakter</small>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                      rows="4" required minlength="10" maxlength="500">{{ old('description') }}</textarea>
            <div class="d-flex justify-content-between">
                <small class="text-muted">Minimal 10, maksimal 500 karakter</small>
                <small class="text-muted"><span id="charCount">0</span> / 500 karakter</small>
            </div>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar <span class="text-danger">*</span></label>
            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" 
                   accept="image/*" required>
            <small class="text-muted">Format: JPG, PNG. Maksimal 4MB</small>
            @error('featured_image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div id="imagePreview" class="mt-2" style="display: none;">
                <img id="previewImg" src="#" width="150" class="img-thumbnail">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="publish" {{ old('status') == 'publish' ? 'selected' : '' }}>Publish</option>
                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <a href="{{ route('admin.carousel') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success" id="submitBtn">Simpan</button>
        </div>
    </form>
</div>

<script>
// Hitung karakter deskripsi
const textarea = document.querySelector('textarea[name="description"]');
const charCount = document.getElementById('charCount');

if (textarea) {
    textarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        if (this.value.length > 500) {
            this.value = this.value.substring(0, 500);
            charCount.textContent = 500;
            alert('⚠️ Deskripsi maksimal 500 karakter!');
        }
    });
    // Initial count
    charCount.textContent = textarea.value.length;
}

// Preview gambar
const fileInput = document.querySelector('input[name="featured_image"]');
const imagePreview = document.getElementById('imagePreview');
const previewImg = document.getElementById('previewImg');

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
            
            // Cek ukuran file
            if (file.size > 4 * 1024 * 1024) {
                alert('⚠️ Ukuran file melebihi 4MB!');
                this.value = '';
                imagePreview.style.display = 'none';
            }
            
            // Cek tipe file
            if (!file.type.match('image.*')) {
                alert('⚠️ File harus berupa gambar!');
                this.value = '';
                imagePreview.style.display = 'none';
            }
        }
    });
}

// Cegah double submit
const form = document.getElementById('carouselForm');
const submitBtn = document.getElementById('submitBtn');

if (form) {
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    });
}
</script>
@endsection