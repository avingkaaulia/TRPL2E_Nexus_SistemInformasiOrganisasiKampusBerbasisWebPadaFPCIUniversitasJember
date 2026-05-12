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
        
        // 🔥 Ambil SEMUA konten About dari database (urutan berdasarkan id_post)
        $aboutSections = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'about');
            })
            ->orderBy('id_post', 'asc')
            ->get();
        
        // Pisahkan berdasarkan judul untuk kontrol yang lebih baik
        $tentang = $aboutSections->firstWhere('title', 'Tentang FPCI UNEJ');
        $sejarah = $aboutSections->firstWhere('title', 'Sejarah');
        $tujuan = $aboutSections->firstWhere('title', 'Tujuan');
        $visi = $aboutSections->firstWhere('title', 'Visi');
        $misi = $aboutSections->firstWhere('title', 'Misi');
        
        // 🔥 Ambil POSTINGAN LAINNYA (selain yang sudah ditentukan di atas)
        // Postingan baru akan masuk ke sini
        $otherSections = $aboutSections->filter(function($item) {
            return !in_array($item->title, ['Tentang FPCI UNEJ', 'Sejarah', 'Tujuan', 'Visi', 'Misi']);
        });
        
        // 🔥 Ambil semua anggota untuk struktur organisasi
        $anggota = Anggota::with(['user', 'divisi'])
            ->orderBy('no_urut')
            ->get();
        
        // 🔥 Ambil menu dan contact
        $menus = Menu::where('id_menu_parent', 0)->get();
        $contact = Contact::first();
        
        return view('about.index', compact(
            'carousel', 
            'tentang', 'sejarah', 'tujuan', 'visi', 'misi',
            'otherSections', // 🔥 KIRIMKAN KE VIEW
            'anggota', 'menus', 'contact'
        ));
    }
}