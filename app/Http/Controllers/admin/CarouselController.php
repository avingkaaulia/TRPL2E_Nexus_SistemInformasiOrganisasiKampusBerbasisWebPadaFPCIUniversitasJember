<?php
// app/Http/Controllers/Admin/CarouselController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarouselController extends Controller
{
    public function index()
    {
        $carouselCategories = PostCategory::whereIn('category_name', [
            'carousel_home', 'carousel_about', 'carousel_writings', 
            'carousel_kegiatan', 'carousel_contact'
        ])->get();
        
        $carouselsByCategory = [];
        foreach ($carouselCategories as $cat) {
            $carousels = Post::where('id_post_category', $cat->id_category)
                ->orderBy('date_published', 'desc')
                ->get();
            
            foreach ($carousels as $slide) {
                $imagePath = storage_path('app/public/' . $slide->featured_image_path);
                $slide->image_exists = ($slide->featured_image_path && file_exists($imagePath));
            }
            
            $carouselsByCategory[$cat->id_category] = $carousels;
        }
        
        return view('admin.carousel.index', compact('carouselCategories', 'carouselsByCategory'));
    }
    
    public function create($categoryId)
    {
        $category = PostCategory::findOrFail($categoryId);
        return view('admin.carousel.create', compact('category'));
    }
    
    public function store(Request $request, $categoryId)
    {
        // Validasi dengan pesan kustom
        $rules = [
            'title' => 'required|string|max:150',
            'description' => 'required|string|min:10|max:500',
            'featured_image' => 'required|image|mimes:jpeg,png,jpg|max:4000',
            'status' => 'required|in:publish,draft'
        ];
        
        $messages = [
            'title.required' => '⚠️ Judul slide wajib diisi',
            'title.max' => '⚠️ Judul slide maksimal 150 karakter',
            'description.required' => '⚠️ Deskripsi slide wajib diisi',
            'description.min' => '⚠️ Deskripsi minimal 10 karakter',
            'description.max' => '⚠️ Deskripsi maksimal 500 karakter',
            'featured_image.required' => '⚠️ Gambar wajib diupload',
            'featured_image.image' => '⚠️ File harus berupa gambar',
            'featured_image.mimes' => '⚠️ Format gambar harus JPG atau PNG',
            'featured_image.max' => '⚠️ Ukuran gambar maksimal 4MB',
            'status.required' => '⚠️ Status wajib dipilih'
        ];
        
        $request->validate($rules, $messages);
        
        $file = $request->file('featured_image');
        $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('img', $filename, 'public');
        
        Post::create([
            'title' => $request->title,
            'content' => $request->description,
            'id_post_category' => $categoryId,
            'post_type' => 'post',
            'id_user' => auth()->id() ?? 1,
            'date_published' => now(),
            'status' => $request->status,
            'featured_image_path' => $path
        ]);
        
        return redirect()->route('admin.carousel')
            ->with('success', '✅ Slide carousel berhasil ditambahkan');
    }
    
    public function edit($id)
    {
        $carousel = Post::findOrFail($id);
        $category = PostCategory::find($carousel->id_post_category);
        
        $imagePath = storage_path('app/public/' . $carousel->featured_image_path);
        $carousel->image_exists = ($carousel->featured_image_path && file_exists($imagePath));
        
        return view('admin.carousel.edit', compact('carousel', 'category'));
    }
    
    public function update(Request $request, $id)
    {
        $carousel = Post::findOrFail($id);
        
        $rules = [
            'title' => 'required|string|max:150',
            'description' => 'required|string|min:10|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
            'status' => 'required|in:publish,draft'
        ];
        
        $messages = [
            'title.required' => '⚠️ Judul slide wajib diisi',
            'title.max' => '⚠️ Judul slide maksimal 150 karakter',
            'description.required' => '⚠️ Deskripsi slide wajib diisi',
            'description.min' => '⚠️ Deskripsi minimal 10 karakter',
            'description.max' => '⚠️ Deskripsi maksimal 500 karakter',
            'featured_image.image' => '⚠️ File harus berupa gambar',
            'featured_image.mimes' => '⚠️ Format gambar harus JPG atau PNG',
            'featured_image.max' => '⚠️ Ukuran gambar maksimal 4MB',
            'status.required' => '⚠️ Status wajib dipilih'
        ];
        
        $request->validate($rules, $messages);
        
        $data = [
            'title' => $request->title,
            'content' => $request->description,
            'status' => $request->status
        ];
        
        if ($request->hasFile('featured_image')) {
            if ($carousel->featured_image_path) {
                Storage::disk('public')->delete($carousel->featured_image_path);
            }
            
            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('img', $filename, 'public');
            $data['featured_image_path'] = $path;
        }
        
        $carousel->update($data);
        
        return redirect()->route('admin.carousel')
            ->with('success', '✅ Slide carousel berhasil diupdate');
    }
    
    public function destroy($id)
    {
        $carousel = Post::findOrFail($id);
        
        if ($carousel->featured_image_path) {
            Storage::disk('public')->delete($carousel->featured_image_path);
        }
        
        $carousel->delete();
        
        return redirect()->route('admin.carousel')
            ->with('success', '✅ Slide carousel berhasil dihapus');
    }
}