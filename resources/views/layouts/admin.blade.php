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
    @php
        $logo = App\Models\Setting::get('site_logo', 'assets/img/logo.png');
        $favicon = App\Models\Setting::get('site_favicon', 'assets/img/favicon.ico');
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ asset($favicon) }}">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 px-0 sidebar">
            <div class="logo-area">
                <img src="{{ asset($logo) }}" alt="Logo">
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
            <a href="{{ route('admin.writings.pending') }}" class="menu-item {{ request()->routeIs('admin.writings*') ? 'active' : '' }}">
    <i class="bi bi-journal-bookmark-fill"></i> Kelola Karya
    @php $pendingWritings = App\Models\Post::where('status', 'pending')->where('post_type', 'post')->count(); @endphp
    @if($pendingWritings > 0)
        <span class="badge bg-warning ms-2">{{ $pendingWritings }}</span>
    @endif
</a>
            <div class="menu-group-title">MANAJEMEN</div>
            <a href="{{ route('admin.pendaftaran.index') }}" class="menu-item {{ request()->routeIs('admin.pendaftaran*') ? 'active' : '' }}">
                <i class="bi bi-person-plus"></i> Pendaftaran
            </a>
            <a href="{{ route('admin.anggota.index') }}" class="menu-item {{ request()->routeIs('admin.anggota*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Anggota
            </a>
           <a href="{{ route('admin.comments.index') }}" class="menu-item {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
    <i class="bi bi-chat-dots"></i> Komentar
    @php 
        // 🔥 HITUNG KOMENTAR YANG PERLU DITINDAKLANJUTI:
        // 1. Komentar dengan status pending (perlu approve/reject)
        // 2. Komentar dengan status approved tapi belum dibalas (is_replied = 0)
        $pendingComments = App\Models\Comment::where('status', 'pending')->count();
        $unrepliedComments = App\Models\Comment::where('status', 'approved')->where('is_replied', 0)->count();
        $totalNeedAction = $pendingComments + $unrepliedComments;
    @endphp
    @if($totalNeedAction > 0)
        <span class="badge bg-danger ms-2">{{ $totalNeedAction }}</span>
    @endif
</a>
            
            <div class="menu-group-title">PENGATURAN</div>
            <a href="{{ route('admin.menu.index') }}" class="menu-item {{ request()->routeIs('admin.menu*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Menu Navigasi
            </a>
            <a href="{{ route('admin.contact.index') }}" class="menu-item {{ request()->routeIs('admin.contact*') ? 'active' : '' }}">
    <i class="bi bi-envelope"></i> Kontak & Sosmed
</a>
<a href="{{ route('admin.logo.index') }}" class="menu-item {{ request()->routeIs('admin.logo*') ? 'active' : '' }}">
    <i class="bi bi-image"></i> Logo & Favicon
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
                    <div class="admin-profile dropdown">
                        <div class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="admin-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <span>{{ Auth::user()->nama ?? 'Admin' }}</span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="bi bi-person me-2"></i> Profil Saya
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
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