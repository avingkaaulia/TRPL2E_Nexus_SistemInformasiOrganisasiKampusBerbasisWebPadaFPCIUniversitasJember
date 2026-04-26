{{-- resources/views/test-post.blade.php --}}
@extends('layouts.app')

@section('content')
{{-- HANYA ISI KONTEN SIMPLE UNTUK TESTING --}}
<div class="test-post-wrapper">
    <div class="container mt-5">
        {{-- CARD POST SIMULASI (TIDAK MENGGANGGU TEMAN) --}}
        <div class="test-post-card">
            <img src="{{ asset($post->featured_image_path) }}" class="test-post-image" alt="{{ $post->title }}">
            <div class="test-post-content">
                <h1 class="test-post-title">{{ $post->title }}</h1>
                <div class="test-post-meta">
                    <span>By {{ $post->user->nama ?? 'Admin' }}</span>
                    <span>{{ \Carbon\Carbon::parse($post->date_published)->format('d M Y') }}</span>
                    <span class="badge-category">{{ $post->category->category_name ?? 'Umum' }}</span>
                </div>
                <div class="test-post-body">
                    {!! nl2br(e($post->content)) !!}
                </div>
            </div>
        </div>
        
        {{-- 🔥 INI TUGAS ANDA - SECTION COMMENTS 🔥 --}}
        <div class="comments-section">
            <div class="container">
                <h3 class="comments-title">Comments ({{ $comments->count() }})</h3>
                
                {{-- Form Tambah Komentar --}}
                <div class="comment-form-wrapper">
                    <form action="{{ route('test.comments.store', $post->id_post) }}" method="POST" class="comment-form">
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
                        <button type="submit" class="btn-publish">Publish Comment</button>
                    </form>
                </div>
                
                {{-- Daftar Komentar --}}
                <div class="comments-list">
                    @forelse($comments as $comment)
                        <div class="comment-item">
                            <div class="comment-avatar">
                                <img src="{{ asset('assets/img/avatar.png') }}" alt="Avatar" class="avatar-img">
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
    </div>
</div>

@if(session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif
@endsection