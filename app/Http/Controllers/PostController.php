<?php
// app/Http/Controllers/PostController.php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    // 🔥 DETAIL POSTINGAN UNTUK SEMUA KATEGORI
    public function show($id)
    {
        try {
            $post = Post::with(['category', 'user', 'gallery', 'comments'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->findOrFail($id);
            
            // Related posts based on category
            $relatedPosts = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $post->id_post_category)
                ->where('id_post', '!=', $id)
                ->take(3)
                ->get();
            
            // Comments untuk post ini
            $comments = Comment::where('id_post', $id)
                ->orderBy('tanggal', 'desc')
                ->get();
            
            // Pastikan view show.blade.php ada di root resources/views
            return view('show', compact('post', 'relatedPosts', 'comments'));
            
        } catch (\Exception $e) {
            Log::error('PostController@show error: ' . $e->getMessage());
            abort(404, 'Post not found');
        }
    }
}