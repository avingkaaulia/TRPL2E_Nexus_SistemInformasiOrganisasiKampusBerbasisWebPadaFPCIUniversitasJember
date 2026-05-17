{{-- resources/views/admin/writings/pending.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Karya - Admin FPCI UNEJ')
@section('page-title', 'Kelola Karya / Writings')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-journal-bookmark-fill me-2"></i> Kelola Karya Writings</h4>
        <div class="stats-badges">
            <span class="badge bg-warning">Pending: {{ $totalPending }}</span>
            <span class="badge bg-success">Published: {{ $totalPublished }}</span>
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
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th>Tanggal Submit</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingPosts as $post)
                <tr>
                    <td>{{ $post->id_post }}</td>
                    <td>
                        @if(isset($post->image_url) && $post->image_url)
                            <img src="{{ $post->image_url }}" width="60" height="50" style="object-fit: cover; border-radius: 8px;">
                        @else
                            <div style="width:60px; height:50px; background:#5C6844; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white;">
                                <i class="bi bi-image" style="font-size:20px;"></i>
                            </div>
                        @endif
                    </td>
                    <td>{{ Str::limit($post->title, 50) }}</td>
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
                    <td>{{ \Carbon\Carbon::parse($post->date_published)->format('d M Y H:i') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.writings.show', $post->id_post) }}" class="btn-action btn-view" title="Detail" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($post->status == 'pending')
                            <form action="{{ route('admin.writings.approve', $post->id_post) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-action btn-approve" onclick="return confirm('Setujui karya ini?')" title="Setujui">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.writings.reject', $post->id_post) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-reject" onclick="return confirm('Tolak dan hapus karya ini?')" title="Tolak">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @elseif($post->status == 'publish')
                            <form action="{{ route('admin.writings.reject', $post->id_post) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Hapus karya ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-2">Tidak ada karya</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="d-flex justify-content-center mt-4">
        {{ $pendingPosts->appends(request()->query())->links() }}
    </div>
</div>
@endsection