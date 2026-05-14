{{-- resources/views/admin/menu/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Menu - Admin FPCI UNEJ')
@section('page-title', 'Tambah Menu Navigasi')

@section('content')
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="form-card mx-auto" style="max-width: 600px;">
    <div class="form-card-head">
        <h6><i class="bi bi-plus-circle me-2"></i> Tambah Menu Baru</h6>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.menu.store') }}" method="POST" id="menuForm">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                <input type="text" name="menu_label" class="form-control @error('menu_label') is-invalid @enderror" 
                       value="{{ old('menu_label') }}" required placeholder="Contoh: Tentang Kami">
                <small class="text-muted">Nama yang akan tampil di navigasi</small>
                @error('menu_label')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Link <span class="text-danger">*</span></label>
                <select name="link" class="form-select @error('link') is-invalid @enderror" required id="link_select">
                    <option value="">-- Pilih Tipe Link --</option>
                    <option value="/">🏠 Home</option>
                    <option value="/about">ℹ️ About</option>
                    <option value="/writings">✍️ Writings</option>
                    <option value="/events">📅 Events</option>
                    <option value="/contact">📞 Contact</option>
                    <optgroup label="📄 Halaman (Pages)">
                        @foreach($pages as $page)
                        <option value="/page/{{ $page->id_post }}">📄 {{ $page->title }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="📝 Postingan">
                        @foreach($posts as $post)
                        <option value="/post/{{ $post->id_post }}">📝 {{ Str::limit($post->title, 40) }}</option>
                        @endforeach
                    </optgroup>
                    <option value="custom">🔗 Custom Link</option>
                </select>
                @error('link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3" id="custom_link_container" style="display: none;">
                <label class="form-label">Custom Link URL <span class="text-danger">*</span></label>
                <input type="text" name="custom_link" class="form-control @error('custom_link') is-invalid @enderror" 
                       value="{{ old('custom_link') }}" placeholder="https://example.com">
                <small class="text-muted">Contoh: https://instagram.com/fpci_unej atau /halaman-khusus</small>
                @error('custom_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Parent Menu</label>
                <select name="id_menu_parent" class="form-select">
                    <option value="0">Main Menu (Top Level)</option>
                    @foreach($parents as $parent)
                    <option value="{{ $parent->id_menu }}">— {{ $parent->menu_label }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih "Main Menu" untuk menu utama, atau pilih menu lain sebagai parent</small>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Tips:</strong>
                <ul class="mb-0 mt-1">
                    <li>Gunakan nama menu yang singkat dan jelas</li>
                    <li>Link bisa menggunakan URL internal (/) atau eksternal (https://...)</li>
                    <li>Menu akan otomatis muncul di navbar setelah ditambahkan</li>
                </ul>
            </div>
            
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn-save" id="submitBtn">
                    <i class="bi bi-save me-1"></i> Simpan Menu
                </button>
                <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Tampilkan custom link input jika memilih "custom"
const linkSelect = document.querySelector('#link_select');
const customContainer = document.getElementById('custom_link_container');
const customLinkInput = document.querySelector('input[name="custom_link"]');

if (linkSelect) {
    linkSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customContainer.style.display = 'block';
            customLinkInput.required = true;
        } else {
            customContainer.style.display = 'none';
            customLinkInput.required = false;
        }
    });
}

// Validasi sebelum submit
const form = document.getElementById('menuForm');
const submitBtn = document.getElementById('submitBtn');

if (form) {
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyimpan...';
        
        // Validasi tambahan
        if (linkSelect.value === 'custom' && !customLinkInput.value.trim()) {
            e.preventDefault();
            alert('⚠️ Custom Link URL wajib diisi!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Menu';
            customLinkInput.focus();
        }
    });
}
</script>
@endsection