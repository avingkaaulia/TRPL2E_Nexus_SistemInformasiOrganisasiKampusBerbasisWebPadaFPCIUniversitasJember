{{-- resources/views/comments.blade.php --}}
<div class="comments-section">
    <div class="container">
        <h3 class="comments-title">Comments ({{ $comments->where('status', 'approved')->count() }})</h3>
        
        {{-- 🔥🔥🔥 CEK APAKAH KOMENTAR AKTIF 🔥🔥🔥 --}}
        @if(!\App\Models\Setting::isCommentsEnabled())
            {{-- KALO NONAKTIF: TAMPILKAN PESAN --}}
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Komentar ditutup!</strong> Admin sedang menonaktifkan fitur komentar untuk sementara waktu.
            </div>
        @else
            {{-- KALO AKTIF: TAMPILKAN FORM KOMENTAR --}}
            <div class="comment-form-wrapper">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
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
                    <div class="info-message mt-2">
                        <i class="bi bi-info-circle"></i>
                        <span>Komentar Anda akan ditampilkan setelah disetujui oleh admin.</span>
                    </div>
                </form>
            </div>
        @endif
        {{-- 🔥 AKHIR DARI PENGECEKAN 🔥 --}}
        
        {{-- 🔥 PESAN UNTUK KOMENTAR PENDING DARI USER YANG SAMA --}}
        @php
            $userEmail = old('email', request()->get('email'));
            $userPendingComments = $comments->where('email', $userEmail)->where('status', 'pending');
        @endphp
        
        @if($userPendingComments->count() > 0)
            <div class="alert alert-warning mt-3">
                <i class="bi bi-clock-history me-2"></i>
                <strong>Menunggu Persetujuan!</strong> Anda memiliki {{ $userPendingComments->count() }} komentar yang sedang menunggu persetujuan admin. Komentar akan muncul setelah disetujui.
            </div>
        @endif
        
        {{-- Daftar Komentar yang sudah disetujui --}}
        <div class="comments-list">
            @forelse($comments->where('status', 'approved') as $comment)
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
                    <p>Jadilah yang pertama berkomentar!</p>
                    <small class="text-muted">Komentar Anda akan muncul setelah disetujui admin.</small>
                </div>
            @endforelse
        </div>
    </div>
</div>