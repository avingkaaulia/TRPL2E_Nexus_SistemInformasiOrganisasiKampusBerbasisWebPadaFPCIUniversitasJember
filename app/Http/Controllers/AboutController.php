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
        // 🔥 CAROUSEL ABOUT
        $carousel = Post::where('status', 'publish')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'carousel_about');
            })
            ->orderBy('date_published', 'desc')
            ->get();
        
        if ($carousel->isEmpty()) {
            $carousel = Post::where('status', 'publish')
                ->whereHas('category', function($q) {
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published', 'desc')
                ->get();
        }
        
        foreach ($carousel as $item) {
            $item->image_url = getImageUrl($item->featured_image_path);
        }
        
        // 🔥 Ambil SEMUA konten About dari database
        $aboutSections = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->orderBy('id_post', 'asc')
            ->get();
        
        $tentang = $aboutSections->firstWhere('title', 'Tentang FPCI UNEJ');
        $sejarah = $aboutSections->firstWhere('title', 'Sejarah');
        $tujuan = $aboutSections->firstWhere('title', 'Tujuan');
        $visi = $aboutSections->firstWhere('title', 'Visi');
        $misi = $aboutSections->firstWhere('title', 'Misi');
        
        $otherSections = $aboutSections->filter(function($item) {
            return !in_array($item->title, ['Tentang FPCI UNEJ', 'Sejarah', 'Tujuan', 'Visi', 'Misi']);
        });
        
        // 🔥 FILTER ANGGOTA: HANYA YANG JABATANNYA PRESIDENT, VICE PRESIDENT, ATAU HEAD
        // Ambil semua anggota, lalu filter berdasarkan jabatan
        $allAnggota = Anggota::with(['user', 'divisi'])
            ->orderBy('no_urut')
            ->get();
        
        // Filter hanya yang jabatannya mengandung kata 'President', 'Vice President', atau 'Head'
        $anggota = $allAnggota->filter(function($item) {
            $jabatan = strtolower($item->jabatan);
            return str_contains($jabatan, 'president') || 
                   str_contains($jabatan, 'vice president') || 
                   str_contains($jabatan, 'head');
        });
        
        // 🔥 Ambil menu dan contact
        $menus = Menu::where('id_menu_parent', 0)->get();
        $contact = Contact::first();
        
        return view('about.index', compact(
            'carousel', 
            'tentang', 'sejarah', 'tujuan', 'visi', 'misi',
            'otherSections',
            'anggota', 'menus', 'contact'
        ));
    }
}