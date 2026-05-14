<?php
// app/Http/Controllers/SearchController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Anggota;
use App\Models\Kegiatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // all, posts, writings, kegiatan, about
        
        if (!$query) {
            return redirect()->back()->with('error', 'Masukkan kata kunci pencarian');
        }
        
        $results = [];
        $counts = [
            'posts' => 0,
            'writings' => 0,
            'kegiatan' => 0,
            'about' => 0
        ];
        
        // 1. PENCARIAN DI POSTS (SEMUA POST)
        if ($type == 'all' || $type == 'posts') {
            $posts = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('content', 'LIKE', '%' . $query . '%')
                      ->orWhere('excerpt', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('date_published', 'desc')
                ->take(20)
                ->get();
            
            foreach ($posts as $post) {
                $post->image_url = getImageUrl($post->featured_image_path);
                $post->search_type = 'posts';
            }
            
            $results['posts'] = $posts;
            $counts['posts'] = $posts->count();
        }
        
        // 2. PENCARIAN DI WRITINGS (HANYA KATEGORI WRITINGS)
        if ($type == 'all' || $type == 'writings') {
            $writingsCategory = PostCategory::where('category_name', 'writings')->first();
            $writingsIds = [$writingsCategory->id_category ?? 0];
            
            $subCats = PostCategory::where('parent_id', $writingsCategory->id_category ?? 0)->get();
            foreach ($subCats as $sub) {
                $writingsIds[] = $sub->id_category;
            }
            
            $writings = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->whereIn('id_post_category', $writingsIds)
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('content', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('date_published', 'desc')
                ->take(20)
                ->get();
            
            foreach ($writings as $writing) {
                $writing->image_url = getImageUrl($writing->featured_image_path);
                $writing->search_type = 'writings';
            }
            
            $results['writings'] = $writings;
            $counts['writings'] = $writings->count();
        }
        
        // 3. PENCARIAN DI KEGIATAN
        if ($type == 'all' || $type == 'kegiatan') {
            $kegiatanCategory = PostCategory::where('category_name', 'kegiatan')->first();
            
            $kegiatan = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $kegiatanCategory->id_category ?? 0)
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('content', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('date_published', 'desc')
                ->take(20)
                ->get();
            
            foreach ($kegiatan as $item) {
                $item->image_url = getImageUrl($item->featured_image_path);
                $item->search_type = 'kegiatan';
            }
            
            $results['kegiatan'] = $kegiatan;
            $counts['kegiatan'] = $kegiatan->count();
        }
        
        // 4. PENCARIAN DI ABOUT (ANGGOTA)
        if ($type == 'all' || $type == 'about') {
            $anggota = \App\Models\Anggota::with(['divisi', 'user'])
                ->where(function($q) use ($query) {
                    $q->where('nama_lengkap', 'LIKE', '%' . $query . '%')
                      ->orWhere('jabatan', 'LIKE', '%' . $query . '%')
                      ->orWhere('bio', 'LIKE', '%' . $query . '%');
                })
                ->take(10)
                ->get();
            
            foreach ($anggota as $item) {
                $item->search_type = 'about';
                $item->title = $item->nama_lengkap;
                $item->excerpt = $item->jabatan . ' - ' . ($item->bio ?? '');
                $item->link = '/about#' . $item->id_anggota;
                $item->image_url = $item->foto ? getImageUrl($item->foto) : asset('assets/img/default-avatar.jpg');
            }
            
            $results['about'] = $anggota;
            $counts['about'] = $anggota->count();
        }
        
        $totalResults = array_sum($counts);
        
        return view('search.results', compact('results', 'counts', 'query', 'type', 'totalResults'));
    }
    
    public function autocomplete(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = [];
        
        // Search titles
        $posts = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->where('title', 'LIKE', '%' . $query . '%')
            ->select('id_post', 'title', 'slug')
            ->take(5)
            ->get();
        
        foreach ($posts as $post) {
            $suggestions[] = [
                'title' => $post->title,
                'url' => '/post/' . $post->id_post,
                'type' => 'Postingan'
            ];
        }
        
        // Search anggota
        $anggota = \App\Models\Anggota::where('nama_lengkap', 'LIKE', '%' . $query . '%')
            ->select('id_anggota', 'nama_lengkap')
            ->take(3)
            ->get();
        
        foreach ($anggota as $a) {
            $suggestions[] = [
                'title' => $a->nama_lengkap,
                'url' => '/about#' . $a->id_anggota,
                'type' => 'Anggota'
            ];
        }
        
        return response()->json($suggestions);
    }
}