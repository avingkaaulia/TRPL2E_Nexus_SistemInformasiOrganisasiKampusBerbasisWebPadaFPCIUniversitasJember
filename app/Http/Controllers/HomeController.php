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
        // 🔥 CAROUSEL HOME - khusus kategori carousel_home
        $carousel = Post::where('status','publish')
            ->whereHas('category', function($q){
                $q->where('category_name', 'carousel_home');
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

        // 🔥 ABOUT
        $about = Post::where('status','publish')
            ->whereHas('category', function($q){
                $q->where('category_name','about');
            })
            ->first();
        
        // 🔥 LATEST UPDATES - 1 POSTINGAN TERBARU PER KATEGORI
        // Fungsi helper untuk mendapatkan ID kategori termasuk sub-kategori
        $getCategoryIds = function($categoryName) {
            $category = PostCategory::where('category_name', $categoryName)->first();
            if (!$category) return [];
            
            $ids = [$category->id_category];
            $subCats = PostCategory::where('parent_id', $category->id_category)->get();
            foreach ($subCats as $sub) {
                $ids[] = $sub->id_category;
            }
            return $ids;
        };
        
        // Kategori yang akan ditampilkan (urutan tetap: writings, pengumuman, event, program)
        $categories = ['writings', 'pengumuman', 'event', 'program'];
        
        $posts = collect();
        foreach ($categories as $catName) {
            $categoryIds = $getCategoryIds($catName);
            if (!empty($categoryIds)) {
                $latestPost = Post::with(['category', 'category.parent'])
                    ->where('status', 'publish')
                    ->where('post_type', 'post')
                    ->whereIn('id_post_category', $categoryIds)
                    ->orderBy('date_published', 'desc')
                    ->first();
                
                if ($latestPost) {
                    $latestPost->image_url = getImageUrl($latestPost->featured_image_path);
                    $posts->push($latestPost);
                }
            }
        }

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

            foreach ($kegiatan as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
            }

        // 🔥 WRITINGS (parent) dan SUB-KATEGORINYA
        $writingsCat = PostCategory::where('category_name', 'writings')->first();
        $writingsIds = [$writingsCat->id_category ?? 2];
        
        $writingsSubCats = PostCategory::where('parent_id', $writingsCat->id_category ?? 2)->get();
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

            foreach ($writings as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
            }

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

            foreach ($urgent as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }

        $latestTitle = 'Latest Updates';
        $urgentTitle = 'Urgent Notice';

        /// 🔥 CEK PENDAFTARAN
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