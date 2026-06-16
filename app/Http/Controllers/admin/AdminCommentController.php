<?php
// app/Http/Controllers/Admin/AdminCommentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            if ($request->status == 'approved') {
                $query->where('status', 'approved');
            } elseif ($request->status == 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status == 'rejected') {
                $query->where('status', 'rejected');
            }
        }
        
        $comments = $query->orderBy('id_comment', 'desc')->paginate(15);
        
        $totalComments = Comment::count();
        $pendingComments = Comment::where('status', 'pending')->count();
        $approvedComments = Comment::where('status', 'approved')->count();
        $rejectedComments = Comment::where('status', 'rejected')->count();
        
        $unrepliedComments = Comment::where('status', 'approved')->where('is_replied', 0)->count();
        
        return view('admin.comments.index', compact('comments', 'totalComments', 'pendingComments', 'approvedComments', 'rejectedComments', 'unrepliedComments'));
    }
    
    // 🔥 PERBAIKI: APPROVE dengan debug
    public function approve(Request $request, $id)
    {
        try {
            Log::info('Approve comment attempt - ID: ' . $id);
            Log::info('Request method: ' . $request->method());
            Log::info('CSRF token: ' . $request->session()->token());
            
            $comment = Comment::find($id);
            
            if (!$comment) {
                Log::error('Comment not found - ID: ' . $id);
                return redirect()->back()->with('error', 'Komentar tidak ditemukan!');
            }
            
            Log::info('Comment found - Status: ' . $comment->status);
            
            if ($comment->status == 'approved') {
                return redirect()->back()->with('error', 'Komentar sudah disetujui sebelumnya!');
            }
            
            if ($comment->status == 'rejected') {
                return redirect()->back()->with('error', 'Komentar sudah ditolak sebelumnya!');
            }
            
            // 🔥 UPDATE STATUS LANGSUNG DENGAN DB FACADE
            $affected = DB::table('comments')
                ->where('id_comment', $id)
                ->where('status', 'pending')
                ->update(['status' => 'approved']);
            
            if ($affected) {
                Log::info('Comment approved successfully - ID: ' . $id);
                return redirect()->back()->with('success', 'Komentar dari "' . $comment->nama_pengunjung . '" berhasil disetujui!');
            } else {
                Log::warning('No rows updated - Comment ID: ' . $id);
                return redirect()->back()->with('error', 'Gagal menyetujui komentar. Tidak ada perubahan.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error approve komentar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyetujui komentar: ' . $e->getMessage());
        }
    }
    
    // 🔥 PERBAIKI: REJECT dengan debug
    public function reject(Request $request, $id)
    {
        try {
            Log::info('Reject comment attempt - ID: ' . $id);
            
            $comment = Comment::find($id);
            
            if (!$comment) {
                Log::error('Comment not found - ID: ' . $id);
                return redirect()->back()->with('error', 'Komentar tidak ditemukan!');
            }
            
            Log::info('Comment found - Status: ' . $comment->status);
            
            if ($comment->status == 'approved') {
                return redirect()->back()->with('error', 'Komentar sudah disetujui sebelumnya!');
            }
            
            if ($comment->status == 'rejected') {
                return redirect()->back()->with('error', 'Komentar sudah ditolak sebelumnya!');
            }
            
            // 🔥 UPDATE STATUS LANGSUNG DENGAN DB FACADE
            $affected = DB::table('comments')
                ->where('id_comment', $id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
            
            if ($affected) {
                Log::info('Comment rejected successfully - ID: ' . $id);
                return redirect()->back()->with('success', 'Komentar dari "' . $comment->nama_pengunjung . '" berhasil ditolak!');
            } else {
                Log::warning('No rows updated - Comment ID: ' . $id);
                return redirect()->back()->with('error', 'Gagal menolak komentar. Tidak ada perubahan.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error reject komentar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menolak komentar: ' . $e->getMessage());
        }
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
        $nama = $comment->nama_pengunjung;
        $comment->delete();
        
        return redirect()->route('admin.comments.index')
            ->with('success', 'Komentar dari "' . $nama . '" berhasil dihapus!');
    }
    // 🔥 TAMBAH METHOD TOGGLE KOMENTAR
    public function toggleComments(Request $request)
    {
        $currentStatus = Setting::isCommentsEnabled();
        $newStatus = !$currentStatus;
        
        Setting::toggleComments($newStatus);
        
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.comments.index')
            ->with('success', "Fitur komentar berhasil {$statusText}.");
    }
}