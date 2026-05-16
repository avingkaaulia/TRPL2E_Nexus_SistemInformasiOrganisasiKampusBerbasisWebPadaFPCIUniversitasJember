<?php
// app/Http/Controllers/Admin/AdminCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostCategory;
use Illuminate\Support\Facades\DB;

class AdminCategoryController extends Controller
{
    // Halaman utama kelola kategori
    public function index()
    {
        $categories = PostCategory::with('parent')->orderBy('parent_id')->orderBy('id_category')->get();
        
        // Buat tree view
        $categoryTree = $this->buildTree($categories);
        
        return view('admin.categories.index', compact('categories', 'categoryTree'));
    }
    
    // Form tambah kategori
    public function create()
    {
        $categories = PostCategory::orderBy('category_name')->get();
        return view('admin.categories.create', compact('categories'));
    }
    
    // Simpan kategori baru
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:50|unique:post_category,category_name',
            'parent_id' => 'nullable|exists:post_category,id_category'
        ]);
        
        PostCategory::create([
            'category_name' => $request->category_name,
            'parent_id' => $request->parent_id ?? null
        ]);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "' . $request->category_name . '" berhasil ditambahkan');
    }
    
    // Form edit kategori
    public function edit($id)
    {
        $category = PostCategory::findOrFail($id);
        $categories = PostCategory::where('id_category', '!=', $id)->orderBy('category_name')->get();
        
        return view('admin.categories.edit', compact('category', 'categories'));
    }
    
    // Update kategori
    public function update(Request $request, $id)
    {
        $category = PostCategory::findOrFail($id);
        
        $request->validate([
            'category_name' => 'required|string|max:50|unique:post_category,category_name,' . $id . ',id_category',
            'parent_id' => 'nullable|exists:post_category,id_category'
        ]);
        
        // Cek agar tidak bisa memilih diri sendiri sebagai parent
        if ($request->parent_id == $id) {
            return redirect()->back()
                ->with('error', 'Kategori tidak bisa menjadi parent untuk dirinya sendiri!')
                ->withInput();
        }
        
        $category->update([
            'category_name' => $request->category_name,
            'parent_id' => $request->parent_id ?? null
        ]);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "' . $request->category_name . '" berhasil diupdate');
    }
    
    // Hapus kategori
    public function destroy($id)
    {
        $category = PostCategory::findOrFail($id);
        $categoryName = $category->category_name;
        
        // Cek apakah kategori memiliki child
        $childCount = PostCategory::where('parent_id', $id)->count();
        if ($childCount > 0) {
            return redirect()->back()
                ->with('error', 'Kategori "' . $categoryName . '" memiliki ' . $childCount . ' sub-kategori. Hapus sub-kategori terlebih dahulu!');
        }
        
        // Cek apakah kategori digunakan di posts
        $postCount = DB::table('posts')->where('id_post_category', $id)->count();
        if ($postCount > 0) {
            return redirect()->back()
                ->with('error', 'Kategori "' . $categoryName . '" sedang digunakan di ' . $postCount . ' postingan. Ganti kategori postingan terlebih dahulu!');
        }
        
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "' . $categoryName . '" berhasil dihapus');
    }
    
    // Fungsi untuk menghitung total post termasuk semua sub-kategori
    private function getTotalPostCount($categoryId, $categories)
    {
        $total = DB::table('posts')->where('id_post_category', $categoryId)->count();
        
        // Cari semua sub-kategori
        $children = $categories->where('parent_id', $categoryId);
        foreach ($children as $child) {
            $total += $this->getTotalPostCount($child->id_category, $categories);
        }
        
        return $total;
    }
    
    // Build tree view untuk kategori
    private function buildTree($categories, $parentId = null, $level = 0)
    {
        $result = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->level = $level;
                $category->total_posts = $this->getTotalPostCount($category->id_category, $categories);
                $category->children = $this->buildTree($categories, $category->id_category, $level + 1);
                $result[] = $category;
            }
        }
        return $result;
    }
}