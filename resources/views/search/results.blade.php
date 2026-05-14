@extends('layouts.app')

@section('content')
<div class="search-results-page">
    <div class="container">
        <div class="search-header">
            <h1>Hasil Pencarian</h1>
            <p>Menampilkan hasil untuk: <span class="query">"{{ $query }}"</span></p>
            <p class="text-muted">Ditemukan {{ $totalResults }} hasil</p>
        </div>
        
        <!-- Search Tabs -->
        <div class="search-tabs">
            <a href="?q={{ $query }}&type=all" class="search-tab {{ $type == 'all' ? 'active' : '' }}">
                Semua <span class="count">{{ $totalResults }}</span>
            </a>
            <a href="?q={{ $query }}&type=posts" class="search-tab {{ $type == 'posts' ? 'active' : '' }}">
                Postingan <span class="count">{{ $counts['posts'] }}</span>
            </a>
            <a href="?q={{ $query }}&type=writings" class="search-tab {{ $type == 'writings' ? 'active' : '' }}">
                Writings <span class="count">{{ $counts['writings'] }}</span>
            </a>
            <a href="?q={{ $query }}&type=kegiatan" class="search-tab {{ $type == 'kegiatan' ? 'active' : '' }}">
                Kegiatan <span class="count">{{ $counts['kegiatan'] }}</span>
            </a>
            <a href="?q={{ $query }}&type=about" class="search-tab {{ $type == 'about' ? 'active' : '' }}">
                About <span class="count">{{ $counts['about'] }}</span>
            </a>
        </div>
        
        <!-- Results -->
        @if($totalResults > 0)
            @foreach($results as $type => $items)
                @if(count($items) > 0 && ($type == $type || $type == 'all'))
                    @foreach($items as $item)
                        <div class="result-card">
                            @if($item->image_url ?? false)
                                <img src="{{ $item->image_url }}" class="result-image" alt="{{ $item->title ?? $item->nama_lengkap ?? '' }}">
                            @else
                                <div class="result-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-file-text" style="font-size: 40px; color: #ccc;"></i>
                                </div>
                            @endif
                            
                            <div class="result-content">
                                <span class="result-badge">
                                    @if($type == 'posts') Postingan
                                    @elseif($type == 'writings') Writings
                                    @elseif($type == 'kegiatan') Kegiatan
                                    @elseif($type == 'about') Anggota
                                    @else {{ ucfirst($type) }}
                                    @endif
                                </span>
                                
                                <a href="{{ $item->link ?? '/post/' . $item->id_post }}" class="result-title">
                                    {{ $item->title ?? $item->nama_lengkap ?? 'Untitled' }}
                                </a>
                                
                                <div class="result-excerpt">
                                    {{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? $item->bio ?? ''), 150) }}
                                </div>
                                
                                <div class="result-meta">
                                    @if(isset($item->category))
                                        Kategori: {{ $item->category->category_name ?? 'Uncategorized' }}
                                    @elseif(isset($item->jabatan))
                                        {{ $item->jabatan }}
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
                <p>Tidak ditemukan konten yang sesuai dengan "{{ $query }}"</p>
                <p class="text-muted">Coba gunakan kata kunci lain atau periksa ejaan Anda</p>
            </div>
        @endif
    </div>
</div>
@endsection