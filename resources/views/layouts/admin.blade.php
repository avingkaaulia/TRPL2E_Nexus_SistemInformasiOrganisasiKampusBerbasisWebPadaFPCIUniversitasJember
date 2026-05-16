{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - FPCI UNEJ')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 px-0 sidebar">
            <div class="logo-area">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
                <h5>FPCI UNEJ Admin</h5>
            </div>
            
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <div class="menu-group-title">MANAJEMEN KONTEN</div>
<a href="{{ route('admin.carousel') }}" class="menu-item {{ request()->routeIs('admin.carousel*') ? 'active' : '' }}">
    <i class="bi bi-images"></i> Carousel / Slider
</a>
            <a href="{{ route('admin.posts.index') }}" class="menu-item {{ request()->routeIs('admin.posts*') ? 'active' : '' }}">
    <i class="bi bi-file-post"></i> Semua Postingan
</a>
<a href="{{ route('admin.posts.create') }}" class="menu-item">
    <i class="bi bi-plus-circle"></i> Tambah Post
</a>
<a href="{{ route('admin.pages.list') }}" class="menu-item">
    <i class="bi bi-files"></i> Halaman (Pages)
</a>
            <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
    <i class="bi bi-tags"></i> Kategori
</a>
            
            <div class="menu-group-title">MANAJEMEN</div>
<a href="{{ route('admin.pendaftaran.index') }}" class="menu-item {{ request()->routeIs('admin.pendaftaran*') ? 'active' : '' }}">
    <i class="bi bi-person-plus"></i> Pendaftaran
</a>
<a href="{{ route('admin.anggota.index') }}" class="menu-item {{ request()->routeIs('admin.anggota*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Anggota
<a href="#" class="menu-item">
    <i class="bi bi-chat-dots"></i> Komentar
</a>
            
            <div class="menu-group-title">PENGATURAN</div>
            <a href="{{ route('admin.menu.index') }}" class="menu-item {{ request()->routeIs('admin.menu*') ? 'active' : '' }}">
    <i class="bi bi-gear"></i> Menu Navigasi
</a>
            <a href="{{ route('profile') }}" class="menu-item">
    <i class="bi bi-person"></i> Profil Saya
</a>
<form action="{{ route('logout') }}" method="POST" id="admin-logout-form">
    @csrf
    <button type="submit" class="menu-item logout-btn" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
        <i class="bi bi-box-arrow-right"></i> Logout
    </button>
</form>
</div>
        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h3><i class="bi bi-speedometer2 me-2"></i> @yield('page-title', 'Dashboard')</h3>
                <div class="top-bar-right">
                    <div class="notification-icon">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge">0</span>
                    </div>
                    <div class="admin-profile">
                        <img src="{{ asset('assets/img/avatars/default-avatar.png') }}" alt="Admin">
                        <span>Admin FPCI</span>
                    </div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>