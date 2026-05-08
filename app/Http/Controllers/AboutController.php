<?php
// app/Http/Controllers/AboutController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Divisi;
use App\Models\Menu;
use App\Models\Contact;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class AboutController extends Controller
{
    public function index()
    {
         // 🔥 CAROUSEL ABOUT - khusus kategori carousel_about
    $carousel = Post::where('status', 'publish')
        ->whereHas('category', function($q) {
            $q->where('category_name', 'carousel_about');
        })
        ->orderBy('date_published', 'desc')
        ->get();
    
    // Fallback ke carousel umum jika kosong
    if ($carousel->isEmpty()) {
        $carousel = Post::where('status', 'publish')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'carousel');
            })
            ->orderBy('date_published', 'desc')
            ->get();
    }
        // 🔥 Ambil SEMUA konten About dari database (terpisah-pisah)
        $aboutSections = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->orderBy('id_post', 'asc')
            ->get();
        
        // 🔥 Atau bisa juga diambil berdasarkan judul spesifik
        $tentang = Post::where('status', 'publish')
            ->where('title', 'Tentang FPCI UNEJ')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->first();
        
        $sejarah = Post::where('status', 'publish')
            ->where('title', 'Sejarah')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->first();
        
        $tujuan = Post::where('status', 'publish')
            ->where('title', 'Tujuan')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->first();
        
        $visi = Post::where('status', 'publish')
            ->where('title', 'Visi')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->first();
        
        $misi = Post::where('status', 'publish')
            ->where('title', 'Misi')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->first();
        
        // 🔥 Ambil semua anggota untuk struktur organisasi
        $anggota = Anggota::with(['user', 'divisi'])
            ->orderBy('no_urut')
            ->get();
        
        // 🔥 Ambil menu dan contact
        $menus = Menu::where('id_menu_parent', 0)->get();
        $contact = Contact::first();
        
        return view('about.index', compact(
            'carousel', 'tentang', 'sejarah', 'tujuan', 'visi', 'misi',
            'anggota', 'menus', 'contact'
        ));
    }
}