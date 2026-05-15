<?php
// app/Http/Controllers/SearchController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Anggota;
use App\Models\User;
use App\Models\Contact;
use App\Models\PeriodePendaftaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all');
        
        if (!$query) {
            return redirect()->back()->with('error', 'Masukkan kata kunci pencarian');
        }
        
        $results = [];
        $counts = [
            'posts' => 0,
            'writings' => 0,
            'events' => 0,
            'struktur' => 0,
            'contact' => 0,
            'pendaftaran' => 0,
            'about' => 0
        ];
        
        // 1. PENCARIAN DI SEMUA POST
        if ($type == 'all' || $type == 'posts') {
            $posts = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('content', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('date_published', 'desc')
                ->take(20)
                ->get();
            
            foreach ($posts as $post) {
                $post->image_url = getImageUrl($post->featured_image_path);
                $post->search_type = 'posts';
                $post->link = '/post/' . $post->id_post;
                $post->excerpt = Str::limit(strip_tags($post->content), 150);
                $post->category_name = $post->category ? $post->category->category_name : 'Uncategorized';
            }
            
            $results['posts'] = $posts;
            $counts['posts'] = $posts->count();
        }
        
        // 2. PENCARIAN DI WRITINGS (category_id = 2 dan subkategorinya: 8,9,10,11)
        if ($type == 'all' || $type == 'writings') {
            $writingsIds = [2, 8, 9, 10, 11];
            
            $writings = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->whereIn('id_post_category', $writingsIds)
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('content', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('date_published', 'desc')
                ->take(20)
                ->get();
            
            foreach ($writings as $writing) {
                $writing->image_url = getImageUrl($writing->featured_image_path);
                $writing->search_type = 'writings';
                $writing->link = '/post/' . $writing->id_post;
                $writing->excerpt = Str::limit(strip_tags($writing->content), 150);
                $writing->category_name = $writing->category ? $writing->category->category_name : 'Writings';
            }
            
            $results['writings'] = $writings;
            $counts['writings'] = $writings->count();
        }
        
        // 3. PENCARIAN DI EVENTS & PROGRAMS (category event=6 dan program=7)
        if ($type == 'all' || $type == 'events') {
            $eventProgramIds = [6, 12, 13, 7, 14, 15, 16];
            
            $events = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->whereIn('id_post_category', $eventProgramIds)
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('content', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('date_published', 'desc')
                ->take(20)
                ->get();
            
            foreach ($events as $event) {
                $event->image_url = getImageUrl($event->featured_image_path);
                $event->search_type = 'events';
                $event->link = '/post/' . $event->id_post;
                $event->excerpt = Str::limit(strip_tags($event->content), 150);
                $event->category_name = $event->category ? $event->category->category_name : 'Event & Program';
            }
            
            $results['events'] = $events;
            $counts['events'] = $events->count();
        }
        
        // 4. PENCARIAN DI STRUKTUR KEPENGURUSAN (bukan anggota perorangan)
        if ($type == 'all' || $type == 'struktur') {
            // Ambil struktur kepengurusan berdasarkan divisi dan jabatan
            $struktur = DB::table('anggota')
                ->join('users', 'anggota.id_user', '=', 'users.id_user')
                ->join('divisi', 'anggota.id_divisi', '=', 'divisi.id_divisi')
                ->where(function($q) use ($query) {
                    $q->where('divisi.nama_divisi', 'LIKE', '%' . $query . '%')
                      ->orWhere('anggota.jabatan', 'LIKE', '%' . $query . '%')
                      ->orWhere('users.nama', 'LIKE', '%' . $query . '%')
                      ->orWhere('anggota.periode', 'LIKE', '%' . $query . '%');
                })
                ->select(
                    'divisi.nama_divisi',
                    'anggota.jabatan',
                    'users.nama as nama_lengkap',
                    'anggota.periode',
                    'anggota.no_urut',
                    'anggota.foto',
                    'anggota.id_anggota'
                )
                ->orderBy('anggota.no_urut', 'asc')
                ->get();
            
            // Group by divisi untuk menampilkan struktur
            $strukturByDivisi = $struktur->groupBy('nama_divisi');
            
            foreach ($strukturByDivisi as $divisiName => $anggotaList) {
                $item = new \stdClass();
                $item->search_type = 'struktur';
                $item->title = 'Struktur ' . $divisiName;
                $item->excerpt = 'Jabatan: ' . $anggotaList->pluck('jabatan')->implode(', ');
                $item->link = '/about#struktur';
                $item->image_url = asset('assets/img/logo.png');
                $item->anggota_list = $anggotaList;
                $item->category_name = 'Struktur Kepengurusan';
                $item->date_published = null;
                
                $results['struktur'][] = $item;
            }
            
            $counts['struktur'] = $strukturByDivisi->count();
        }
        
        // 5. PENCARIAN DI CONTACT
        if ($type == 'all' || $type == 'contact') {
            $contact = DB::table('contact')->first();
            $contactResults = collect();

            if ($contact) {
                $fields = [
                    $contact->email,
                    $contact->no_hp,
                    $contact->alamat,
                    $contact->instagram,
                    $contact->linkedin,
                    $contact->tiktok,
                    $contact->youtube,
                    $contact->x,
                ];

                $matched = collect($fields)->filter(function($val) use ($query) {
                    return $val && stripos($val, $query) !== false;
                });

                if ($matched->count() > 0) {
                    $item = new \stdClass();
                    $item->search_type = 'contact';
                    $item->title = 'Kontak FPCI UNEJ';
                    $item->excerpt = $contact->email . ' | ' . $contact->no_hp;
                    $item->link = '/contact';
                    $item->image_url = asset('assets/img/logo.png');
                    $item->contact_data = $contact;
                    $item->category_name = 'Kontak Resmi';
                    $item->date_published = null;

                    $contactResults->push($item);
                }
            }

            $results['contact'] = $contactResults;
            $counts['contact'] = $contactResults->count();
        }
        
        // 6. PENCARIAN DI PENDAFTARAN
        if ($type == 'all' || $type == 'pendaftaran') {
            $periodeAktif = PeriodePendaftaran::where(function($q) use ($query) {
                    $q->where('tahun_ajaran', 'LIKE', '%' . $query . '%')
                      ->orWhere('nama_periode', 'LIKE', '%' . $query . '%')
                      ->orWhere('deskripsi', 'LIKE', '%' . $query . '%');
                })
                ->orderBy('is_active', 'desc')
                ->orderBy('tanggal_mulai', 'desc')
                ->get();
            
            $pendaftaranResults = collect();
            
            foreach ($periodeAktif as $periode) {
                $item = new \stdClass();
                $item->search_type = 'pendaftaran';
                $item->title = $periode->nama_periode . ' (' . $periode->tahun_ajaran . ')';
                $item->excerpt = $periode->deskripsi ?? 'Pendaftaran anggota baru FPCI UNEJ';
                $item->link = '/pendaftaran';
                $item->image_url = asset('assets/img/logo.png');
                $item->tanggal_mulai = $periode->tanggal_mulai;
                $item->tanggal_selesai = $periode->tanggal_selesai;
                $item->is_active = $periode->is_active;
                $item->kuota = $periode->kuota;
                $item->date_published = $periode->tanggal_mulai;
                $item->category_name = 'Pendaftaran';
                
                $pendaftaranResults->push($item);
            }
            
            $results['pendaftaran'] = $pendaftaranResults;
            $counts['pendaftaran'] = $pendaftaranResults->count();
        }
        
        // 7. PENCARIAN DI ABOUT (konten about)
if ($type == 'all' || $type == 'about') {
    $aboutPosts = Post::with(['category'])
        ->where('status', 'publish')
        ->whereHas('category', function($q) {
            $q->where('category_name', 'about');
        })
        ->where(function($q) use ($query) {
            $q->where('title', 'LIKE', '%' . $query . '%')
              ->orWhere('content', 'LIKE', '%' . $query . '%');
        })
        ->orderBy('date_published', 'desc')
        ->take(5)
        ->get();

    foreach ($aboutPosts as $item) {
        $item->image_url = getImageUrl($item->featured_image_path);
        $item->search_type = 'about';
        $item->link = '/about';
        $item->excerpt = Str::limit(strip_tags($item->content), 150);
        $item->category_name = 'About FPCI';
    }

    $results['about'] = $aboutPosts;
    $counts['about'] = $aboutPosts->count();
}
        
        $totalResults = array_sum($counts);
        
        return view('search.results', compact('results', 'counts', 'query', 'type', 'totalResults'));
    }
    
    public function autocomplete(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = [];
        
        // Search di posts berdasarkan title
        $posts = Post::where('status', 'publish')
            ->where('post_type', 'post')
            ->where('title', 'LIKE', '%' . $query . '%')
            ->select('id_post', 'title')
            ->orderBy('date_published', 'desc')
            ->take(3)
            ->get();
        
        foreach ($posts as $post) {
            $suggestions[] = [
                'title' => Str::limit($post->title, 50),
                'url' => '/post/' . $post->id_post,
                'type' => 'Artikel'
            ];
        }
        
        // Search struktur kepengurusan (divisi)
        $divisi = DB::table('divisi')
            ->where('nama_divisi', 'LIKE', '%' . $query . '%')
            ->take(2)
            ->get();

        foreach ($divisi as $d) {
            $suggestions[] = [
                'title' => 'Struktur ' . $d->nama_divisi,
                'url' => '/about#struktur',
                'type' => 'Struktur Kepengurusan'
            ];
        }
        
        // Search contact
        $contact = Contact::where(function($q) use ($query) {
                $q->where('email', 'LIKE', '%' . $query . '%')
                  ->orWhere('no_hp', 'LIKE', '%' . $query . '%')
                  ->orWhere('instagram', 'LIKE', '%' . $query . '%');
            })
            ->first();
        
        if ($contact) {
            $suggestions[] = [
                'title' => 'Informasi Kontak FPCI UNEJ',
                'url' => '/contact',
                'type' => 'Kontak - ' . $contact->email
            ];
        }
        
        // Search pendaftaran
        $pendaftaran = PeriodePendaftaran::where('tahun_ajaran', 'LIKE', '%' . $query . '%')
            ->orWhere('nama_periode', 'LIKE', '%' . $query . '%')
            ->first();
        
        if ($pendaftaran) {
            $status = $pendaftaran->is_active ? 'Aktif' : 'Tutup';
            $suggestions[] = [
                'title' => $pendaftaran->nama_periode . ' ' . $pendaftaran->tahun_ajaran,
                'url' => '/pendaftaran',
                'type' => 'Pendaftaran - ' . $status
            ];
        }
        
        // Batasi maksimal 8 saran
        $suggestions = array_slice($suggestions, 0, 8);
        
        return response()->json($suggestions);
    }
}