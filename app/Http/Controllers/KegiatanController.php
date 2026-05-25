<?php
// app/Http/Controllers/KegiatanController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $carousel = Post::where('status','publish')
            ->whereHas('category', function($q){
                $q->where('category_name', 'carousel_kegiatan');
            })
            ->orderBy('date_published','desc')
            ->get();
        
        // 🔥 GUNAKAN FUNGSI DARI HELPER
        foreach ($carousel as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        if ($carousel->isEmpty()) {
            $carousel = Post::where('status','publish')
                ->whereHas('category', function($q){
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published','desc')
                ->get();
            foreach ($carousel as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
            }
        }
        
        // SUB KATEGORI EVENT (statis)
    $eventReguler  = PostCategory::where('category_name', 'Event Reguler')->first();
    $eventUnggulan = PostCategory::where('category_name', 'Event Unggulan')->first();

    // SUB KATEGORI PROGRAM (statis)
    $programPlanned   = PostCategory::where('category_name', 'Sedang Direncanakan')->first();
    $programOngoing   = PostCategory::where('category_name', 'Sedang Berlangsung')->first();
    $programCompleted = PostCategory::where('category_name', 'Selesai')->first();

    // 🔥 SUB KATEGORI EVENT DINAMIS (tambahan dari admin, parent_id = id event)
    $eventParent = PostCategory::where('category_name', 'event')->first();
    $extraEventCategories = PostCategory::where('parent_id', $eventParent->id_category ?? 6)
        ->whereNotIn('category_name', ['Event Reguler', 'Event Unggulan'])
        ->get();

    // 🔥 SUB KATEGORI PROGRAM DINAMIS (tambahan dari admin, parent_id = id program)
    $programParent = PostCategory::where('category_name', 'program')->first();
    $extraProgramCategories = PostCategory::where('parent_id', $programParent->id_category ?? 7)
        ->whereNotIn('category_name', ['Sedang Direncanakan', 'Sedang Berlangsung', 'Selesai'])
        ->get();

    // Ambil posts untuk extra event categories
    $extraEventPosts = [];
    foreach ($extraEventCategories as $cat) {
        $posts = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $cat->id_category)
            ->latest('date_published')
            ->take(3)
            ->get();
        foreach ($posts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        $extraEventPosts[$cat->id_category] = [
            'category' => $cat,
            'posts'    => $posts,
        ];
    }

    // Ambil posts untuk extra program categories
    $extraProgramPosts = [];
    foreach ($extraProgramCategories as $cat) {
        $posts = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $cat->id_category)
            ->latest('date_published')
            ->take(4)
            ->get();
        foreach ($posts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        $extraProgramPosts[$cat->id_category] = [
            'category' => $cat,
            'posts'    => $posts,
        ];
    }
        
        // 🔥 EVENT REGULER - 3 event terbaru
        $eventRegulerPosts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $eventReguler->id_category ?? 0)
            ->latest('date_published')
            ->take(3)
            ->get();
        
        foreach ($eventRegulerPosts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        // 🔥 EVENT UNGGULAN - 3 event terbaru
        $eventUnggulanPosts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $eventUnggulan->id_category ?? 0)
            ->latest('date_published')
            ->take(3)
            ->get();
        
        foreach ($eventUnggulanPosts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        // 🔥 PROGRAM SEDANG DIRENCANAKAN
        $programsPlanned = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programPlanned->id_category ?? 0)
            ->latest('date_published')
            ->take(4)
            ->get();
        
        foreach ($programsPlanned as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        // 🔥 PROGRAM SEDANG BERLANGSUNG
        $programsOngoing = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programOngoing->id_category ?? 0)
            ->latest('date_published')
            ->take(4)
            ->get();
        
        foreach ($programsOngoing as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        // 🔥 PROGRAM SELESAI
        $programsCompleted = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programCompleted->id_category ?? 0)
            ->latest('date_published')
            ->take(4)
            ->get();
        
        foreach ($programsCompleted as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        $currentSlide = $request->get('slide', 0);
        
        return view('kegiatan.index', compact(
            'carousel',
        'eventRegulerPosts',
        'eventUnggulanPosts',
        'programsPlanned',
        'programsOngoing',
        'programsCompleted',
        'extraEventPosts',
        'extraProgramPosts',
        'currentSlide'
        ));
    }
    
    // Halaman all Event Reguler dengan sorting
public function allEventReguler(Request $request)
{
    $eventReguler = PostCategory::where('category_name', 'Event Reguler')->first();
    
    $query = Post::with(['category', 'gallery'])
        ->where('status', 'publish')
        ->where('post_type', 'post')
        ->where('id_post_category', $eventReguler->id_category ?? 0);
    
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
    
    $posts = $query->paginate(9);
    
    foreach ($posts as $item) {
        $item->image_url = getImageUrl($item->featured_image_path);
    }
    
    return view('kegiatan.all', compact('posts', 'sort'));
}

// Halaman all Event Unggulan dengan sorting
public function allEventUnggulan(Request $request)
{
    $eventUnggulan = PostCategory::where('category_name', 'Event Unggulan')->first();
    
    $query = Post::with(['category', 'gallery'])
        ->where('status', 'publish')
        ->where('post_type', 'post')
        ->where('id_post_category', $eventUnggulan->id_category ?? 0);
    
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
    
    $posts = $query->paginate(9);
    
    foreach ($posts as $item) {
        $item->image_url = getImageUrl($item->featured_image_path);
    }
    
    return view('kegiatan.all', compact('posts', 'sort'));
}

// 🔥 PERBAIKI: Halaman all Programs by ID kategori (dinamis)
    public function allPrograms(Request $request, $categoryId)
    {
        // Cari kategori berdasarkan ID
        $category = PostCategory::findOrFail($categoryId);
        
        $query = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $categoryId);
        
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
        
        $posts = $query->paginate(9);
        
        foreach ($posts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        return view('kegiatan.all', compact('posts', 'sort', 'category'));
    }
    
    // Detail Event/Program
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