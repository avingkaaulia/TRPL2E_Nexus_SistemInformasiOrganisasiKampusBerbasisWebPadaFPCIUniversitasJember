@extends('layouts.app')

@section('content')
<div class="search-results-page">
    <div class="container">
        <div class="search-header">
            <h1>Hasil Pencarian</h1>
            <p>Menampilkan hasil untuk: <span class="query">"{{ htmlspecialchars($query) }}"</span></p>
            <p class="text-muted">Ditemukan {{ $totalResults }} hasil</p>
        </div>
        
        
<!-- Search Tabs -->
<div class="search-tabs">
    <a href="?q={{ urlencode($query) }}&type=all" class="search-tab {{ $type == 'all' ? 'active' : '' }}">
        <i class="bi bi-search"></i> Semua <span class="count">{{ $totalResults }}</span>
    </a>
    <a href="?q={{ urlencode($query) }}&type=posts" class="search-tab {{ $type == 'posts' ? 'active' : '' }}">
        <i class="bi bi-file-post"></i> Postingan <span class="count">{{ $counts['posts'] ?? 0 }}</span>
    </a>
    <a href="?q={{ urlencode($query) }}&type=writings" class="search-tab {{ $type == 'writings' ? 'active' : '' }}">
        <i class="bi bi-pencil-square"></i> Writings <span class="count">{{ $counts['writings'] ?? 0 }}</span>
    </a>
    <a href="?q={{ urlencode($query) }}&type=events" class="search-tab {{ $type == 'events' ? 'active' : '' }}">
        <i class="bi bi-calendar-event"></i> Events <span class="count">{{ $counts['events'] ?? 0 }}</span>
    </a>
    <a href="?q={{ urlencode($query) }}&type=about" class="search-tab {{ $type == 'about' ? 'active' : '' }}">
    <i class="bi bi-info-circle"></i> About <span class="count">{{ $counts['about'] ?? 0 }}</span>
</a>
    <a href="?q={{ urlencode($query) }}&type=contact" class="search-tab {{ $type == 'contact' ? 'active' : '' }}">
        <i class="bi bi-envelope"></i> Kontak <span class="count">{{ $counts['contact'] ?? 0 }}</span>
    </a>
</div>
        
        <!-- Results -->
        @if($totalResults > 0)
            @foreach($results as $resultType => $items)
                @if(count($items) > 0)
                    @foreach($items as $item)
                        <div class="result-card">
                            @if(isset($item->image_url) && $item->image_url)
                                <img src="{{ $item->image_url }}" class="result-image" alt="{{ $item->title ?? $item->nama_lengkap ?? '' }}">
                            @else
                                <div class="result-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-file-text" style="font-size: 40px; color: #ccc;"></i>
                                </div>
                            @endif
                            
                            <div class="result-content">
                                <span class="result-badge">
                                    @if($resultType == 'posts') 
                                        <i class="bi bi-file-post"></i> Postingan
                                    @elseif($resultType == 'writings') 
                                        <i class="bi bi-pencil-square"></i> Writings
                                    @elseif($resultType == 'events') 
                                        <i class="bi bi-calendar-event"></i> Events & Programs
                                    @elseif($resultType == 'contact') 
                                        <i class="bi bi-envelope"></i> Kontak
                                    @elseif($resultType == 'about') 
                                    <i class="bi bi-info-circle"></i> About FPCI
                                    @else 
                                        {{ ucfirst($resultType) }}
                                    @endif
                                </span>
                                
                                <a href="{{ $item->link ?? '#' }}" class="result-title">
                                    {{ $item->title ?? $item->nama_lengkap ?? 'Untitled' }}
                                </a>
                                
                                <div class="result-excerpt">
                                    @if($resultType == 'contact' && isset($item->contact_data))
                                        <div class="contact-info">
                                            <div><i class="bi bi-envelope"></i> Email: {{ $item->contact_data->email }}</div>
                                            <div><i class="bi bi-telephone"></i> No HP: {{ $item->contact_data->no_hp }}</div>
                                            <div><i class="bi bi-geo-alt"></i> Alamat: {{ Str::limit($item->contact_data->alamat, 100) }}</div>
                                            <div class="social-links">
                                                @if($item->contact_data->instagram)
                                                    <span><i class="bi bi-instagram"></i> @{{ $item->contact_data->instagram }}</span>
                                                @endif
                                                @if($item->contact_data->linkedin)
                                                    <span><i class="bi bi-linkedin"></i> {{ $item->contact_data->linkedin }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{ \Illuminate\Support\Str::limit(strip_tags($item->excerpt ?? $item->content ?? $item->bio ?? ''), 150) }}
                                    @endif
                                </div>
                                
                                <div class="result-meta">
                                    @if($resultType == 'about')
                                        <i class="bi bi-briefcase"></i> {{ $item->jabatan ?? 'Member' }}
                                        @if(isset($item->periode))
                                            <span class="separator">•</span>
                                            <i class="bi bi-calendar"></i> Periode {{ $item->periode }}
                                        @endif
                                    @elseif($resultType == 'contact')
                                        <i class="bi bi-building"></i> FPCI UNEJ
                                        <span class="separator">•</span>
                                        <i class="bi bi-clock"></i> Kontak Resmi
                                    @elseif(isset($item->category_name))
                                        <i class="bi bi-tag"></i> {{ $item->category_name }}
                                    @elseif(isset($item->category) && $item->category)
                                        <i class="bi bi-tag"></i> {{ $item->category->category_name ?? 'Uncategorized' }}
                                    @endif
                                    
                                    @if(isset($item->date_published))
                                        <span class="separator">•</span>
                                        <i class="bi bi-clock"></i> {{ date('d M Y', strtotime($item->date_published)) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        @else
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h3>Tidak ada hasil ditemukan</h3>
                <p>Tidak ditemukan konten yang sesuai dengan "{{ htmlspecialchars($query) }}"</p>
                <p class="text-muted">Coba gunakan kata kunci lain atau periksa ejaan Anda</p>
                
                <div class="suggestions mt-4">
                    <h5>Suggestions:</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle"></i> Pastikan semua kata sudah benar</li>
                        <li><i class="bi bi-check-circle"></i> Coba gunakan kata kunci yang lebih umum</li>
                        <li><i class="bi bi-check-circle"></i> Coba gunakan kata kunci yang lebih singkat</li>
                        <li><i class="bi bi-check-circle"></i> Lihat postingan terbaru di halaman utama</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection


