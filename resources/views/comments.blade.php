{{-- resources/views/comments.blade.php --}}
<div class="comments-section">
    <div class="container">
        <h3 class="comments-title">Comments ({{ $comments->count() }})</h3>
        
        {{-- Form Tambah Komentar --}}
        <div class="comment-form-wrapper">
            <form action="{{ route('comments.store', $post->id_post) }}" method="POST" class="comment-form">
                @csrf
                <div class="form-group">
                    <input type="text" name="nama" placeholder="Your Name" class="comment-input" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Email" class="comment-input" required>
                </div>
                <div class="form-group">
                    <textarea name="isi_komentar" rows="4"
                        placeholder="Type your comment here..."
                        class="comment-textarea"
                        maxlength="1000"
                        required>{{ old('isi_komentar') }}</textarea>
                </div>
                <button type="submit" class="btn-publish">Publish Comment</button>
            </form>
        </div>
        
        {{-- Daftar Komentar --}}
        <div class="comments-list">
            @forelse($comments as $comment)
                <div class="comment-item {{ $comment->is_replied ? 'has-reply' : '' }}">
                    <div class="comment-avatar">
                        <i class="bi bi-person-circle avatar-icon"></i>
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author">{{ $comment->nama_pengunjung }}</span>
                            <span class="comment-date">{{ \Carbon\Carbon::parse($comment->tanggal)->diffForHumans() }}</span>
                        </div>
                        <p class="comment-text">{{ $comment->isi_komentar }}</p>
                        
                        {{-- Balasan Admin --}}
                        @if($comment->is_replied && $comment->reply)
                            <div class="admin-reply">
                                <div class="reply-header">
                                    <i class="bi bi-shield-check"></i>
                                    <span class="reply-author">{{ $comment->reply_by ?? 'Admin' }}</span>
                                    <span class="reply-date">{{ \Carbon\Carbon::parse($comment->reply_date)->diffForHumans() }}</span>
                                </div>
                                <p class="reply-text">{{ $comment->reply }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="no-comments">
                    <i class="bi bi-chat-dots"></i>
                    <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('styles')
<style>
/* Admin Reply Styles */
.admin-reply {
    background: #F0F4E8;
    border-left: 4px solid #5C6844;
    padding: 15px 20px;
    margin-top: 15px;
    border-radius: 12px;
}

.reply-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.reply-header i {
    color: #5C6844;
    font-size: 16px;
}

.reply-author {
    font-weight: 700;
    color: #5C6844;
    font-size: 13px;
}

.reply-date {
    color: #999;
    font-size: 11px;
}

.reply-text {
    color: #333;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.avatar-icon {
    font-size: 45px;
    color: #5C6844;
    background: #F5F2E8;
    border-radius: 50%;
    padding: 5px;
}

.comment-item.has-reply {
    border-bottom-color: #E0E8D4;
}
</style>
@endpush