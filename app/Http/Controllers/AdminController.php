<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Comment;
use App\Models\Anggota;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Statistik sederhana
        $totalPosts = Post::count();
        $totalKarya = Post::where('id_post_category', 2)->count();
        $totalAnggota = Anggota::count();
        $totalComments = Comment::count();
        
        // Data terbaru
        $recentPosts = Post::orderBy('date_published', 'desc')->limit(5)->get();
        $recentComments = Comment::orderBy('tanggal', 'desc')->limit(5)->get();
        
        // 🔥 PAKAI QUERY MANUAL (PALING AMAN)
        $categoryStats = DB::table('post_category')
            ->leftJoin('posts', 'post_category.id_category', '=', 'posts.id_post_category')
            ->select('post_category.id_category', 'post_category.category_name', DB::raw('COUNT(posts.id_post) as posts_count'))
            ->groupBy('post_category.id_category', 'post_category.category_name')
            ->get();
        
        // Pendaftaran
        $totalPendaftaran = Pendaftaran::count();
        $pendingPendaftaran = Pendaftaran::where('status', 'menunggu')->count();
        
        return view('admin.dashboard', compact(
            'totalPosts', 'totalKarya', 'totalAnggota', 'totalComments',
            'recentPosts', 'recentComments', 'categoryStats',
            'totalPendaftaran', 'pendingPendaftaran'
        ));
    }
}