{{-- resources/views/admin/logo/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Logo - Admin FPCI UNEJ')
@section('page-title', 'Kelola Logo Website')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-image me-2"></i> Logo Website</h4>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            <div class="text-center mb-4">
                <div class="current-logo">
                    <img src="{{ asset($logo) }}" alt="Current Logo" class="logo-preview">
                    <p class="mt-2 text-muted">Logo saat ini</p>
                </div>
            </div>
            
            <form action="{{ route('admin.logo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="logo">Ganti Logo</label>
                    <input type="file" 
                           name="logo" 
                           id="logo" 
                           class="form-control @error('logo') is-invalid @enderror" 
                           accept="image/*" 
                           required>
                    <small class="text-muted">Format: JPG, PNG, SVG. Max 5MB. Ukuran: 90x90px direkomendasikan</small>
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div id="logoPreview" class="mt-3" style="display: none;">
                    <p>Preview:</p>
                    <img id="previewLogo" src="#" width="90" class="img-thumbnail">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="bi bi-upload me-1"></i> Upload Logo
                    </button>
                    <a href="{{ route('admin.logo.reset') }}" class="btn-cancel" 
                       onclick="return confirm('Reset ke logo default?')">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset ke Default
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-browser-chrome me-2"></i> Favicon</h4>
            </div>
            
            <div class="text-center mb-4">
                <div class="current-favicon">
                    <img src="{{ asset($favicon) }}" alt="Current Favicon" class="favicon-preview">
                    <p class="mt-2 text-muted">Favicon saat ini</p>
                </div>
            </div>
            
            <form action="{{ route('admin.logo.favicon') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="favicon">Ganti Favicon</label>
                    <input type="file" 
                           name="favicon" 
                           id="favicon" 
                           class="form-control @error('favicon') is-invalid @enderror" 
                           accept="image/x-icon,image/png,image/jpeg" 
                           required>
                    <small class="text-muted">Format: ICO, PNG, JPG. Max 5MB. Ukuran: 32x32px direkomendasikan</small>
                    @error('favicon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div id="faviconPreview" class="mt-3" style="display: none;">
                    <p>Preview:</p>
                    <img id="previewFavicon" src="#" width="32" height="32" class="img-thumbnail" style="object-fit: contain;">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="bi bi-upload me-1"></i> Upload Favicon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="admin-card mt-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-eye me-2"></i> Preview Logo di Website</h4>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h6>Navbar:</h6>
            <div class="preview-navbar">
                <img src="{{ asset($logo) }}" class="logo-navbar-preview" alt="Logo Preview">
            </div>
        </div>
        <div class="col-md-6">
            <h6>Footer:</h6>
            <div class="preview-footer">
                <img src="{{ asset($logo) }}" class="logo-footer-preview" alt="Logo Preview">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Preview logo
document.getElementById('logo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewLogo').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Preview favicon
document.getElementById('favicon')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFavicon').src = e.target.result;
            document.getElementById('faviconPreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection