{{-- resources/views/admin/writings/pending.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Karya - Admin FPCI UNEJ')
@section('page-title', 'Kelola Karya / Writings')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-journal-bookmark-fill me-2"></i> Kelola Karya Writings</h4>
        <div class="stats-badges d-flex gap-2">
    <a href="{{ route('admin.writings.pending') }}" 
       class="badge {{ request('status') == '' ? 'bg-dark' : 'bg-secondary' }} text-decoration-none">
        Semua: {{ $totalPending + $totalPublished + ($totalDraft ?? 0) }}
    </a>
    <a href="{{ route('admin.writings.pending', ['status' => 'pending']) }}" 
       class="badge {{ request('status') == 'pending' ? 'bg-dark' : 'bg-warning' }} text-decoration-none">
        Pending: {{ $totalPending }}
    </a>
    <a href="{{ route('admin.writings.pending', ['status' => 'publish']) }}" 
       class="badge {{ request('status') == 'publish' ? 'bg-dark' : 'bg-success' }} text-decoration-none">
        Published: {{ $totalPublished }}
    </a>
    <a href="{{ route('admin.writings.pending', ['status' => 'draft']) }}" 
       class="badge {{ request('status') == 'draft' ? 'bg-dark' : 'bg-secondary' }} text-decoration-none">
        Draft: {{ $totalDraft ?? 0 }}
    </a>
</div>
    </div>
    
    <!-- Filter -->
    <div class="filter-bar mb-4">
        <form action="{{ route('admin.writings.pending') }}" method="GET" class="d-flex gap-3 flex-wrap align-items-center">
            <select name="status" class="form-select" style="width: 150px;">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="publish" {{ request('status') == 'publish' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Cari judul..." value="{{ request('search') }}" style="width: 250px;">
            <button type="submit" class="btn-search">Filter</button>
            <a href="{{ route('admin.writings.pending') }}" class="btn-reset">Reset</a>
        </form>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th>Tanggal Submit</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingPosts as $post)
                <tr>
                    <td>
                        @if(isset($post->image_url) && $post->image_url)
                            <img src="{{ $post->image_url }}" width="60" height="50" style="object-fit: cover; border-radius: 8px;">
                        @else
                            <div style="width:60px; height:50px; background:#5C6844; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white;">
                                <i class="bi bi-image" style="font-size:20px;"></i>
                            </div>
                        @endif
                    </td>
                    <td class="td-title">{{ Str::limit($post->title, 50) }}</td>
                    <td>{{ $post->category->category_name ?? '-' }}</td>
                    <td>{{ $post->user->nama ?? 'User' }}</td>
                    <td>
                        <span class="badge-status 
                            @if($post->status == 'publish') badge-publish
                            @elseif($post->status == 'pending') badge-pending
                            @else badge-draft @endif">
                            {{ $post->status }}
                        </span>
                    </td>
                    <td class="td-date">
                        <div class="date-human">{{ \Carbon\Carbon::parse($post->date_published)->diffForHumans() }}</div>
                        <div class="date-full">{{ \Carbon\Carbon::parse($post->date_published)->format('d M Y H:i') }}</div>
                    </td>
                    <td class="td-action">
                        <div class="dropdown">
                            <button class="btn-action btn-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.writings.show', $post->id_post) }}" target="_blank">
                                        <i class="bi bi-eye me-2"></i> Detail
                                    </a>
                                </li>
                                @if($post->status == 'pending')
                                    <li>
                                        <form action="{{ route('admin.writings.approve', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="dropdown-item text-success" onclick="return confirm('Setujui karya ini?')">
                                                <i class="bi bi-check-lg me-2"></i> Setujui
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.writings.reject', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Tolak karya ini?')">
                                                <i class="bi bi-x-lg me-2"></i> Tolak
                                            </button>
                                        </form>
                                    </li>
                                @elseif($post->status == 'draft')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.writings.force-delete', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Hapus permanen karya ini?')">
                                                <i class="bi bi-trash me-2"></i> Hapus Permanen
                                            </button>
                                        </form>
                                    </li>
                                @elseif($post->status == 'publish')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.writings.reject', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Tolak karya ini?')">
                                                <i class="bi bi-x-lg me-2"></i> Tolak
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-2">Tidak ada karya</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- 🔥 PAGINATION DENGAN CSS CUSTOM --}}
    <div class="pagination-wrapper">
        {{ $pendingPosts->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection