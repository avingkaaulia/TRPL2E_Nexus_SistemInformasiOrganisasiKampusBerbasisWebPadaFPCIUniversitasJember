<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Carbon\Carbon;

class CommentController extends Controller
{
    // Menampilkan komentar
    public function index($id_post)
    {
        $comments = Comment::where('id_post', $id_post)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        $post = Post::find($id_post);
        
        return view('writings.show', compact('comments', 'post'));
    }
    
    // Menyimpan komentar baru
    public function store(Request $request, $id_post)
{
    $request->validate([
        'nama' => 'required|string|max:100',
        'email' => 'required|email|max:100',
        'isi_komentar' => 'required|string|min:3|max:1000',
    ], [
        'isi_komentar.min' => 'Komentar minimal 3 karakter.',
        'isi_komentar.max' => 'Komentar maksimal 1000 karakter.',
    ]);

    // Anti spam: email yang sama tidak boleh komentar dalam 1 menit pada postingan yang sama
    $lastComment = Comment::where('id_post', $id_post)
        ->where('email', $request->email)
        ->latest('tanggal')
        ->first();

    if ($lastComment && Carbon::parse($lastComment->tanggal)->diffInSeconds(now()) < 60) {
        return back()
            ->withInput()
            ->with('error', 'Tunggu 1 menit sebelum mengirim komentar lagi.');
    }

    Comment::create([
        'id_post' => $id_post,
        'nama_pengunjung' => $request->nama,
        'email' => $request->email,
        'isi_komentar' => trim($request->isi_komentar),
        'tanggal' => now(),
    ]);

    return back()->with('success', 'Komentar berhasil ditambahkan!');
}
}