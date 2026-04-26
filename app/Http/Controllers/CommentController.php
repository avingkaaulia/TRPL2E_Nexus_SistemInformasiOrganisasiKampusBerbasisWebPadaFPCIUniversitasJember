<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Carbon\Carbon;

class CommentController extends Controller
{
    // 🔥 UNTUK TESTING - Menampilkan halaman post dengan comment
    public function testShow($id)
    {
        $post = Post::with(['category', 'user'])->findOrFail($id);
        $comments = Comment::where('id_post', $id)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Langsung panggil view comments saja (tanpa layout post dari teman)
        return view('test-post', compact('post', 'comments'));
    }
    
    // Menyimpan komentar baru
    public function store(Request $request, $id_post)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'isi_komentar' => 'required|string'
        ]);
        
        Comment::create([
            'id_post' => $id_post,
            'nama_pengunjung' => $request->nama,
            'email' => $request->email,
            'isi_komentar' => $request->isi_komentar,
            'tanggal' => Carbon::now()
        ]);
        
        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan!');
    }
}