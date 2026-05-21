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
    
    public function create()
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
        return view('admin.posts.create', compact('categories', 'users'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'post_content' => 'required|string',
            'id_post_category' => 'required|exists:post_category,id_category',
            'post_type' => 'required|in:post,page',
            'status' => 'required|in:publish,draft,pending',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_descriptions.*' => 'nullable|string|max:255'
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
                $filename = time() . '_gallery_' . $key . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('gallery', $filename, 'public');
                
                PostGallery::create([
                    'id_post' => $post->id_post,
                    'image_path' => $path,
                    'description' => $request->gallery_descriptions[$key] ?? ''
                ]);
            }
        }
        
        // Redirect berdasarkan post_type
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
        return view('admin.posts.edit', compact('post', 'categories', 'users'));
    }
    
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:150',
            'post_content' => 'required|string',
            'id_post_category' => 'required|exists:post_category,id_category',
            'post_type' => 'required|in:post,page',
            'status' => 'required|in:publish,draft,pending',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'gallery_descriptions.*' => 'nullable|string|max:255'
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
                $filename = time() . '_gallery_' . $key . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('gallery', $filename, 'public');
                
                PostGallery::create([
                    'id_post' => $post->id_post,
                    'image_path' => $path,
                    'description' => $request->gallery_descriptions[$key] ?? ''
                ]);
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