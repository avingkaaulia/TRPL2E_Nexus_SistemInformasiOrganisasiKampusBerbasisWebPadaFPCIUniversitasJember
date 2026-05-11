{{-- resources/views/admin/posts/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Postingan - Admin FPCI UNEJ')
@section('page-title', 'Tambah Postingan Baru')

@section('content')
<div class="admin-card">
    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
        @csrf
        
        <div class="mb-3">
            <label class="form-label">Judul <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                   value="{{ old('title') }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Kategori <span class="text-danger">*</span></label>
            <select name="id_post_category" class="form-select @error('id_post_category') is-invalid @enderror" required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id_category }}" {{ old('id_post_category') == $cat->id_category ? 'selected' : '' }}>
                    {{ $cat->category_name }}
                </option>
                @endforeach
            </select>
            @error('id_post_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tipe Postingan</label>
                    <select name="post_type" class="form-select">
                        <option value="post">Post</option>
                        <option value="page">Page</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="publish">Publish</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar Unggulan</label>
            <input type="file" name="featured_image" class="form-control" accept="image/*">
            <small class="text-muted">Format: JPG, PNG. Maksimal 4MB</small>
            <div id="imagePreview" class="mt-2" style="display: none;">
                <img id="previewImg" src="#" width="150" class="img-thumbnail">
            </div>
        </div>
        
        <!-- 🔥 GALLERY SECTION -->
        <div class="mb-3">
            <label class="form-label">Gallery Images</label>
            <div id="galleryContainer">
                <div class="gallery-item row mb-3" id="galleryItem1">
                    <div class="col-md-4">
                        <input type="file" name="gallery_images[]" class="form-control gallery-input" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Deskripsi gambar (opsional)">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-gallery" data-id="1">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" id="addMoreGallery" class="btn btn-sm btn-secondary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Gambar Lain
                </button>
            </div>
            <small class="text-muted">Format: JPG, PNG. Maksimal 4MB per gambar</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Konten <span class="text-danger">*</span></label>
            <textarea id="tiny-editor" name="post_content" class="form-control @error('post_content') is-invalid @enderror" 
                      rows="15">{{ old('post_content') }}</textarea>
            @error('post_content')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <div class="mb-3">
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/9vwhsdy8sam8oxtsknl77ddzx28dapdx3bjig8xljsfnbge5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
// Inisialisasi TinyMCE
tinymce.init({
    selector: '#tiny-editor',
    height: 500,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | bold italic backcolor | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | removeformat | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
});

// Preview featured image
document.querySelector('input[name="featured_image"]')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Gallery dynamic add/remove
let galleryCount = 1;

document.getElementById('addMoreGallery')?.addEventListener('click', function() {
    galleryCount++;
    const newGalleryItem = document.createElement('div');
    newGalleryItem.className = 'gallery-item row mb-3';
    newGalleryItem.id = 'galleryItem' + galleryCount;
    newGalleryItem.innerHTML = `
        <div class="col-md-4">
            <input type="file" name="gallery_images[]" class="form-control gallery-input" accept="image/*">
        </div>
        <div class="col-md-6">
            <input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Deskripsi gambar (opsional)">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-gallery" data-id="${galleryCount}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    document.getElementById('galleryContainer').appendChild(newGalleryItem);
});

$(document).on('click', '.remove-gallery', function() {
    const id = $(this).data('id');
    $('#galleryItem' + id).remove();
});
</script>
@endsection