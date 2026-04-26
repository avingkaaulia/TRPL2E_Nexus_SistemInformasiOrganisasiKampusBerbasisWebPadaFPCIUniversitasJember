<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        // 🔥 CAROUSEL
        $carousel = Post::where('status','publish')
            ->whereHas('category', function($q){
                $q->where('category_name','carousel');
            })
            ->orderBy('date_published','desc')
            ->get();

        // 🔥 ABOUT
        $about = Post::where('status','publish')
            ->whereHas('category', function($q){
                $q->where('category_name','about');
            })
            ->first();
        // 🔥 LATEST (gabungan semua kategori)
        $posts = Post::with('category')
            ->where('status','publish')
            ->whereHas('category', function($q){
                $q->whereIn('category_name', ['kegiatan','writings','pengumuman']);
            })
            ->orderBy('date_published','desc')
            ->take(4)
            ->get();

        // 🔥 KEGIATAN
        $kegiatan = Post::with('category')
            ->whereHas('category', function($q){
                $q->where('category_name','kegiatan');
            })
            ->latest('date_published')
            ->take(4)
            ->get();

        // 🔥 WRITINGS
        $writings = Post::with('category')
            ->whereHas('category', function($q){
                $q->where('category_name','writings');
            })
            ->latest('date_published')
            ->take(4)
            ->get();

        // 🔥 URGENT
        $urgent = Post::with('category')
            ->whereHas('category', function($q){
                $q->where('category_name','urgent');
            })
            ->latest('date_published')
            ->take(2)
            ->get();

       return view('home', compact(
    'carousel',
    'about',
    'kegiatan',
    'writings',
    'urgent',
    'posts' // 🔥 INI YANG KURANG
));
    }
}
