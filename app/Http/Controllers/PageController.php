<?php
// app/Http/Controllers/PageController.php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($id)
    {
        $page = Post::with(['user', 'category', 'gallery'])
            ->where('post_type', 'page')
            ->where('status', 'publish')
            ->findOrFail($id);
        
        // Set image URL menggunakan helper
        $page->image_url = getImageUrl($page->featured_image_path);
        
        // Set image URL untuk gallery
        foreach ($page->gallery as $item) {
            $item->image_url = getImageUrl($item->image_path);
        }
        
        // ← TAMBAHKAN INI: Ambil post dengan kategori SAMA dengan page ini
        $postsInPage = Post::with(['user', 'category'])
            ->where('id_post_category', $page->id_post_category)
            ->where('post_type', 'post')       // hanya post, bukan page
            ->where('status', 'publish')       // hanya yang publish
            ->where('id_post', '!=', $page->id_post)  // jangan tampilkan page-nya sendiri
            ->orderBy('date_published', 'desc')
            ->get();
        
        // Set image URL untuk post terkait
        foreach ($postsInPage as $relatedPost) {
            $relatedPost->image_url = getImageUrl($relatedPost->featured_image_path);
        }
        
        return view('page.show', compact('page', 'postsInPage'));
    }
    
    public function all()
    {
        $pages = Post::where('post_type', 'page')
            ->where('status', 'publish')
            ->orderBy('title', 'asc')
            ->paginate(12);
        
        return view('pages.index', compact('pages'));
    }
}