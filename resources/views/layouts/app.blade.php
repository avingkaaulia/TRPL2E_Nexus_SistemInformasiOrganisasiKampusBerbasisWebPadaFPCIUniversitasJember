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

<div class="site-lightbox" id="siteLightbox" role="dialog" aria-modal="true" aria-labelledby="lightboxTitle" aria-hidden="true">
    <button type="button" class="site-lightbox-close" id="lightboxClose" aria-label="Tutup gambar">&times;</button>
    <button type="button" class="site-lightbox-control site-lightbox-prev" id="lightboxPrev" aria-label="Gambar sebelumnya">&#10094;</button>
    <figure class="site-lightbox-figure">
        <img id="lightboxImage" src="" alt="">
        <figcaption id="lightboxTitle"></figcaption>
    </figure>
    <button type="button" class="site-lightbox-control site-lightbox-next" id="lightboxNext" aria-label="Gambar berikutnya">&#10095;</button>
    <div class="site-lightbox-zoom-controls" role="group" aria-label="Kontrol zoom">
        <button type="button" id="lightboxZoomOut" aria-label="Perkecil gambar">&minus;</button>
        <button type="button" id="lightboxZoomReset" aria-label="Atur ulang ukuran gambar">100%</button>
        <button type="button" id="lightboxZoomIn" aria-label="Perbesar gambar">+</button>
    </div>
</div>

<!-- FOOTER -->
<div class="footer-wrapper">
    <div class="footer">
        <div class="container">
            <div class="row footer-row">

                <div class="col-md-4">
                    <img src="{{ asset($logo) }}" width="90" alt="Logo FPCI UNEJ">
                    <p>{{ $contact->alamat ?? 'Jl. Kalimantan No.37, Tegal Boto Lor, Sumbersari, Kec. Sumbersari, Kabupaten Jember, Jawa Timur 68121' }}</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('siteLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const closeButton = document.getElementById('lightboxClose');
    const previousButton = document.getElementById('lightboxPrev');
    const nextButton = document.getElementById('lightboxNext');
    const zoomOutButton = document.getElementById('lightboxZoomOut');
    const zoomResetButton = document.getElementById('lightboxZoomReset');
    const zoomInButton = document.getElementById('lightboxZoomIn');
    const images = Array.from(document.querySelectorAll('main img:not(.no-lightbox)'))
        .filter(image => image.src);
    let currentIndex = 0;
    let zoomLevel = 1;

    function setZoom(level) {
        zoomLevel = Math.min(3, Math.max(0.5, level));
        lightboxImage.style.transform = `scale(${zoomLevel})`;
        zoomResetButton.textContent = `${Math.round(zoomLevel * 100)}%`;
    }

    function updateLightbox(index) {
        currentIndex = (index + images.length) % images.length;
        const image = images[currentIndex];
        lightboxImage.src = image.currentSrc || image.src;
        lightboxImage.alt = image.alt || 'Gambar';
        lightboxTitle.textContent = image.alt || '';
        setZoom(1);
        previousButton.hidden = images.length < 2;
        nextButton.hidden = images.length < 2;
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxImage.src = '';
    }

    function openLightbox(index) {
        if (!images[index]) {
            return;
        }
        updateLightbox(index);
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        closeButton.focus();
    }

    document.addEventListener('click', function(event) {
        const carouselImage = event.target.closest('main .carousel-item img:not(.no-lightbox)');
        if (!carouselImage) {
            return;
        }
        const carouselLink = carouselImage.closest('a');
        const carouselHref = carouselLink ? carouselLink.getAttribute('href') : '';
        if (carouselLink && carouselHref && carouselHref !== '#') {
            return;
        }

        const imageIndex = images.indexOf(carouselImage);
        if (imageIndex === -1) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        openLightbox(imageIndex);
    }, true);

    images.forEach((image, index) => {
        const link = image.closest('a');
        const href = link ? link.getAttribute('href') : '';
        const hasDestination = link && href && href !== '#';

        if (hasDestination) {
            const linkContainer = document.createElement('span');
            linkContainer.className = 'lightbox-link-container';
            link.parentNode.insertBefore(linkContainer, link);
            linkContainer.appendChild(link);

            const previewButton = document.createElement('button');
            previewButton.type = 'button';
            previewButton.className = 'image-preview-trigger';
            previewButton.setAttribute('aria-label', `Lihat gambar ${image.alt || 'lebih besar'}`);
            previewButton.innerHTML = '&#128269;';
            previewButton.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                openLightbox(index);
            });
            linkContainer.appendChild(previewButton);
            return;
        }

        image.classList.add('lightbox-trigger');
        image.addEventListener('click', function(event) {
            if (link && href === '#') {
                event.preventDefault();
            }
            event.stopPropagation();
            openLightbox(index);
        });
    });

    closeButton.addEventListener('click', closeLightbox);
    zoomOutButton.addEventListener('click', function() {
        setZoom(zoomLevel - 0.25);
    });
    zoomResetButton.addEventListener('click', function() {
        setZoom(1);
    });
    zoomInButton.addEventListener('click', function() {
        setZoom(zoomLevel + 0.25);
    });
    lightboxImage.addEventListener('wheel', function(event) {
        event.preventDefault();
        setZoom(zoomLevel + (event.deltaY < 0 ? 0.1 : -0.1));
    }, { passive: false });
    previousButton.addEventListener('click', function() {
        updateLightbox(currentIndex - 1);
    });
    nextButton.addEventListener('click', function() {
        updateLightbox(currentIndex + 1);
    });
    lightbox.addEventListener('click', function(event) {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', function(event) {
        if (!lightbox.classList.contains('is-open')) {
            return;
        }
        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowLeft' && images.length > 1) {
            updateLightbox(currentIndex - 1);
        } else if (event.key === 'ArrowRight' && images.length > 1) {
            updateLightbox(currentIndex + 1);
        }
    });
});
</script>

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