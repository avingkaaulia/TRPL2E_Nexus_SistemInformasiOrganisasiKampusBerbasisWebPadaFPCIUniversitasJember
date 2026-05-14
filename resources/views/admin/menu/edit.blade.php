{{-- resources/views/admin/menu/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Menu - Admin FPCI UNEJ')
@section('page-title', 'Edit Menu Navigasi')

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
        <h6><i class="bi bi-pencil-square me-2"></i> Edit Menu: {{ $menu->menu_label }}</h6>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.menu.update', $menu->id_menu) }}" method="POST" id="menuForm">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                <input type="text" name="menu_label" class="form-control @error('menu_label') is-invalid @enderror"
                    value="{{ old('menu_label', $menu->menu_label) }}"
                    required placeholder="Contoh: Tentang Kami">
                <small class="text-muted">Nama yang akan tampil di navigasi</small>
                @error('menu_label')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Link <span class="text-danger">*</span></label>
                <select name="link" class="form-select @error('link') is-invalid @enderror" required id="link_select">
                    <option value="">-- Pilih Tipe Link --</option>
                    <option value="/" {{ old('link', $menu->link) == '/' ? 'selected' : '' }}>🏠 Home</option>
                    <option value="/about" {{ old('link', $menu->link) == '/about' ? 'selected' : '' }}>ℹ️ About</option>
                    <option value="/writings" {{ old('link', $menu->link) == '/writings' ? 'selected' : '' }}>✍️ Writings</option>
                    <option value="/events" {{ old('link', $menu->link) == '/events' ? 'selected' : '' }}>📅 Events</option>
                    <option value="/contact" {{ old('link', $menu->link) == '/contact' ? 'selected' : '' }}>📞 Contact</option>
                    <optgroup label="📄 Halaman (Pages)">
                        @foreach($pages as $page)
                        <option value="/page/{{ $page->id_post }}"
                            {{ old('link', $menu->link) == '/page/'.$page->id_post ? 'selected' : '' }}>
                            📄 {{ $page->title }}
                        </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="📝 Postingan">
                        @foreach($posts as $post)
                        <option value="/post/{{ $post->id_post }}"
                            {{ old('link', $menu->link) == '/post/'.$post->id_post ? 'selected' : '' }}>
                            📝 {{ Str::limit($post->title, 40) }}
                        </option>
                        @endforeach
                    </optgroup>
                    <option value="custom" id="custom_option">🔗 Custom Link</option>
                </select>
                @error('link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3" id="custom_link_container">
                <label class="form-label">Custom Link URL <span class="text-danger">*</span></label>
                <input type="text" name="custom_link" class="form-control @error('custom_link') is-invalid @enderror"
                    value="{{ old('custom_link', $menu->link) }}" placeholder="https://example.com">
                <small class="text-muted">Contoh: https://instagram.com/fpci_unej atau /halaman-khusus</small>
                @error('custom_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Parent Menu</label>
                <select name="id_menu_parent" class="form-select">
                    <option value="0" {{ old('id_menu_parent', $menu->id_menu_parent) == 0 ? 'selected' : '' }}>
                        Main Menu (Top Level)
                    </option>
                    @foreach($parents as $parent)
                    <option value="{{ $parent->id_menu }}"
                        {{ old('id_menu_parent', $menu->id_menu_parent) == $parent->id_menu ? 'selected' : '' }}>
                        — {{ $parent->menu_label }}
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih "Main Menu" untuk menu utama, atau pilih menu lain sebagai parent</small>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Tips:</strong>
                <ul class="mb-0 mt-1">
                    <li>Perubahan akan langsung tampil di navbar</li>
                    <li>Pastikan link yang dimasukkan valid</li>
                    <li>Menu parent tidak bisa memilih dirinya sendiri</li>
                </ul>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn-save" id="submitBtn">
                    <i class="bi bi-save me-1"></i> Update Menu
                </button>
                <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const linkSelect = document.getElementById('link_select');
    const customContainer = document.getElementById('custom_link_container');
    const customLinkInput = document.querySelector('input[name="custom_link"]');
    
    // Cek apakah link saat ini adalah custom link
    function checkCustomLink() {
        if (linkSelect) {
            const isCustom = linkSelect.value === 'custom';
            customContainer.style.display = isCustom ? 'block' : 'none';
            if (customLinkInput) {
                customLinkInput.required = isCustom;
            }
        }
    }
    
    // Set initial state
    checkCustomLink();
    
    // Add event listener
    if (linkSelect) {
        linkSelect.addEventListener('change', checkCustomLink);
    }
    
    // Validasi sebelum submit
    const form = document.getElementById('menuForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memperbarui...';
            }
            
            if (linkSelect && linkSelect.value === 'custom' && customLinkInput && !customLinkInput.value.trim()) {
                e.preventDefault();
                alert('⚠️ Custom Link URL wajib diisi!');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-save me-1"></i> Update Menu';
                }
                customLinkInput.focus();
            }
        });
    }
});
</script>
@endsection