<?php
// app/Http/Controllers/Admin/AdminWritingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostGallery;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

class AdminWritingsController extends Controller
{
    public function pending(Request $request)
    {
        // 🔥 AMBIL SEMUA KATEGORI WRITINGS DAN SUB WRITINGS
        $writingsCategory = PostCategory::where('category_name', 'writings')->first();
        
        $categoryIds = [];
        if ($writingsCategory) {
            // Tambahkan ID writings
            $categoryIds[] = $writingsCategory->id_category;
            // Tambahkan semua sub-kategori writings
            $subCategories = PostCategory::where('parent_id', $writingsCategory->id_category)->get();
            foreach ($subCategories as $sub) {
                $categoryIds[] = $sub->id_category;
            }
        }
        
        $query = Post::with(['category', 'user'])
            ->where('post_type', 'post');
        
        // Filter berdasarkan kategori writings dan subnya
        if (!empty($categoryIds)) {
            $query->whereIn('id_post_category', $categoryIds);
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // Default tampilkan semua status
            $query->whereIn('status', ['pending', 'publish', 'draft']);
        }
        
        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        $pendingPosts = $query->orderBy('date_published', 'desc')->paginate(15);
        
        // 🔥 TAMBAHKAN IMAGE URL UNTUK SETIAP POST
        foreach ($pendingPosts as $post) {
            $post->image_url = getImageUrl($post->featured_image_path);
        }
        
        // Hitung jumlah per status
        $totalPending = Post::whereIn('id_post_category', $categoryIds)
            ->where('status', 'pending')
            ->where('post_type', 'post')
            ->count();
        
        $totalPublished = Post::whereIn('id_post_category', $categoryIds)
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->count();
        
        $statusFilter = $request->get('status', '');
        
        return view('admin.writings.pending', compact('pendingPosts', 'totalPending', 'totalPublished', 'statusFilter'));
    }
    
    public function approve($id)
    {
        $post = Post::findOrFail($id);
        $post->status = 'publish';
        $post->save();
        
        return redirect()->back()->with('success', 'Karya "' . $post->title . '" berhasil disetujui dan dipublikasikan!');
    }
    
    public function reject($id)
    {
        $post = Post::findOrFail($id);
        $title = $post->title;
        
        // Hapus featured image
        if ($post->featured_image_path) {
            Storage::disk('public')->delete($post->featured_image_path);
        }
        
        // Hapus gallery images
        foreach ($post->gallery as $gallery) {
            if ($gallery->image_path) {
                Storage::disk('public')->delete($gallery->image_path);
            }
        }
        
        $post->gallery()->delete();
        $post->delete();
        
        return redirect()->back()->with('success', 'Karya "' . $title . '" berhasil ditolak dan dihapus!');
    }
    
    // 🔥 PERBAIKI: Gunakan view show.blade.php yang sudah ada dengan getImageUrl
    public function show($id)
    {
        $post = Post::with(['category', 'user', 'gallery', 'comments'])
            ->findOrFail($id);
        
        // 🔥 GUNAKAN FUNGSI getImageUrl SEPERTI DI POST CONTROLLER
        $post->image_url = getImageUrl($post->featured_image_path);
        
        foreach ($post->gallery as $item) {
            $item->image_url = getImageUrl($item->image_path);
        }
        
        $relatedPosts = Post::with(['category', 'user'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $post->id_post_category)
            ->where('id_post', '!=', $id)
            ->take(3)
            ->get();
        
        foreach ($relatedPosts as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        $comments = Comment::where('id_post', $id)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Gunakan view show.blade.php yang sudah ada
        return view('show', compact('post', 'relatedPosts', 'comments'));
    }
}