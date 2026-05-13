{{-- resources/views/admin/posts/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Postingan - Admin FPCI UNEJ')
@section('page-title', 'Edit Postingan')

@section('content')
<div class="admin-card">
    <form action="{{ route('admin.posts.update', $post->id_post) }}" method="POST" enctype="multipart/form-data" id="postForm">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label class="form-label">Judul <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                   value="{{ old('title', $post->title) }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <div class="mb-3">
    <label class="form-label">Kategori <span class="text-danger">*</span></label>
    <select name="id_post_category" class="form-select @error('id_post_category') is-invalid @enderror" required>
        <option value="">Pilih Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id_category }}" {{ old('id_post_category', $post->id_post_category) == $cat->id_category ? 'selected' : '' }}>
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
                        <option value="post" {{ $post->post_type == 'post' ? 'selected' : '' }}>Post</option>
                        <option value="page" {{ $post->post_type == 'page' ? 'selected' : '' }}>Page</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="publish" {{ $post->status == 'publish' ? 'selected' : '' }}>Publish</option>
                        <option value="draft" {{ $post->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ $post->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar Saat Ini</label>
            <div>
                @php
                    $imageFound = false;
                    $imageUrl = '';
                    
                    if($post->featured_image_path) {
                        $storagePath = storage_path('app/public/' . $post->featured_image_path);
                        if(file_exists($storagePath)) {
                            $imageFound = true;
                            $imageUrl = asset('storage/' . $post->featured_image_path);
                        }
                        elseif(file_exists(public_path($post->featured_image_path))) {
                            $imageFound = true;
                            $imageUrl = asset($post->featured_image_path);
                        }
                    }
                @endphp
                
                @if($imageFound)
                    <img src="{{ $imageUrl }}" width="150" class="img-thumbnail">
                @else
                    <div style="width:150px; height:100px; background:#5C6844; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white;">
                        <i class="bi bi-image" style="font-size:32px;"></i>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Ganti Gambar (Opsional)</label>
            <input type="file" name="featured_image" class="form-control" accept="image/*">
            <small class="text-muted">Format: JPG, PNG. Maksimal 4MB</small>
            <div id="imagePreview" class="mt-2" style="display: none;">
                <img id="previewImg" src="#" width="150" class="img-thumbnail">
            </div>
        </div>
        
        <!-- 🔥 EXISTING GALLERY SECTION -->
        @if($post->gallery && $post->gallery->count() > 0)
        <div class="mb-3">
            <label class="form-label">Gallery Saat Ini</label>
            <div class="row">
                @foreach($post->gallery as $gallery)
                <div class="col-md-3 text-center mb-3">
                    <img src="{{ asset('storage/' . $gallery->image_path) }}" class="img-thumbnail" style="height:100px; object-fit:cover;">
                    <p class="small text-muted mt-1">{{ Str::limit($gallery->description, 30) }}</p>
                    <a href="{{ route('admin.posts.gallery.delete', $gallery->id_gallery) }}" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Hapus gambar ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- 🔥 TAMBAH GALLERY BARU - PERBAIKI -->
        <div class="mb-3">
            <label class="form-label">Tambah Gallery Baru</label>
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
                            <i class="bi bi-trash"></i> Hapus
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
                      rows="15">{{ old('post_content', $post->content) }}</textarea>
            @error('post_content')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <div class="mb-3">
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/9vwhsdy8sam8oxtsknl77ddzx28dapdx3bjig8xljsfnbge5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
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

// 🔥 GALLERY DYNAMIC ADD/REMOVE - PERBAIKI
let galleryCount = 1;

function addGalleryItem() {
    galleryCount++;
    const newId = galleryCount;
    const newGalleryItem = document.createElement('div');
    newGalleryItem.className = 'gallery-item row mb-3';
    newGalleryItem.id = 'galleryItem' + newId;
    newGalleryItem.innerHTML = `
        <div class="col-md-4">
            <input type="file" name="gallery_images[]" class="form-control gallery-input" accept="image/*">
        </div>
        <div class="col-md-6">
            <input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Deskripsi gambar (opsional)">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-gallery" data-id="${newId}">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>
    `;
    document.getElementById('galleryContainer').appendChild(newGalleryItem);
    
    const removeBtn = newGalleryItem.querySelector('.remove-gallery');
    removeBtn.addEventListener('click', function() {
        newGalleryItem.remove();
    });
}

document.getElementById('addMoreGallery')?.addEventListener('click', addGalleryItem);

document.querySelectorAll('.remove-gallery').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const item = document.getElementById('galleryItem' + id);
        if (item) item.remove();
    });
});
</script>
@endsection