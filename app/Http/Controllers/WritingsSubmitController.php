<?php
// app/Http/Controllers/WritingsSubmitController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostGallery;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WritingsSubmitController extends Controller
{
    public function create()
    {
        // 🔥 AMBIL HANYA SUB KATEGORI DARI WRITINGS (parent_id = 2)
        $writingsCategory = PostCategory::where('category_name', 'writings')->first();
        
        $categories = collect();
        if ($writingsCategory) {
            // Ambil semua sub-kategori dari writings
            $categories = PostCategory::where('parent_id', $writingsCategory->id_category)
                ->orderBy('category_name')
                ->get();
        }
        
        return view('writings.submit', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'post_content' => 'required|string|min:50',
            'id_post_category' => 'required|exists:post_category,id_category',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'gallery_descriptions.*' => 'nullable|string|max:255'
        ], [
            'title.required' => 'Judul karya wajib diisi',
            'title.max' => 'Judul maksimal 150 karakter',
            'post_content.required' => 'Konten karya wajib diisi',
            'post_content.min' => 'Konten karya minimal 50 karakter',
            'id_post_category.required' => 'Kategori wajib dipilih',
            'featured_image.image' => 'File harus berupa gambar',
            'featured_image.max' => 'Ukuran gambar maksimal 4MB',
            'gallery_images.*.image' => 'File gallery harus berupa gambar',
            'gallery_images.*.max' => 'Ukuran gambar gallery maksimal 4MB'
        ]);
        
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk submit karya');
        }
        
        $data = [
            'title' => $request->title,
            'content' => $request->post_content,
            'id_post_category' => $request->id_post_category,
            'post_type' => 'post',
            'id_user' => Auth::id(),
            'date_published' => now(),
            'status' => 'pending',
            'featured_image_path' => null,
        ];
        
        // Upload featured image
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('img/writings', $filename, 'public');
            $data['featured_image_path'] = $path;
        }
        
        $post = Post::create($data);
        
        // 🔥 UPLOAD GALLERY IMAGES
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $key => $file) {
                if ($file) {
                    $filename = time() . '_gallery_' . $key . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('gallery/writings', $filename, 'public');
                    
                    PostGallery::create([
                        'id_post' => $post->id_post,
                        'image_path' => $path,
                        'description' => $request->gallery_descriptions[$key] ?? ''
                    ]);
                }
            }
        }
        
        return redirect()->route('writings')
            ->with('success', 'Karya Anda berhasil dikirim dan menunggu persetujuan admin!');
    }
}