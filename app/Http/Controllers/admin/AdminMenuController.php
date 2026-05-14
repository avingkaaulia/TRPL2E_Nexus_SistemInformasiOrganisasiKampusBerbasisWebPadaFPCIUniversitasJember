<?php
// app/Http/Controllers/Admin/AdminMenuController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class AdminMenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('parent')->orderBy('id_menu_parent')->orderBy('id_menu')->get();
        $pages = Post::where('post_type', 'page')->where('status', 'publish')->get();
        $posts = Post::where('post_type', 'post')->where('status', 'publish')->take(20)->get();
        
        return view('admin.menu.index', compact('menus', 'pages', 'posts'));
    }
    
    public function create()
    {
        $parents = Menu::where('id_menu_parent', 0)->get();
        $pages = Post::where('post_type', 'page')->where('status', 'publish')->get();
        $posts = Post::where('post_type', 'post')->where('status', 'publish')->take(20)->get();
        
        return view('admin.menu.create', compact('parents', 'pages', 'posts'));
    }
    
    public function store(Request $request)
    {
        // Validasi dengan pesan kustom
        $request->validate([
            'menu_label' => 'required|string|max:100',
            'link' => 'required|string|max:255',
            'id_menu_parent' => 'nullable|integer'
        ], [
            'menu_label.required' => '⚠️ Nama menu wajib diisi',
            'menu_label.max' => '⚠️ Nama menu maksimal 100 karakter',
            'link.required' => '⚠️ Link menu wajib diisi',
            'link.max' => '⚠️ Link maksimal 255 karakter',
        ]);
        
        // Proses link
        $link = $request->link;
        if ($link === 'custom') {
            if (empty($request->custom_link)) {
                return redirect()->back()
                    ->with('error', '⚠️ Custom Link URL wajib diisi!')
                    ->withInput();
            }
            $link = $request->custom_link;
            
            // Validasi URL custom
            if (!filter_var($link, FILTER_VALIDATE_URL) && !str_starts_with($link, '/')) {
                return redirect()->back()
                    ->with('error', '⚠️ Format URL tidak valid! Contoh: https://example.com atau /halaman')
                    ->withInput();
            }
        }
        
        $parentId = $request->id_menu_parent ?? 0;
        
        // Cek parent ID valid (jika bukan 0)
        if ($parentId != 0 && !Menu::where('id_menu', $parentId)->exists()) {
            return redirect()->back()
                ->with('error', '⚠️ Parent menu yang dipilih tidak valid!')
                ->withInput();
        }
        
        // Cek duplikat menu (opsional)
        $existingMenu = Menu::where('menu_label', $request->menu_label)->first();
        if ($existingMenu) {
            return redirect()->back()
                ->with('error', '⚠️ Menu dengan nama "' . $request->menu_label . '" sudah ada!')
                ->withInput();
        }
        
        $menu = Menu::create([
            'menu_label' => $request->menu_label,
            'link' => $link,
            'id_menu_parent' => $parentId
        ]);
        
        if ($menu) {
            return redirect()->route('admin.menu.index')
                ->with('success', '✅ Menu "' . $request->menu_label . '" berhasil ditambahkan!');
        } else {
            return redirect()->back()
                ->with('error', '❌ Gagal menambahkan menu. Silakan coba lagi.')
                ->withInput();
        }
    }
    
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $parents = Menu::where('id_menu_parent', 0)->where('id_menu', '!=', $id)->get();
        $pages = Post::where('post_type', 'page')->where('status', 'publish')->get();
        $posts = Post::where('post_type', 'post')->where('status', 'publish')->take(20)->get();

        return view('admin.menu.edit', compact('menu', 'parents', 'pages', 'posts'));
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        // Validasi dengan pesan kustom
        $request->validate([
            'menu_label' => 'required|string|max:100',
            'link' => 'required|string|max:255',
            'id_menu_parent' => 'nullable|integer',
        ], [
            'menu_label.required' => '⚠️ Nama menu wajib diisi',
            'menu_label.max' => '⚠️ Nama menu maksimal 100 karakter',
            'link.required' => '⚠️ Link menu wajib diisi',
            'link.max' => '⚠️ Link maksimal 255 karakter',
        ]);

        // Proses link
        $link = $request->link;
        if ($link === 'custom') {
            if (empty($request->custom_link)) {
                return redirect()->back()
                    ->with('error', '⚠️ Custom Link URL wajib diisi!')
                    ->withInput();
            }
            $link = $request->custom_link;
            
            if (!filter_var($link, FILTER_VALIDATE_URL) && !str_starts_with($link, '/')) {
                return redirect()->back()
                    ->with('error', '⚠️ Format URL tidak valid! Contoh: https://example.com atau /halaman')
                    ->withInput();
            }
        }

        $parentId = $request->id_menu_parent ?? 0;

        // Cek parent ID valid (jika bukan 0)
        if ($parentId != 0 && !Menu::where('id_menu', $parentId)->exists()) {
            return redirect()->back()
                ->with('error', '⚠️ Parent menu yang dipilih tidak valid!')
                ->withInput();
        }
        
        // Cek duplikat menu (kecuali dirinya sendiri)
        $existingMenu = Menu::where('menu_label', $request->menu_label)
            ->where('id_menu', '!=', $id)
            ->first();
        if ($existingMenu) {
            return redirect()->back()
                ->with('error', '⚠️ Menu dengan nama "' . $request->menu_label . '" sudah ada!')
                ->withInput();
        }

        $menu->update([
            'menu_label' => $request->menu_label,
            'link' => $link,
            'id_menu_parent' => $parentId,
        ]);

        return redirect()->route('admin.menu.index')
            ->with('success', '✅ Menu "' . $menu->menu_label . '" berhasil diupdate');
    }
    
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menuLabel = $menu->menu_label;
        
        // Cek apakah menu ini memiliki child
        $childCount = Menu::where('id_menu_parent', $id)->count();
        if ($childCount > 0) {
            return redirect()->back()
                ->with('error', '⚠️ Menu "' . $menuLabel . '" memiliki ' . $childCount . ' sub-menu. Hapus sub-menu terlebih dahulu!');
        }
        
        Menu::where('id_menu_parent', $id)->delete();
        $menu->delete();
        
        return redirect()->route('admin.menu.index')
            ->with('success', '✅ Menu "' . $menuLabel . '" berhasil dihapus');
    }
}