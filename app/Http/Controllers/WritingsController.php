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
    public function index(Request $request)
    {
        try {
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
            
            foreach ($carousel as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            $writingsCategory = PostCategory::where('category_name', 'writings')->first();
            
            $subCategories = collect();
            if ($writingsCategory) {
                $subCategories = PostCategory::where('parent_id', $writingsCategory->id_category)->get();
            }
            
            if ($subCategories->isEmpty()) {
                $subCategories = PostCategory::where('parent_id', 4)->get();
            }
            
            // 🔥 QUERY UNTUK 8 POSTINGAN TERBARU DI HALAMAN UTAMA
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post');
            
            if ($subCategories->isNotEmpty()) {
                $query->whereIn('id_post_category', $subCategories->pluck('id_category'));
            }
            
            $search = $request->get('search');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            // Hot topics (6 posts terbaru)
            $hotTopics = (clone $query)->orderBy('date_published', 'desc')->take(6)->get();
            
            foreach ($hotTopics as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            // 🔥 AMBIL 8 POSTINGAN TERBARU UNTUK GRID
            $posts = $query->orderBy('date_published', 'desc')->take(8)->get();
            
            foreach ($posts as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
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
    
    // 🔥 HALAMAN ALL WRITINGS (SEMUA POSTINGAN DENGAN PAGINATION)
    public function all(Request $request)
    {
        try {
            $writingsCategory = PostCategory::where('category_name', 'writings')->first();
            
            $subCategories = collect();
            if ($writingsCategory) {
                $subCategories = PostCategory::where('parent_id', $writingsCategory->id_category)->get();
            }
            
            if ($subCategories->isEmpty()) {
                $subCategories = PostCategory::where('parent_id', 4)->get();
            }
            
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->whereIn('id_post_category', $subCategories->pluck('id_category'));
            
            $search = $request->get('search');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            // 🔥 SORTING
            $sort = $request->get('sort', 'terbaru');
            switch ($sort) {
                case 'terlama':
                    $query->orderBy('date_published', 'asc');
                    break;
                case 'az':
                    $query->orderBy('title', 'asc');
                    break;
                case 'za':
                    $query->orderBy('title', 'desc');
                    break;
                case 'terbaru':
                default:
                    $query->orderBy('date_published', 'desc');
                    break;
            }
            // 🔥 PAGINATION UNTUK HALAMAN ALL (9 PER HALAMAN)
            $posts = $query->orderBy('date_published', 'desc')->paginate(9);
            
            foreach ($posts as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            return view('writings.all', compact('posts', 'search', 'sort'));
            
        } catch (\Exception $e) {
            Log::error('WritingsController@all error: ' . $e->getMessage());
            return view('writings.all', ['posts' => collect()]);
        }
    }
    
    public function category(Request $request, $categoryId)
    {
        try {
            $carousel = Post::where('status', 'publish')
                ->whereHas('category', function($q) {
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published', 'desc')
                ->get();
            
            foreach ($carousel as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            $currentCategory = PostCategory::findOrFail($categoryId);
            
            $writingsCategory = PostCategory::where('category_name', 'writings')->first();
            
            $subCategories = collect();
            if ($writingsCategory) {
                $subCategories = PostCategory::where('parent_id', $writingsCategory->id_category)->get();
            }
            if ($subCategories->isEmpty()) {
                $subCategories = PostCategory::where('parent_id', 4)->get();
            }
            
            $search = $request->get('search');
            
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
            
            foreach ($hotTopics as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            // 🔥 DI HALAMAN KATEGORI TAMPILKAN 8 POSTINGAN TERBARU
            $posts = $query->orderBy('date_published', 'desc')->take(8)->get();
            
            foreach ($posts as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            return view('writings.index', compact('carousel', 'subCategories', 'hotTopics', 'posts', 'search', 'currentCategory'));
            
        } catch (\Exception $e) {
            return redirect()->route('writings');
        }
    }
    
    public function show($id)
    {
        try {
            $post = Post::with(['category', 'user', 'gallery', 'comments'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->findOrFail($id);
            
            $post->image_url = getImageUrl($post->featured_image_path);
            
            foreach ($post->gallery as $item) {
                $item->image_url = getImageUrl($item->image_path);
            }
            
            $relatedPosts = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $post->id_post_category)
                ->where('id_post', '!=', $id)
                ->take(3)
                ->get();
            
            foreach ($relatedPosts as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
            
            $comments = Comment::where('id_post', $id)
                ->orderBy('tanggal', 'desc')
                ->get();
            
            return view('show', compact('post', 'relatedPosts', 'comments'));
            
        } catch (\Exception $e) {
            abort(404, 'Post not found');
        }
    }
}