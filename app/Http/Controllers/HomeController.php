<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;
use App\Models\PeriodePendaftaran;
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
        // 🔥 LATEST - Semua postingan terbaru dari semua kategori (kegiatan, writings, pengumuman, dll)
$posts = Post::with('category')
    ->where('status', 'publish')
    ->where('post_type', 'post')
    ->orderBy('date_published', 'desc')
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

        // 🔥 JUDUL SECTION
        $latestTitle = 'Latest Updates';
        $urgentTitle = 'Urgent Notice';


       /// 🔥 CEK APAKAH PENDAFTARAN DIBUKA
        $periodeAktif = PeriodePendaftaran::where('is_active', 1)
            ->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        $isPendaftaranOpen = $periodeAktif ? true : false;
        $pendaftaranInfo = $periodeAktif;
        
        return view('home', compact(
            'carousel', 'about', 'kegiatan', 'writings', 
            'urgent', 'posts', 'latestTitle', 'urgentTitle',
            'isPendaftaranOpen', 'pendaftaranInfo'
        ));
    }
}