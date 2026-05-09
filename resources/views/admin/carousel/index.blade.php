{{-- resources/views/admin/carousel/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Carousel - Admin FPCI UNEJ')
@section('page-title', 'Manajemen Carousel / Slider')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Nav Tabs -->
<ul class="nav nav-tabs mb-4" id="carouselTab" role="tablist">
    @foreach($carouselCategories as $index => $cat)
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $index == 0 ? 'active' : '' }}" 
                id="tab-{{ $cat->id_category }}" 
                data-bs-toggle="tab" 
                data-bs-target="#content-{{ $cat->id_category }}" 
                type="button" 
                role="tab">
            <i class="bi bi-image me-1"></i> {{ $cat->category_name }}
            <span class="badge bg-secondary ms-1">{{ $carouselsByCategory[$cat->id_category]->count() }}</span>
        </button>
    </li>
    @endforeach
</ul>

<!-- Tab Content -->
<div class="tab-content">
    @foreach($carouselCategories as $index => $cat)
    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" 
         id="content-{{ $cat->id_category }}" 
         role="tabpanel">
        
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-images me-2"></i> {{ $cat->category_name }}</h4>
                <a href="{{ route('admin.carousel.create', $cat->id_category) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Slide
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carouselsByCategory[$cat->id_category] as $key => $slide)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @php
                                    // Cek berbagai kemungkinan lokasi gambar
                                    $imageFound = false;
                                    $imageUrl = '';
                                    
                                    // Cek di storage
                                    if($slide->featured_image_path) {
                                        $storagePath = storage_path('app/public/' . $slide->featured_image_path);
                                        if(file_exists($storagePath)) {
                                            $imageFound = true;
                                            $imageUrl = asset('storage/' . $slide->featured_image_path);
                                        }
                                    }
                                    
                                    // Cek di public/img
                                    if(!$imageFound && $slide->featured_image_path) {
                                        $publicPath = public_path($slide->featured_image_path);
                                        if(file_exists($publicPath)) {
                                            $imageFound = true;
                                            $imageUrl = asset($slide->featured_image_path);
                                        }
                                    }
                                    
                                    // Cek di public/assets/img
                                    if(!$imageFound && $slide->featured_image_path) {
                                        $assetsPath = public_path('assets/' . $slide->featured_image_path);
                                        if(file_exists($assetsPath)) {
                                            $imageFound = true;
                                            $imageUrl = asset('assets/' . $slide->featured_image_path);
                                        }
                                    }
                                    
                                    // Cek di public/images
                                    if(!$imageFound && $slide->featured_image_path) {
                                        $imagesPath = public_path('images/' . $slide->featured_image_path);
                                        if(file_exists($imagesPath)) {
                                            $imageFound = true;
                                            $imageUrl = asset('images/' . $slide->featured_image_path);
                                        }
                                    }
                                @endphp
                                
                                @if($imageFound)
                                    <img src="{{ $imageUrl }}" 
                                         width="80" height="50" style="object-fit: cover; border-radius: 8px;">
                                @else
                                    <div style="width:80px; height:50px; background: #5C6844; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white;">
                                        <i class="bi bi-image" style="font-size:24px;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ Str::limit($slide->title, 40) }}</td>
                            <td>{{ Str::limit(strip_tags($slide->content), 50) }}</td>
                            <td>
                                <span class="badge-status {{ $slide->status == 'publish' ? 'badge-publish' : 'badge-draft' }}">
                                    {{ $slide->status }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($slide->date_published)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.carousel.edit', $slide->id_post) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.carousel.destroy', $slide->id_post) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Hapus slide ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada slide carousel</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection