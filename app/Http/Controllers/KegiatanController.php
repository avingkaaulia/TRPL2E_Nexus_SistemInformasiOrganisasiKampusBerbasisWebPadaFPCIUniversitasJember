<?php
// app/Http/Controllers/KegiatanController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;

class KegiatanController extends Controller
{
    // Halaman utama Event & Program
    public function index(Request $request)
    {
        // 🔥 CAROUSEL KEGIATAN - khusus kategori carousel_kegiatan
    // 🔥 CAROUSEL HOME - khusus kategori carousel_home
        $carousel = Post::where('status','publish')
            ->whereHas('category', function($q){
                $q->where('category_name', 'carousel_kegiatan');
            })
            ->orderBy('date_published','desc')
            ->get();
        
        // Proses gambar carousel agar bisa ditampilkan
        foreach ($carousel as $item) {
            $item->image_url = $this->getImageUrl($item->featured_image_path);
        }
        
        // Jika tidak ada carousel khusus home, ambil dari carousel umum
        if ($carousel->isEmpty()) {
            $carousel = Post::where('status','publish')
                ->whereHas('category', function($q){
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published','desc')
                ->get();
            foreach ($carousel as $item) {
                $item->image_url = $this->getImageUrl($item->featured_image_path);
            }
        }
        
        // 🔥 AMBIL KATEGORI EVENT DAN PROGRAM
        $eventCategory = PostCategory::where('category_name', 'event')->first();
        $programCategory = PostCategory::where('category_name', 'program')->first();
        
        // 🔥 SUB KATEGORI EVENT (Event Reguler & Event Unggulan)
        $eventReguler = PostCategory::where('category_name', 'Event Reguler')->first();
        $eventUnggulan = PostCategory::where('category_name', 'Event Unggulan')->first();
        
        // 🔥 SUB KATEGORI PROGRAM (status)
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
        
        // 🔥 EVENT UNGGULAN - 3 event terbaru
        $eventUnggulanPosts = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $eventUnggulan->id_category ?? 0)
            ->latest('date_published')
            ->take(3)
            ->get();
        
        // 🔥 PROGRAM SEDANG DIRENCANAKAN
        $programsPlanned = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programPlanned->id_category ?? 0)
            ->latest('date_published')
            ->take(4)
            ->get();
        
        // 🔥 PROGRAM SEDANG BERLANGSUNG
        $programsOngoing = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programOngoing->id_category ?? 0)
            ->latest('date_published')
            ->take(4)
            ->get();
        
        // 🔥 PROGRAM SELESAI
        $programsCompleted = Post::with(['category', 'gallery'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $programCompleted->id_category ?? 0)
            ->latest('date_published')
            ->take(4)
            ->get();
        
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
            
            $relatedPosts = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $post->id_post_category)
                ->where('id_post', '!=', $id)
                ->take(3)
                ->get();
            
            $comments = Comment::where('id_post', $id)
                ->orderBy('tanggal', 'desc')
                ->get();
            
            return view('show', compact('post', 'relatedPosts', 'comments'));
            
        } catch (\Exception $e) {
            abort(404, 'Post not found');
        }
    }
    // Fungsi helper untuk mendapatkan URL gambar
    private function getImageUrl($path)
    {
        if (!$path) {
            return asset('assets/img/default-image.jpg');
        }
        
        // Cek di storage
        $storagePath = storage_path('app/public/' . $path);
        if (file_exists($storagePath)) {
            return asset('storage/' . $path);
        }
        
        // Cek di public
        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            return asset($path);
        }
        
        // Cek di public/assets/img
        $assetsPath = public_path('assets/' . $path);
        if (file_exists($assetsPath)) {
            return asset('assets/' . $path);
        }
        
        return asset('assets/img/default-image.jpg');
    }
}