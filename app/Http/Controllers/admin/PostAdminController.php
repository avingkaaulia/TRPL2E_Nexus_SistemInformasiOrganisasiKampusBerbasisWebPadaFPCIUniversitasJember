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
    public function index(Request $request)
    {
        $query = Post::with(['category', 'user']);
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('category') && $request->category != '') {
            $query->where('id_post_category', $request->category);
        }
        
        if ($request->has('post_type') && $request->post_type != '') {
            $query->where('post_type', $request->post_type);
        }
        
        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        $posts = $query->orderBy('date_published', 'desc')->paginate(15);
        $categories = PostCategory::all();
        $statuses = ['publish', 'draft', 'pending'];
        $postTypes = ['post', 'page'];
        
        return view('admin.posts.index', compact('posts', 'categories', 'statuses', 'postTypes'));
    }
    
    public function create()
    {
        $categories = PostCategory::all();
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
        ];
        
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('img', $filename, 'public');
            $data['featured_image_path'] = $path;
        }
        
        $post = Post::create($data);
        
        // Upload gallery images
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
        
        return redirect()->route('admin.posts.index')
            ->with('success', 'Postingan berhasil ditambahkan');
    }
    
    public function edit($id)
    {
        $post = Post::with('gallery')->findOrFail($id);
        $categories = PostCategory::all();
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
        
        // Upload new gallery images
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
        
        return redirect()->route('admin.posts.index')
            ->with('success', 'Postingan berhasil diupdate');
    }
    
    // Hapus gallery image
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
        
        // Delete gallery images
        foreach ($post->gallery as $gallery) {
            if ($gallery->image_path) {
                Storage::disk('public')->delete($gallery->image_path);
            }
        }
        
        $post->gallery()->delete();
        $post->delete();
        
        return redirect()->route('admin.posts.index')
            ->with('success', 'Postingan berhasil dihapus');
    }
}