@php
$menus = DB::table('menu')->get();
$contact = DB::table('contact')->first();
$logo = App\Models\Setting::get('site_logo', 'assets/img/logo.png');
$favicon = App\Models\Setting::get('site_favicon', 'assets/img/favicon.ico');
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>FPCI UNEJ</title>

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset($favicon) }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/comments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pendaftaran.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/post.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kegiatan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">

        <!-- LOGO -->
        <img src="{{ asset($logo) }}" width="90" alt="Logo FPCI UNEJ">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
    @php
        $menuParents = $menus->where('id_menu_parent', 0);
        $currentPath = request()->getPathInfo();
    @endphp

    @foreach($menuParents as $parent)
        @php
            $subMenus = $menus->where('id_menu_parent', $parent->id_menu);
            $isActive = $currentPath === $parent->link
                || $subMenus->contains('link', $currentPath);
        @endphp

        @if($subMenus->count() > 0)
            <li class="nav-item fpci-dropdown">
                <a href="{{ $parent->link }}"
                   class="nav-link fpci-dropdown-toggle {{ $isActive ? 'active' : '' }}">
                    {{ $parent->menu_label }}
                </a>
                <ul class="fpci-dropdown-menu">
                    @foreach($subMenus as $sub)
                        <li>
                            <a href="{{ $sub->link }}"
                               class="fpci-dropdown-item {{ $currentPath === $sub->link ? 'active' : '' }}">
                                {{ $sub->menu_label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @else
            <li class="nav-item">
                <a href="{{ $parent->link }}"
                   class="nav-link {{ $isActive ? 'active' : '' }}">
                    {{ $parent->menu_label }}
                </a>
            </li>
        @endif
    @endforeach
</ul>
        </div>

        <!-- SEARCH ICON -->
        <!-- SEARCH FORM dengan autocomplete -->
<form action="{{ route('search') }}" method="GET" class="d-flex align-items-center search-form position-relative" id="searchForm">
    <div class="search-wrapper position-relative">
        <i class="bi bi-search search-icon" id="searchIcon"></i>
        <input type="text" 
               name="q" 
               id="searchInput" 
               class="search-input" 
               placeholder="Cari..." 
               autocomplete="off">
        <div id="searchResults" class="search-autocomplete"></div>
    </div>
</form>
        
        <!-- LOGIN - Hanya tampilkan Login, Register di dalam halaman login -->
@auth
    <div class="dropdown user-dropdown">
        <button class="btn btn-login dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i> {{ Str::limit(Auth::user()->nama, 15) }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @if(Auth::user()->id_role == 1)
                <li>
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard Admin
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
            @endif
            <li>
                <a class="dropdown-item" href="{{ route('profile') }}">
                    <i class="bi bi-person me-2"></i> Profil Saya
                </a>
            </li>
            <li>
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
@else
    <a href="{{ route('login') }}" class="btn btn-login ms-2">
        </i> Login
    </a>
@endauth

    </div>
</nav>

<main class="flex-grow-1">
    @yield('content')
</main>

<!-- FOOTER -->
<div class="footer-wrapper">
    <div class="footer">
        <div class="container">
            <div class="row footer-row">

                <div class="col-md-4">
                    <img src="{{ asset($logo) }}" width="90" alt="Logo FPCI UNEJ">
                    <p>FPCI UNEJ fokus pada isu global & kepemudaan</p>
                </div>

                <div class="col-md-4">
                    <h5>Menu</h5>

                    @php
                        $footerParents = $menus->where('id_menu_parent', 0);
                        $chunks = $footerParents->chunk(ceil($footerParents->count() / 2));
                    @endphp

                    <div class="row">
                        <div class="col-6">
                            @foreach($chunks[0] as $m)
                                <p>
                                    <a href="{{ $m->link }}" class="footer-link">
                                        {{ $m->menu_label }}
                                    </a>
                                </p>
                            @endforeach
                        </div>

                        <div class="col-6">
                            @if(isset($chunks[1]))
                                @foreach($chunks[1] as $m)
                                    <p>
                                        <a href="{{ $m->link }}" class="footer-link">
                                            {{ $m->menu_label }}
                                        </a>
                                    </p>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                 <div class="col-md-4">
                    <h5>Follow Us</h5>

                    <div class="d-flex gap-3 mt-2 flex-wrap">
                        <!-- Instagram -->
                        @if($contact && $contact->instagram)
                        <a href="https://instagram.com/{{ $contact->instagram }}" target="_blank" class="social-icon" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        @endif

                        <!-- LinkedIn -->
                        @if($contact && $contact->linkedin)
                        <a href="https://linkedin.com/company/{{ $contact->linkedin }}" target="_blank" class="social-icon" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        @endif

                        <!-- TikTok -->
                        @if($contact && $contact->tiktok)
                        <a href="https://tiktok.com/@{{ $contact->tiktok }}" target="_blank" class="social-icon" title="TikTok">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        @endif

                        <!-- YouTube -->
                        @if($contact && $contact->youtube)
                        <a href="https://youtube.com/{{ $contact->youtube }}" target="_blank" class="social-icon" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                        @endif

                        <!-- Twitter/X -->
                        @if($contact && $contact->x)
                        <a href="https://twitter.com/{{ $contact->x }}" target="_blank" class="social-icon" title="Twitter / X">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        @endif
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
<div class="footer-bottom">
    Copyright © 2024 fpci-unej
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- TARUH JAVASCRIPT SEARCH DI SINI -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchIcon = document.getElementById('searchIcon');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    
    // Toggle search input
    if (searchIcon) {
        searchIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            searchInput.classList.toggle('active');
            if (searchInput.classList.contains('active')) {
                searchInput.focus();
            } else {
                searchResults.classList.remove('active');
                searchInput.value = '';
            }
        });
    }
    
    // Autocomplete
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('active');
                return;
            }
            
            searchTimeout = setTimeout(function() {
                fetch(`/search/autocomplete?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            searchResults.innerHTML = data.map(item => `
                                <div class="search-suggestion" onclick="window.location.href='${item.url}'">
                                    <i class="bi bi-file-text"></i>
                                    <div class="search-suggestion-content">
                                        <div class="search-suggestion-title">${escapeHtml(item.title)}</div>
                                        <div class="search-suggestion-type">${item.type}</div>
                                    </div>
                                </div>
                            `).join('');
                            searchResults.classList.add('active');
                        } else {
                            searchResults.innerHTML = '<div class="search-suggestion"><i class="bi bi-info-circle"></i><div>Tidak ada saran</div></div>';
                            searchResults.classList.add('active');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }, 300);
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (searchInput && searchResults) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.remove('active');
                }
            }
        });
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
</body>
</html>