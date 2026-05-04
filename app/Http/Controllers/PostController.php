<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    // Halaman Writings
    public function index(Request $request)
    {
        try {
            // Ambil semua kategori
            $categories = PostCategory::all();
            
            // Ambil keyword search
            $search = $request->get('search');
            
            // Query posts - TAMPILKAN SEMUA POST DARI KATEGORI WRITINGS DAN KATEGORI LAINNYA
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post');
            
            // Filter search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            // Hot topics (6 posts terbaru dari semua kategori)
            $hotTopics = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->orderBy('date_published', 'desc')
                ->take(6)
                ->get();
            
            // Posts dengan pagination (8 per halaman) - TAMPILKAN SEMUA
            $posts = $query->orderBy('date_published', 'desc')->paginate(8);
            
            return view('writings.index', compact('categories', 'hotTopics', 'posts', 'search'));
            
        } catch (\Exception $e) {
            Log::error('PostController@index error: ' . $e->getMessage());
            return view('writings.index', [
                'categories' => collect(),
                'hotTopics' => collect(),
                'posts' => collect()
            ]);
        }
    }
    
    // 🔥 FILTER BY CATEGORY (TAMBAHKAN METHOD INI)
    public function category(Request $request, $categoryId)
    {
        try {
            // Ambil semua kategori
            $categories = PostCategory::all();
            
            // Ambil kategori yang dipilih
            $currentCategory = PostCategory::findOrFail($categoryId);
            
            // Ambil keyword search
            $search = $request->get('search');
            
            // Query posts berdasarkan kategori
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $categoryId);
            
            // Filter search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            // Hot topics (6 posts terbaru dari kategori yang sama)
            $hotTopics = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $categoryId)
                ->orderBy('date_published', 'desc')
                ->take(6)
                ->get();
            
            // Posts dengan pagination (8 per halaman)
            $posts = $query->orderBy('date_published', 'desc')->paginate(8);
            
            return view('writings.index', compact('categories', 'hotTopics', 'posts', 'search', 'currentCategory'));
            
        } catch (\Exception $e) {
            Log::error('PostController@category error: ' . $e->getMessage());
            return redirect()->route('writings')->with('error', 'Category not found');
        }
    }
    
    // Detail post
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
            
            return view('writings.show', compact('post', 'relatedPosts', 'comments'));
            
        } catch (\Exception $e) {
            Log::error('PostController@show error: ' . $e->getMessage());
            abort(404, 'Post not found');
        }
    }
}