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
        
        // 🔥 SUB KATEGORI EVENT
        $eventReguler = PostCategory::where('category_name', 'Event Reguler')->first();
        $eventUnggulan = PostCategory::where('category_name', 'Event Unggulan')->first();
        
        // 🔥 SUB KATEGORI PROGRAM
        $programPlanned = PostCategory::where('category_name', 'Sedang Direncanakan')->first();
        $programOngoing = PostCategory::where('category_name', 'Sedang Berlangsung')->first();
        $programCompleted = PostCategory::where('category_name', 'Selesai')->first();
        
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
            'currentSlide'
        ));
    }
    
    // Halaman all Event Reguler
    public function allEventReguler()
    {
        $eventReguler = PostCategory::where('category_name', 'Event Reguler')->first();
        
        $posts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $eventReguler->id_category ?? 0)
            ->latest('date_published')
            ->paginate(9);
        
        foreach ($posts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        return view('kegiatan.all', compact('posts'));
    }
    
    // Halaman all Event Unggulan
    public function allEventUnggulan()
    {
        $eventUnggulan = PostCategory::where('category_name', 'Event Unggulan')->first();
        
        $posts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $eventUnggulan->id_category ?? 0)
            ->latest('date_published')
            ->paginate(9);
        
        foreach ($posts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        return view('kegiatan.all', compact('posts'));
    }
    
    // Halaman all Programs by status
    public function allPrograms($status)
    {
        $statusMap = [
            'planned' => 'Sedang Direncanakan',
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Selesai'
        ];
        
        $categoryName = $statusMap[$status] ?? 'Sedang Direncanakan';
        $programCategory = PostCategory::where('category_name', $categoryName)->first();
        
        $posts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programCategory->id_category ?? 0)
            ->latest('date_published')
            ->paginate(9);
        
        foreach ($posts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        return view('kegiatan.all', compact('posts'));
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