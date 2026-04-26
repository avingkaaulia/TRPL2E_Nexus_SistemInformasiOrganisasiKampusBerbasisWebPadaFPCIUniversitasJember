{{-- resources/views/partials/comments.blade.php --}}
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
                    <textarea name="isi_komentar" rows="4" placeholder="Type your comment here..." class="comment-textarea" required></textarea>
                </div>
                <button type="submit" class="btn-publish">Publish</button>
            </form>
        </div>
        
        {{-- Daftar Komentar --}}
        <div class="comments-list">
            @forelse($comments as $comment)
                <div class="comment-item">
                    <div class="comment-avatar">
                        <img src="{{ asset('assets/img/avatars/default-avatar.png') }}" alt="Avatar" class="avatar-img">
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author">{{ $comment->nama_pengunjung }}</span>
                            <span class="comment-date">{{ \Carbon\Carbon::parse($comment->tanggal)->diffForHumans() }}</span>
                        </div>
                        <p class="comment-text">{{ $comment->isi_komentar }}</p>
                    </div>
                </div>
            @empty
                <div class="no-comments">
                    <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif