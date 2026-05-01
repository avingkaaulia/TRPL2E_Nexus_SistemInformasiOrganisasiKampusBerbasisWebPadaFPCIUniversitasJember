@extends('layouts.app')

@section('content')

<!-- CAROUSEL -->
<div id="carouselExampleIndicators" 
     class="carousel slide" 
     data-bs-ride="carousel"
     data-bs-interval="5000">
<div class="carousel-indicators">
    @foreach($carousel as $key => $c)
        <button data-bs-target="#carouselExampleIndicators"
                data-bs-slide-to="{{ $key }}"
                class="{{ $key == 0 ? 'active' : '' }}">
        </button>
    @endforeach
</div>

<div class="carousel-inner">

@foreach($carousel as $key => $c)
<div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
    <img src="{{ asset($c->featured_image_path) }}" class="d-block w-100">

    <div class="carousel-caption">
        <h1>{{ $c->title }}</h1>
        <p>{{ $c->content }}</p>
    </div>
</div>
@endforeach

</div>
</div>

<!-- ABOUT -->
 <div class="about-card">
<div class="text-center mt-5">
    <h2>{{ $about->title ?? 'About' }}</h2>
    <p>{{ $about->content ?? '' }}</p>
<a href="/about" class="btn btn-outline-main">Learn More</a>
</div>
</div>
<!-- LATEST -->
<div class="container mt-5">
    <div class="section-header">
<h2 class="text-center">{{ $latestTitle }}</h2>
<div class="section-line"></div>
    </div>
    
<div class="row">
@foreach($posts as $p)
<div class="col-md-3">
    <div class="card card-custom">
        <a href="/post/{{ $p->id_post }}">
    <img src="{{ asset($p->featured_image_path) }}" class="card-img-top">
</a>
        
        <div class="p-3">
            <button class="btn btn-sm btn-main">
                {{ $p->category->category_name }}
            </button>

            <h6>{{ $p->title }}</h6>
        </div>
    </div>
</div>
@endforeach
</div>
</div>

<!-- URGENT -->
 
<div class="container mt-5 ">
    <div class="section-header">
<h2 class="text-center mt-5">{{ $urgentTitle }}</h2>
<div class="section-line"></div>
    </div>
<div class="row">
@foreach($urgent as $u)
<div class="col-md-6">
    <div class="urgent-box">
        <div class="row">

            <div class="col-md-9">
                <h5>{{ $u->title }}</h5>
                <p>{{ Str::limit($u->content,100) }}</p>
            </div>

            <div class="col-md-3 text-end">
                <img src="{{ asset($u->featured_image_path) }}" width="70">
            </div>

        </div>
    </div>
</div>
@endforeach
</div>
</div>
<!-- 🔥 TOMBOL PENDAFTARAN (DI BAWAH URGENT) -->
<div class="container mt-5 mb-5">
    <div class="pendaftaran-cta">
        <div class="pendaftaran-cta-content">
            <h3>📋 {{ $isPendaftaranOpen ? 'Pendaftaran Anggota Baru Dibuka!' : 'Pendaftaran Sedang Ditutup' }}</h3>
            @if($isPendaftaranOpen && isset($pendaftaranInfo))
                <p>Periode: {{ $pendaftaranInfo->nama_periode }} ({{ $pendaftaranInfo->tahun_ajaran }})</p>
                <p>📅 {{ \Carbon\Carbon::parse($pendaftaranInfo->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($pendaftaranInfo->tanggal_selesai)->format('d M Y') }}</p>
                <p>🎯 Kuota tersisa: {{ $pendaftaranInfo->kuota - $pendaftaranInfo->getJumlahPendaftarAttribute() }} dari {{ $pendaftaranInfo->kuota }}</p>
            @elseif(!$isPendaftaranOpen)
                <p>Pendaftaran anggota baru sedang ditutup. Silahkan tunggu informasi selanjutnya.</p>
            @endif
            
            <a href="{{ route('pendaftaran') }}" class="btn-pendaftaran">
                {{ $isPendaftaranOpen ? 'Daftar Sekarang →' : 'Info Pendaftaran' }}
            </a>
        </div>
    </div>
</div>

@endsection