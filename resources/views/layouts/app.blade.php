@php
$menus = DB::table('menu')->get();
$contact = DB::table('contact')->first();
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>FPCI UNEJ</title>

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="{{ asset('assets/css/comments.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">

        <!-- LOGO (LEBIH BESAR) -->
        <img src="{{ asset('assets/img/logo.png') }}" width="90">

        <ul class="navbar-nav mx-auto">
            @foreach($menus as $m)
                <li>
                    <a href="{{ $m->link }}" class="nav-link">
                        {{ $m->menu_label }}
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- SEARCH ICON -->
<form action="/search" method="GET" class="d-flex align-items-center search-form">
    <i class="bi bi-search me-2"></i>
</form>

        <!-- LOGIN -->
        <a class="btn btn-login ms-2">Login</a>

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
                <img src="{{ asset('assets/img/logo.png') }}" width="90">
                <p>FPCI UNEJ fokus pada isu global & kepemudaan</p>
            </div>

            <div class="col-md-4">
    <h5>Menu</h5>

    @php
        $chunks = $menus->chunk(ceil($menus->count() / 2));
    @endphp

    <div class="row">
        <!-- KOLOM KIRI -->
        <div class="col-6">
            @foreach($chunks[0] as $m)
                <p>
                    <a href="{{ $m->link }}" class="footer-link">
                        {{ $m->menu_label }}
                    </a>
                </p>
            @endforeach
        </div>

        <!-- KOLOM KANAN -->
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

<div class="d-flex gap-3 mt-2">

    <!-- INSTAGRAM -->
    @if($contact && $contact->instagram)
    <a href="https://instagram.com/{{ $contact->instagram }}" target="_blank" class="social-icon">
        <i class="bi bi-instagram"></i>
    </a>
    @endif

    <!-- LINKEDIN -->
    @if($contact && $contact->linkedin)
    <a href="https://linkedin.com/in/{{ $contact->linkedin }}" target="_blank" class="social-icon">
        <i class="bi bi-linkedin"></i>
    </a>
    @endif

    <!-- TIKTOK (default / nanti bisa dari DB kalau mau) -->
    <a href="https://tiktok.com" target="_blank" class="social-icon">
        <i class="bi bi-tiktok"></i>
    </a>

    <!-- YOUTUBE -->
    <a href="https://youtube.com" target="_blank" class="social-icon">
        <i class="bi bi-youtube"></i>
    </a>

    <!-- X (TWITTER) -->
    <a href="https://x.com" target="_blank" class="social-icon">
        <i class="bi bi-twitter-x"></i>
    </a>

</div>

        </div>
    </div>
</div>
</div>
<div class="footer-bottom">
    Copyright © 2024 fpci-unej
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>