{{-- resources/views/admin/carousel/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Slide - Admin FPCI UNEJ')
@section('page-title', 'Edit Slide - ' . $category->category_name)

@section('content')
<div class="admin-card">
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

    <form action="{{ route('admin.carousel.update', $carousel->id_post) }}" 
          method="POST" enctype="multipart/form-data" id="carouselForm">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label class="form-label">Judul Slide <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                   value="{{ old('title', $carousel->title) }}" required maxlength="150">
            <small class="text-muted">Maksimal 150 karakter</small>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                      rows="4" required minlength="10" maxlength="500">{{ old('description', $carousel->content) }}</textarea>
            <div class="d-flex justify-content-between">
                <small class="text-muted">Minimal 10, maksimal 500 karakter</small>
                <small class="text-muted"><span id="charCount">0</span> / 500 karakter</small>
            </div>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar Saat Ini</label>
            <div>
                @php
                    $imageFound = false;
                    $imageUrl = '';
                    
                    if($carousel->featured_image_path) {
                        $storagePath = storage_path('app/public/' . $carousel->featured_image_path);
                        if(file_exists($storagePath)) {
                            $imageFound = true;
                            $imageUrl = asset('storage/' . $carousel->featured_image_path);
                        }
                    }
                    
                    if(!$imageFound && $carousel->featured_image_path) {
                        $publicPath = public_path($carousel->featured_image_path);
                        if(file_exists($publicPath)) {
                            $imageFound = true;
                            $imageUrl = asset($carousel->featured_image_path);
                        }
                    }
                @endphp
                
                @if($imageFound)
                    <img src="{{ $imageUrl }}" width="200" class="img-thumbnail">
                @else
                    <div style="width:200px; height:120px; background:#5C6844; border-radius:8px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white;">
                        <i class="bi bi-image" style="font-size:48px;"></i>
                        <span style="margin-top:10px;">Gambar tidak ditemukan</span>
                        <span style="font-size:11px;">Silahkan upload gambar baru</span>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Ganti Gambar (Opsional)</label>
            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/*">
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
                <option value="publish" {{ old('status', $carousel->status) == 'publish' ? 'selected' : '' }}>Publish</option>
                <option value="draft" {{ old('status', $carousel->status) == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <a href="{{ route('admin.carousel') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="submitBtn">Update</button>
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
            
            if (file.size > 4 * 1024 * 1024) {
                alert('⚠️ Ukuran file melebihi 4MB!');
                this.value = '';
                imagePreview.style.display = 'none';
            }
            
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
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
    });
}
</script>
@endsection