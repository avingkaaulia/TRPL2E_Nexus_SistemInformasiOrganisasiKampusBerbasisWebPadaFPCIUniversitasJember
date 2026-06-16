<?php
// app/Http/Controllers/Admin/PostAdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostGallery;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostAdminController extends Controller
{
    // 🔥 HANYA MENAMPILKAN POST (post_type = 'post') untuk halaman "Semua Postingan"
    public function index(Request $request)
    {
        $query = Post::with(['category', 'user'])->where('post_type', 'post');
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('category') && $request->category != '') {
            $query->where('id_post_category', $request->category);
        }
        
        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        $posts = $query->orderBy('date_published', 'desc')->paginate(15);
        
        $parentWithoutChildren = PostCategory::whereNull('parent_id')
            ->whereNotIn('id_category', function($query) {
                $query->select('parent_id')
                    ->from('post_category')
                    ->whereNotNull('parent_id')
                    ->distinct();
            })
            ->orderBy('category_name')
            ->get();
        
        $subCategories = PostCategory::whereNotNull('parent_id')
            ->orderBy('category_name')
            ->get();
        
        $categories = $parentWithoutChildren->merge($subCategories);
        
        $statuses = ['publish', 'draft', 'pending'];
        
        return view('admin.posts.index', compact('posts', 'categories', 'statuses'));
    }
    
   public function create(Request $request)
{
    $parentWithoutChildren = PostCategory::whereNull('parent_id')
        ->whereNotIn('id_category', function($query) {
            $query->select('parent_id')
                ->from('post_category')
                ->whereNotNull('parent_id')
                ->distinct();
        })
        ->orderBy('category_name')
        ->get();
    
    $subCategories = PostCategory::whereNotNull('parent_id')
        ->orderBy('category_name')
        ->get();
    
    $categories = $parentWithoutChildren->merge($subCategories);
    
    $users = User::all();
    
    // 🔥 CEK PARAMETER TYPE DARI URL
    $type = $request->get('type', 'post'); // default 'post'
    $isPage = ($type === 'page');
    
    return view('admin.posts.create', compact('categories', 'users', 'isPage', 'type'));
}
    
    public function store(Request $request)
    {
        // 🔥 VALIDASI LENGKAP DENGAN PESAN ERROR
        $request->validate([
            'title' => 'required|string|max:150',
            'post_content' => 'required|string|min:10',
            'id_post_category' => 'required|exists:post_category,id_category',
            'post_type' => 'required|in:post,page',
            'status' => 'required|in:publish,draft,pending',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_descriptions.*' => 'nullable|string|max:255'
        ], [
            // Pesan error untuk title
            'title.required' => 'Judul postingan wajib diisi',
            'title.max' => 'Judul postingan maksimal 150 karakter',
            
            // Pesan error untuk post_content
            'post_content.required' => 'Konten postingan wajib diisi',
            'post_content.min' => 'Konten postingan minimal 10 karakter',
            
            // Pesan error untuk kategori
            'id_post_category.required' => 'Kategori wajib dipilih',
            'id_post_category.exists' => 'Kategori yang dipilih tidak valid',
            
            // Pesan error untuk tipe
            'post_type.required' => 'Tipe postingan wajib dipilih',
            'post_type.in' => 'Tipe postingan harus Post atau Page',
            
            // Pesan error untuk status
            'status.required' => 'Status postingan wajib dipilih',
            'status.in' => 'Status postingan tidak valid',
            
            // Pesan error untuk gambar
            'featured_image.image' => 'File harus berupa gambar',
            'featured_image.mimes' => 'Format gambar harus JPG, PNG, atau JPEG',
            'featured_image.max' => 'Ukuran gambar maksimal 4MB',
            
            // Pesan error untuk gallery
            'gallery_images.*.image' => 'File gallery harus berupa gambar',
            'gallery_images.*.mimes' => 'Format gallery harus JPG, PNG, atau JPEG',
            'gallery_images.*.max' => 'Ukuran gallery maksimal 4MB',
            'gallery_descriptions.*.max' => 'Deskripsi gallery maksimal 255 karakter',
        ]);
        
        $data = [
            'title' => $request->title,
            'content' => $request->post_content,
            'id_post_category' => $request->id_post_category,
            'post_type' => $request->post_type,
            'id_user' => auth()->id() ?? 1,
            'date_published' => now(),
            'status' => $request->status,
            'featured_image_path' => null,
        ];
        
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('img', $filename, 'public');
            $data['featured_image_path'] = $path;
        }
        
        $post = Post::create($data);
        
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $key => $file) {
                if ($file) {
                    $filename = time() . '_gallery_' . $key . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('gallery', $filename, 'public');
                    
                    PostGallery::create([
                        'id_post' => $post->id_post,
                        'image_path' => $path,
                        'description' => $request->gallery_descriptions[$key] ?? ''
                    ]);
                }
            }
        }
        
        if ($request->post_type == 'page') {
            return redirect()->route('admin.pages.list')
                ->with('success', 'Halaman berhasil ditambahkan');
        }
        
        return redirect()->route('admin.posts.index')
            ->with('success', 'Postingan berhasil ditambahkan');
    }
    
   public function edit($id)
{
    $post = Post::with('gallery')->findOrFail($id);
    
    $parentWithoutChildren = PostCategory::whereNull('parent_id')
        ->whereNotIn('id_category', function($query) {
            $query->select('parent_id')
                ->from('post_category')
                ->whereNotNull('parent_id')
                ->distinct();
        })
        ->orderBy('category_name')
        ->get();
    
    $subCategories = PostCategory::whereNotNull('parent_id')
        ->orderBy('category_name')
        ->get();
    
    $categories = $parentWithoutChildren->merge($subCategories);
    
    $users = User::all();
    
    // 🔥 KIRIMKAN FLAG IS PAGE
    $isPage = $post->post_type === 'page';
    
    return view('admin.posts.edit', compact('post', 'categories', 'users', 'isPage'));
}
    
    
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        // 🔥 VALIDASI LENGKAP UNTUK UPDATE
        $request->validate([
            'title' => 'required|string|max:150',
            'post_content' => 'required|string|min:10',
            'id_post_category' => 'required|exists:post_category,id_category',
            'post_type' => 'required|in:post,page',
            'status' => 'required|in:publish,draft,pending',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_descriptions.*' => 'nullable|string|max:255'
        ], [
            'title.required' => 'Judul postingan wajib diisi',
            'title.max' => 'Judul postingan maksimal 150 karakter',
            'post_content.required' => 'Konten postingan wajib diisi',
            'post_content.min' => 'Konten postingan minimal 10 karakter',
            'id_post_category.required' => 'Kategori wajib dipilih',
            'id_post_category.exists' => 'Kategori yang dipilih tidak valid',
            'post_type.required' => 'Tipe postingan wajib dipilih',
            'post_type.in' => 'Tipe postingan harus Post atau Page',
            'status.required' => 'Status postingan wajib dipilih',
            'status.in' => 'Status postingan tidak valid',
            'featured_image.image' => 'File harus berupa gambar',
            'featured_image.mimes' => 'Format gambar harus JPG, PNG, atau JPEG',
            'featured_image.max' => 'Ukuran gambar maksimal 4MB',
            'gallery_images.*.image' => 'File gallery harus berupa gambar',
            'gallery_images.*.mimes' => 'Format gallery harus JPG, PNG, atau JPEG',
            'gallery_images.*.max' => 'Ukuran gallery maksimal 4MB',
            'gallery_descriptions.*.max' => 'Deskripsi gallery maksimal 255 karakter',
        ]);
        
        $data = [
            'title' => $request->title,
            'content' => $request->post_content,
            'id_post_category' => $request->id_post_category,
            'post_type' => $request->post_type,
            'status' => $request->status,
        ];
        
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image_path) {
                Storage::disk('public')->delete($post->featured_image_path);
            }
            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('img', $filename, 'public');
            $data['featured_image_path'] = $path;
        }
        
        $post->update($data);
        
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $key => $file) {
                if ($file) {
                    $filename = time() . '_gallery_' . $key . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('gallery', $filename, 'public');
                    
                    PostGallery::create([
                        'id_post' => $post->id_post,
                        'image_path' => $path,
                        'description' => $request->gallery_descriptions[$key] ?? ''
                    ]);
                }
            }
        }
        
        if ($post->post_type == 'page') {
            return redirect()->route('admin.pages.list')
                ->with('success', 'Halaman berhasil diupdate');
        }
        
        return redirect()->route('admin.posts.index')
            ->with('success', 'Postingan berhasil diupdate');
    }
    
    public function deleteGallery($id)
    {
        $gallery = PostGallery::findOrFail($id);
        $postId = $gallery->id_post;
        
        if ($gallery->image_path) {
            Storage::disk('public')->delete($gallery->image_path);
        }
        
        $gallery->delete();
        
        return redirect()->route('admin.posts.edit', $postId)
            ->with('success', 'Gambar gallery berhasil dihapus');
    }
    
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        
        if ($post->featured_image_path) {
            Storage::disk('public')->delete($post->featured_image_path);
        }
        
        foreach ($post->gallery as $gallery) {
            if ($gallery->image_path) {
                Storage::disk('public')->delete($gallery->image_path);
            }
        }
        
        $post->gallery()->delete();
        $post->delete();
        
        if ($post->post_type == 'page') {
            return redirect()->route('admin.pages.list')
                ->with('success', 'Halaman berhasil dihapus');
        }
        
        return redirect()->route('admin.posts.index')
            ->with('success', 'Postingan berhasil dihapus');
    }
}