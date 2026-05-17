{{-- resources/views/admin/comments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Komentar - Admin FPCI UNEJ')
@section('page-title', 'Kelola Komentar')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-chat-dots me-2"></i> Manajemen Komentar</h4>
        <div class="stats-badges">
            <span class="badge bg-secondary">Total: {{ $totalComments }}</span>
            <span class="badge bg-warning">Pending: {{ $pendingComments }}</span>
            <span class="badge bg-success">Replied: {{ $repliedComments }}</span>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter & Search -->
        <div class="filter-bar">
            <form action="{{ route('admin.comments.index') }}" method="GET" class="d-flex gap-3 flex-wrap">
                <select name="status" class="form-select" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Sudah Dibalas</option>
                </select>
                <input type="text" name="search" class="form-control" placeholder="Cari komentar..." value="{{ request('search') }}" style="width: 250px;">
                <button type="submit" class="btn-search">Filter</button>
                <a href="{{ route('admin.comments.index') }}" class="btn-reset">Reset</a>
            </form>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        
        <div class="table-responsive mt-4">
            <form action="{{ route('admin.comments.bulk') }}" method="POST" id="bulkForm">
                @csrf
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="selectAll"></th>
                            <th width="50">ID</th>
                            <th>Postingan</th>
                            <th>Pengunjung</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comments as $comment)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $comment->id_comment }}" class="comment-checkbox"></td>
                            <td>{{ $comment->id_comment }}</td>
                            <td>
                                <a href="{{ route('post.show', $comment->id_post) }}" target="_blank">
                                    {{ Str::limit($comment->post->title ?? 'No Title', 40) }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $comment->nama_pengunjung }}</strong><br>
                                <small class="text-muted">{{ $comment->email }}</small>
                            </td>
                            <td>
                                <div class="comment-preview">{{ Str::limit($comment->isi_komentar, 50) }}</div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($comment->tanggal)->format('d M Y H:i') }}</td>
                            <td>
                                @if($comment->is_replied)
                                    <span class="badge bg-success">Sudah Dibalas</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-group">
                                    <button type="button" class="btn-action btn-reply" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#replyModal"
                                            data-id="{{ $comment->id_comment }}"
                                            data-name="{{ $comment->nama_pengunjung }}"
                                            data-comment="{{ $comment->isi_komentar }}">
                                        <i class="bi bi-reply"></i>
                                    </button>
                                    <form action="{{ route('admin.comments.destroy', $comment->id_comment) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" 
                                                onclick="return confirm('Hapus komentar ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mt-2">Belum ada komentar</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($comments->count() > 0)
                <div class="bulk-actions mt-3">
                    <select name="action" class="form-select" style="width: auto; display: inline-block;">
                        <option value="">Pilih Aksi</option>
                        <option value="delete">Hapus Terpilih</option>
                    </select>
                    <button type="submit" class="btn-bulk">Terapkan</button>
                </div>
                @endif
            </form>
        </div>
        
        {{ $comments->appends(request()->query())->links() }}
    </div>
</div>

<!-- Modal Reply -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="replyForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Balas Komentar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="original-comment mb-3">
                        <strong>Dari: <span id="commenterName"></span></strong>
                        <p id="originalComment" class="mt-2 text-muted"></p>
                    </div>
                    <div class="form-group">
                        <label for="reply">Balasan Anda:</label>
                        <textarea name="reply" id="reply" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save">Kirim Balasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Select All Checkbox
document.getElementById('selectAll')?.addEventListener('change', function(e) {
    document.querySelectorAll('.comment-checkbox').forEach(cb => cb.checked = e.target.checked);
});

// Reply Modal
const replyModal = document.getElementById('replyModal');
if (replyModal) {
    replyModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const commentId = button.getAttribute('data-id');
        const commenterName = button.getAttribute('data-name');
        const originalComment = button.getAttribute('data-comment');
        
        const form = document.getElementById('replyForm');
        form.action = `/admin/comments/${commentId}/reply`;
        
        document.getElementById('commenterName').textContent = commenterName;
        document.getElementById('originalComment').textContent = originalComment;
    });
}
</script>
@endpush

@push('styles')
<style>
.stats-badges {
    display: flex;
    gap: 10px;
}
.stats-badges .badge {
    font-size: 12px;
    padding: 5px 12px;
}
.filter-bar {
    padding-bottom: 15px;
    border-bottom: 1px solid #E8E4D9;
}
.btn-search, .btn-reset {
    padding: 8px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
}
.btn-search {
    background: #5C6844;
    color: white;
}
.btn-reset {
    background: #f8f9fa;
    color: #1C150F;
    border: 1px solid #ddd;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.btn-bulk {
    background: #6c757d;
    color: white;
    border: none;
    padding: 6px 20px;
    border-radius: 8px;
    margin-left: 10px;
}
.comment-preview {
    max-width: 200px;
    font-size: 13px;
    color: #666;
}
.original-comment {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
}
.btn-reply {
    background: #17a2b8;
    color: white;
}
.btn-reply:hover {
    background: #138496;
}
</style>
@endpush
@endsection