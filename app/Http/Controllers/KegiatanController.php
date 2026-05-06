<?php
// app/Http/Controllers/KegiatanController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;

class KegiatanController extends Controller
{
    // Halaman utama kegiatan
    public function index(Request $request)
    {
        // 🔥 CAROUSEL
        $carousel = Post::where('status', 'publish')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'carousel');
            })
            ->orderBy('date_published', 'desc')
            ->get();
        
        // 🔥 LATEST POSTS - 3 post terbaru dari kategori kegiatan
        $latest = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'kegiatan');
            })
            ->latest('date_published')
            ->take(3)
            ->get();
        
        // 🔥 OTHER POSTS - 6 post lainnya
        $others = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'kegiatan');
            })
            ->latest('date_published')
            ->skip(3)
            ->take(6)
            ->get();
        
        // 🔥 FEATURED PROGRAMS
        $featured = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'kegiatan');
            })
            ->inRandomOrder()
            ->take(3)
            ->get();
        
        $categories = PostCategory::all();
        $currentSlide = $request->get('slide', 0);
        
        return view('kegiatan.index', compact(
            'carousel', 'latest', 'others', 'featured', 
            'categories', 'currentSlide'
        ));
    }
    
    // Halaman all kegiatan (pagination)
    public function all()
    {
        $posts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'kegiatan');
            })
            ->latest('date_published')
            ->paginate(9);
        
        return view('kegiatan.all', compact('posts'));
    }
    
    // 🔥 DETAIL KEGIATAN - Menggunakan show.blade.php (SAMA DENGAN WRITINGS)
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
            
            // 🔥 PAKAI SHOW.BLADE.PHP YANG SAMA DENGAN WRITINGS
            return view('show', compact('post', 'relatedPosts', 'comments'));
            
        } catch (\Exception $e) {
            abort(404, 'Post not found');
        }
    }
}