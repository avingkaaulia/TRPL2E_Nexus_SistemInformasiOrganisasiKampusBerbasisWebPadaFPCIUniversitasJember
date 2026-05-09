<?php
// app/Http/Controllers/WritingsController.php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WritingsController extends Controller
{
    // Halaman Writings - Dinamis mengambil dari database
    public function index(Request $request)
    {
        try {
        // 🔥 CAROUSEL WRITINGS
        $carousel = Post::where('status', 'publish')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'carousel_writings');
            })
            ->orderBy('date_published', 'desc')
            ->get();
        
        if ($carousel->isEmpty()) {
            $carousel = Post::where('status', 'publish')
                ->whereHas('category', function($q) {
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published', 'desc')
                ->get();
        }
        
        // Proses gambar carousel
        foreach ($carousel as $item) {
            $item->image_url = $this->getImageUrl($item->featured_image_path);
        }
            
            // 🔥 AMBIL KATEGORI WRITINGS (dinamis, cari kategori dengan nama 'writings')
            $writingsCategory = PostCategory::where('category_name', 'writings')->first();
            
            // 🔥 SUB KATEGORI WRITINGS (semua kategori yang parent_id-nya = id writings)
            $subCategories = collect();
            if ($writingsCategory) {
                $subCategories = PostCategory::where('parent_id', $writingsCategory->id_category)->get();
            }
            
            // Jika tidak ada sub kategori, ambil berdasarkan parent_id = 4 (fallback)
            if ($subCategories->isEmpty()) {
                $subCategories = PostCategory::where('parent_id', 4)->get();
            }
            
            // 🔥 QUERY POSTS - HANYA DARI SUB KATEGORI WRITINGS
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post');
            
            if ($subCategories->isNotEmpty()) {
                $query->whereIn('id_post_category', $subCategories->pluck('id_category'));
            }
            
            // Search dinamis
            $search = $request->get('search');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            // Hot topics (6 posts terbaru)
            $hotTopics = (clone $query)->orderBy('date_published', 'desc')->take(6)->get();
            
            // Posts dengan pagination (8 per halaman)
            $posts = $query->orderBy('date_published', 'desc')->paginate(8);
            
            // Filter kategori yang dipilih
            $currentCategory = null;
            if ($request->get('category')) {
                $currentCategory = PostCategory::find($request->get('category'));
            }
            
            return view('writings.index', compact('carousel', 'subCategories', 'hotTopics', 'posts', 'search', 'currentCategory'));
            
        } catch (\Exception $e) {
            Log::error('WritingsController@index error: ' . $e->getMessage());
            return view('writings.index', [
                'carousel' => collect(),
                'subCategories' => collect(),
                'hotTopics' => collect(),
                'posts' => collect()
            ]);
        }
    }
    
    // Filter berdasarkan sub-kategori (dinamis)
    public function category(Request $request, $categoryId)
    {
        try {
            // CAROUSEL
            $carousel = Post::where('status', 'publish')
                ->whereHas('category', function($q) {
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published', 'desc')
                ->get();
            
            // Kategori yang dipilih
            $currentCategory = PostCategory::findOrFail($categoryId);
            
            // Ambil kategori writings
            $writingsCategory = PostCategory::where('category_name', 'writings')->first();
            
            // Sub kategori writings
            $subCategories = collect();
            if ($writingsCategory) {
                $subCategories = PostCategory::where('parent_id', $writingsCategory->id_category)->get();
            }
            if ($subCategories->isEmpty()) {
                $subCategories = PostCategory::where('parent_id', 4)->get();
            }
            
            // Search
            $search = $request->get('search');
            
            // Query posts berdasarkan kategori yang dipilih
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $categoryId);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            $hotTopics = (clone $query)->orderBy('date_published', 'desc')->take(6)->get();
            $posts = $query->orderBy('date_published', 'desc')->paginate(8);
            
            return view('writings.index', compact('carousel', 'subCategories', 'hotTopics', 'posts', 'search', 'currentCategory'));
            
        } catch (\Exception $e) {
            return redirect()->route('writings');
        }
    }
    
    // Detail postingan writings (dinamis)
    public function show($id)
    {
        try {
            $post = Post::with(['category', 'user', 'gallery', 'comments'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->findOrFail($id);
            
            // Related posts berdasarkan kategori yang sama
            $relatedPosts = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $post->id_post_category)
                ->where('id_post', '!=', $id)
                ->take(3)
                ->get();
            
            // Comments
            $comments = Comment::where('id_post', $id)
                ->orderBy('tanggal', 'desc')
                ->get();
            
            return view('show', compact('post', 'relatedPosts', 'comments'));
            
        } catch (\Exception $e) {
            abort(404, 'Post not found');
        }
    }
    // Tambahkan fungsi helper
private function getImageUrl($path)
{
    if (!$path) return asset('assets/img/default-image.jpg');
    
    if (file_exists(storage_path('app/public/' . $path))) {
        return asset('storage/' . $path);
    }
    if (file_exists(public_path($path))) {
        return asset($path);
    }
    if (file_exists(public_path('assets/' . $path))) {
        return asset('assets/' . $path);
    }
    return asset('assets/img/default-image.jpg');
}
}