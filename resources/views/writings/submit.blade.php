{{-- resources/views/writings/submit.blade.php --}}
@extends('layouts.app')

@section('title', 'Submit Karya - FPCI UNEJ')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">

<div class="submit-container">
    <div class="submit-wrapper">
        <div class="submit-header">
            <i class="bi bi-pencil-square"></i>
            <h2>Submit Your Writing</h2>
            <p>Share your thoughts, stories, and ideas with our community</p>
        </div>
        
        @if(session('success'))
            <div class="alert-submit success">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert-submit error">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert-submit error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('writings.submit.store') }}" method="POST" enctype="multipart/form-data" class="submit-form" id="submitForm">
            @csrf
            
            <div class="form-group">
                <label for="title">
                    <i class="bi bi-heading"></i> Title <span class="required">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" 
                       placeholder="Enter your title..."
                       required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="id_post_category">
                    <i class="bi bi-tag"></i> Category <span class="required">*</span>
                </label>
                <select name="id_post_category" id="id_post_category" class="form-control @error('id_post_category') is-invalid @enderror" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_category }}" {{ old('id_post_category') == $cat->id_category ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>
                @error('id_post_category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="featured_image">
                    <i class="bi bi-image"></i> Featured Image
                </label>
                <input type="file" 
                       name="featured_image" 
                       id="featured_image" 
                       class="form-control @error('featured_image') is-invalid @enderror" 
                       accept="image/*">
                <small class="text-muted">Format: JPG, PNG. Max 4MB</small>
                <div id="imagePreview" class="mt-2" style="display: none;">
                    <img id="previewImg" src="#" width="150" class="img-thumbnail">
                </div>
                @error('featured_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- 🔥 GALLERY SECTION -->
            <div class="form-group">
                <label for="gallery">
                    <i class="bi bi-images"></i> Gallery Images
                </label>
                <div id="galleryContainer">
                    <div class="gallery-item row mb-3" id="galleryItem1">
                        <div class="col-md-5">
                            <input type="file" name="gallery_images[]" class="form-control gallery-input" accept="image/*">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Description (optional)">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn-remove-gallery" data-id="1">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" id="addMoreGallery" class="btn-add-gallery">
                    <i class="bi bi-plus-circle"></i> Add More Images
                </button>
                <small class="text-muted">Format: JPG, PNG. Max 4MB per image</small>
            </div>
            
            <div class="form-group">
                <label for="post_content">
                    <i class="bi bi-file-text"></i> Content <span class="required">*</span>
                </label>
                <textarea id="tiny-editor" name="post_content">{{ old('post_content') }}</textarea>
                <small class="text-muted">Minimal 50 characters</small>
                @error('post_content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-send"></i> Submit for Review
                </button>
                <a href="{{ route('writings') }}" class="btn-cancel">
                    <i class="bi bi-arrow-left"></i> Cancel
                </a>
            </div>
            
            <div class="info-note">
                <i class="bi bi-info-circle"></i>
                <span>Your submission will be reviewed by admin before being published. You will be notified once approved.</span>
            </div>
        </form>
    </div>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/9vwhsdy8sam8oxtsknl77ddzx28dapdx3bjig8xljsfnbge5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
// TinyMCE Configuration
tinymce.init({
    selector: '#tiny-editor',
    height: 400,
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
document.getElementById('featured_image')?.addEventListener('change', function(e) {
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

// Gallery Dynamic Add/Remove
let galleryCount = 1;

function addGalleryItem() {
    galleryCount++;
    const newId = galleryCount;
    const newGalleryItem = document.createElement('div');
    newGalleryItem.className = 'gallery-item row mb-3';
    newGalleryItem.id = 'galleryItem' + newId;
    newGalleryItem.innerHTML = `
        <div class="col-md-5">
            <input type="file" name="gallery_images[]" class="form-control gallery-input" accept="image/*">
        </div>
        <div class="col-md-5">
            <input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Description (optional)">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn-remove-gallery" data-id="${newId}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    document.getElementById('galleryContainer').appendChild(newGalleryItem);
    
    const removeBtn = newGalleryItem.querySelector('.btn-remove-gallery');
    removeBtn.addEventListener('click', function() {
        newGalleryItem.remove();
    });
}

document.getElementById('addMoreGallery')?.addEventListener('click', addGalleryItem);

document.querySelectorAll('.btn-remove-gallery').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const item = document.getElementById('galleryItem' + id);
        if (item) item.remove();
    });
});
</script>
@endsection