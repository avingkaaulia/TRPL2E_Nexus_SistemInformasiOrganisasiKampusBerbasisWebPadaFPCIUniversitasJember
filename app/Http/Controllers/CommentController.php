<?php
// app/Http/Controllers/CommentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
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

        // 🔥 SIMPAN KOMENTAR DENGAN STATUS PENDING
        try {
            $comment = Comment::create([
                'id_post' => $id_post,
                'nama_pengunjung' => $request->nama,
                'email' => $request->email,
                'isi_komentar' => trim($request->isi_komentar),
                'tanggal' => now(),
                'is_replied' => 0,
                'status' => 'pending'
            ]);
            
            Log::info('Komentar baru disimpan dengan ID: ' . $comment->id_comment . ', Status: ' . $comment->status);
            
            return back()->with('success', 'Komentar berhasil ditambahkan dan menunggu persetujuan admin!');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan komentar: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan komentar. Silahkan coba lagi.');
        }
    }
}