<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;
use App\Models\Anggota;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // 🔥 AMBIL SEMUA ID KATEGORI WRITINGS DAN SUB KATEGORINYA (menggunakan query builder)
        $writingsCategory = DB::table('post_category')->where('category_name', 'writings')->first();
        $writingsCategoryIds = [];
        
        if ($writingsCategory) {
            // Tambahkan ID kategori writings
            $writingsCategoryIds[] = $writingsCategory->id_category;
            
            // Ambil semua sub-kategori dari writings
            $subCategories = DB::table('post_category')->where('parent_id', $writingsCategory->id_category)->get();
            foreach ($subCategories as $sub) {
                $writingsCategoryIds[] = $sub->id_category;
            }
        }
        
        // Post stats (hanya post_type = 'post')
        $totalPosts = DB::table('posts')->where('post_type', 'post')->count();
        
        // 🔥 TOTAL KARYA - AMBIL DARI KATEGORI WRITINGS DAN SUB KATEGORINYA
        if (!empty($writingsCategoryIds)) {
            $totalKarya = DB::table('posts')
                ->where('post_type', 'post')
                ->whereIn('id_post_category', $writingsCategoryIds)
                ->count();
        } else {
            $totalKarya = 0;
        }
        
        // Anggota stats
        $totalAnggota = DB::table('anggota')->count();
        
        // Comment stats
        $totalComments = DB::table('comments')->count();
        
        // Recent data
        $recentPosts = DB::table('posts')
            ->orderBy('date_published', 'desc')
            ->take(5)
            ->get();
        
        $recentComments = DB::table('comments')
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->get();
        
        // Category stats
        $categoryStats = DB::table('post_category')
            ->leftJoin('posts', 'post_category.id_category', '=', 'posts.id_post_category')
            ->select('post_category.id_category', 'post_category.category_name', DB::raw('COUNT(posts.id_post) as posts_count'))
            ->where('posts.post_type', 'post')
            ->groupBy('post_category.id_category', 'post_category.category_name')
            ->having('posts_count', '>', 0)
            ->orderBy('posts_count', 'desc')
            ->take(5)
            ->get();
        
        // Pendaftaran stats
        $totalPendaftaran = DB::table('pendaftaran')->count();
        $pendingPendaftaran = DB::table('pendaftaran')->where('status', 'menunggu')->count();
        
        // User stats
        $totalAdmin = DB::table('users')->where('id_role', 1)->count();
        $totalAnggotaUser = DB::table('users')->where('id_role', 2)->count();
        
        return view('admin.dashboard', compact(
            'totalPosts', 'totalKarya', 'totalAnggota', 'totalComments',
            'recentPosts', 'recentComments', 'categoryStats',
            'totalPendaftaran', 'pendingPendaftaran',
            'totalAdmin', 'totalAnggotaUser'
        ));
    }
}