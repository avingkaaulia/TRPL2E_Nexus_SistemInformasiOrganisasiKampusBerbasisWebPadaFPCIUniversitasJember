<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
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
             // 🔥 LATEST - Semua postingan terbaru dari SEMUA KATEGORI
        // Tampilkan parent category di badge (bukan sub-category)
        $posts = Post::with(['category', 'category.parent'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->orderBy('date_published', 'desc')
            ->take(4)
            ->get();

        // 🔥 KEGIATAN
        $kegiatan = Post::with('category')
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q){
                $q->where('category_name', 'kegiatan');
            })
            ->latest('date_published')
            ->take(4)
            ->get();

        // 🔥 WRITINGS (parent) dan SUB-KATEGORINYA
        $writingsCat = PostCategory::where('category_name', 'writings')->first();
        $writingsIds = [$writingsCat->id_category ?? 4];
        
        // Ambil semua sub-kategori dari writings
        $writingsSubCats = PostCategory::where('parent_id', $writingsCat->id_category ?? 4)->get();
        foreach ($writingsSubCats as $sub) {
            $writingsIds[] = $sub->id_category;
        }
        
        $writings = Post::with('category')
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->whereIn('id_post_category', $writingsIds)
            ->latest('date_published')
            ->take(4)
            ->get();

        // 🔥 URGENT
        $urgent = Post::with('category')
            ->where('status', 'publish')
            ->where('post_type', 'post')
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