<?php
// app/Http/Controllers/PageController.php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // Menampilkan halaman page berdasarkan ID atau slug
    public function show($id)
    {
        $page = Post::where('post_type', 'page')
            ->where('status', 'publish')
            ->where('id_post', $id)
            ->firstOrFail();
        
        $page->image_url = getImageUrl($page->featured_image_path);
        
        return view('page.show', compact('page'));
    }
    
    // Menampilkan semua page (opsional)
    public function all()
    {
        $pages = Post::where('post_type', 'page')
            ->where('status', 'publish')
            ->orderBy('title')
            ->get();
        
        return view('page.index', compact('pages'));
    }
}