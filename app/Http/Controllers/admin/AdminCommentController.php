<?php
// app/Http/Controllers/Admin/AdminCommentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::with('post');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_pengunjung', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('isi_komentar', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('status')) {
            if ($request->status == 'replied') {
                $query->where('is_replied', 1);
            } elseif ($request->status == 'pending') {
                $query->where('is_replied', 0);
            }
        }
        
        $comments = $query->orderBy('tanggal', 'desc')->paginate(20);
        $totalComments = Comment::count();
        $pendingComments = Comment::where('is_replied', 0)->count();
        $repliedComments = Comment::where('is_replied', 1)->count();
        
        return view('admin.comments.index', compact('comments', 'totalComments', 'pendingComments', 'repliedComments'));
    }
    
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|min:3|max:1000'
        ]);
        
        $comment = Comment::findOrFail($id);
        
        $comment->update([
            'reply' => trim($request->reply),
            'reply_by' => Auth::user()->nama ?? 'Admin',
            'reply_date' => Carbon::now(),
            'is_replied' => 1
        ]);
        
        return redirect()->back()->with('success', 'Balasan berhasil ditambahkan!');
    }
    
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        
        return redirect()->route('admin.comments.index')
            ->with('success', 'Komentar berhasil dihapus!');
    }
    
    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        
        if ($request->action == 'delete') {
            Comment::whereIn('id_comment', $ids)->delete();
            return redirect()->back()->with('success', 'Komentar terpilih berhasil dihapus!');
        }
        
        return redirect()->back()->with('error', 'Aksi tidak dikenal!');
    }
}