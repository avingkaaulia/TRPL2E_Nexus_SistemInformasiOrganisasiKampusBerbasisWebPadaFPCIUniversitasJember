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
            <span class="badge bg-success">Disetujui: {{ $approvedComments }}</span>
            <span class="badge bg-danger">Ditolak: {{ $rejectedComments }}</span>
            @if($unrepliedComments > 0)
                <span class="badge bg-info">Belum Dibalas: {{ $unrepliedComments }}</span>
            @endif
        </div>
    </div>
    
    <div class="card-body">
        <!-- 🔥 TOMBOL ON/OFF KOMENTAR -->
        <div class="toggle-comment-bar mb-3 p-3 bg-light rounded d-flex justify-content-between align-items-center">
            <div>
                <strong><i class="bi bi-power me-2"></i> Status Fitur Komentar:</strong>
                @if(\App\Models\Setting::isCommentsEnabled())
                    <span class="badge bg-success">AKTIF</span>
                    <small class="text-muted ms-2">Pengunjung bisa menulis komentar</small>
                @else
                    <span class="badge bg-danger">NONAKTIF</span>
                    <small class="text-muted ms-2">Pengunjung tidak bisa menulis komentar</small>
                @endif
            </div>
            
            <form action="{{ route('admin.comments.toggle') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn {{ \App\Models\Setting::isCommentsEnabled() ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    <i class="bi {{ \App\Models\Setting::isCommentsEnabled() ? 'bi-x-circle' : 'bi-check-circle' }} me-1"></i>
                    {{ \App\Models\Setting::isCommentsEnabled() ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
        </div>
        <!-- Filter -->
        <div class="filter-bar">
            <form action="{{ route('admin.comments.index') }}" method="GET" class="d-flex gap-3 flex-wrap">
                <select name="status" class="form-select" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
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
        
        @if(session('error'))
            <div class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif
        
        <div class="table-responsive mt-4">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Postingan</th>
                        <th>Pengunjung</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Balasan</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                    <tr>
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
                            @if($comment->is_replied && $comment->reply)
                                <div class="reply-preview mt-1">
                                    <small class="text-success">
                                        <i class="bi bi-reply-fill"></i> Balasan: {{ Str::limit($comment->reply, 40) }}
                                    </small>
                                </div>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($comment->tanggal)->format('d M Y H:i') }}</td>
                        <td>
                            @if($comment->status == 'approved')
                                <span class="badge bg-success">Disetujui</span>
                            @elseif($comment->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                        <td>
                            @if($comment->status == 'approved')
                                @if($comment->is_replied)
                                    <span class="badge bg-success">Sudah Dibalas</span>
                                @else
                                    <span class="badge bg-warning">Belum Dibalas</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-group" style="flex-wrap: wrap; gap: 5px;">
                                @if($comment->status == 'pending')
                                    {{-- TOMBOL SETUJUI --}}
                                    <form action="{{ route('admin.comments.approve', $comment->id_comment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-action btn-approve" title="Setujui" onclick="return confirm('Setujui komentar ini?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    
                                    {{-- TOMBOL TOLAK --}}
                                    <form action="{{ route('admin.comments.reject', $comment->id_comment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-action btn-reject" title="Tolak" onclick="return confirm('Tolak komentar ini?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                {{-- TOMBOL BALAS (hanya untuk komentar yang sudah disetujui) --}}
                                @if($comment->status == 'approved')
                                    <button type="button" class="btn-action btn-reply" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#replyModal"
                                            data-id="{{ $comment->id_comment }}"
                                            data-name="{{ $comment->nama_pengunjung }}"
                                            data-comment="{{ $comment->isi_komentar }}"
                                            data-reply="{{ $comment->reply }}"
                                            data-is-replied="{{ $comment->is_replied }}"
                                            title="{{ $comment->is_replied ? 'Edit Balasan' : 'Balas Komentar' }}">
                                        <i class="bi bi-reply"></i>
                                        @if(!$comment->is_replied)
                                            <span class="badge-reply">!</span>
                                        @endif
                                    </button>
                                @endif
                                
                                {{-- TOMBOL HAPUS --}}
                                <form action="{{ route('admin.comments.destroy', $comment->id_comment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Hapus" onclick="return confirm('Hapus komentar ini?')">
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
        </div>
        
        <div class="pagination-wrapper">
            {{ $comments->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<!-- Modal Reply -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="replyForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalTitle">Balas Komentar</h5>
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
                        <small class="text-muted">Balasan akan langsung ditampilkan di halaman postingan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save" id="btnSubmitReply">Kirim Balasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Reply Modal
const replyModal = document.getElementById('replyModal');
if (replyModal) {
    replyModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const commentId = button.getAttribute('data-id');
        const commenterName = button.getAttribute('data-name');
        const originalComment = button.getAttribute('data-comment');
        const existingReply = button.getAttribute('data-reply');
        const isReplied = button.getAttribute('data-is-replied') === '1';
        
        const form = document.getElementById('replyForm');
        form.action = `/admin/comments/${commentId}/reply`;
        
        document.getElementById('commenterName').textContent = commenterName;
        document.getElementById('originalComment').textContent = originalComment;
        
        const replyTextarea = document.getElementById('reply');
        const modalTitle = document.getElementById('replyModalTitle');
        const submitBtn = document.getElementById('btnSubmitReply');
        
        if (isReplied && existingReply) {
            replyTextarea.value = existingReply;
            modalTitle.textContent = 'Edit Balasan Komentar';
            submitBtn.textContent = 'Update Balasan';
        } else {
            replyTextarea.value = '';
            modalTitle.textContent = 'Balas Komentar';
            submitBtn.textContent = 'Kirim Balasan';
        }
    });
}
</script>
@endpush


@endsection