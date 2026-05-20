<?php
// app/Http/Controllers/Admin/AdminCommentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
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
        
        // 🔥 PERBAIKI: Urutkan berdasarkan id_comment ASC (yang lama di atas, baru di bawah)
        // Ini untuk menghindari masalah token CSRF pada baris pertama
        $comments = $query->orderBy('id_comment', 'asc')->paginate(20);
        
        $totalComments = Comment::count();
        $pendingComments = Comment::where('status', 'pending')->count();
        $approvedComments = Comment::where('status', 'approved')->count();
        $rejectedComments = Comment::where('status', 'rejected')->count();
        
        return view('admin.comments.index', compact('comments', 'totalComments', 'pendingComments', 'approvedComments', 'rejectedComments'));
    }
    
    // 🔥 APPROVE - Method POST
    public function approve($id)
    {
        try {
            Log::info('Approve comment ID: ' . $id . ' - Method: ' . $_SERVER['REQUEST_METHOD']);
            
            // Cari komentar berdasarkan ID
            $comment = Comment::find($id);
            
            if (!$comment) {
                return redirect()->back()->with('error', 'Komentar tidak ditemukan!');
            }
            
            // Log status saat ini
            Log::info('Komentar ID ' . $id . ' - Nama: ' . $comment->nama_pengunjung . ' - Status saat ini: ' . $comment->status);
            
            // Cek status saat ini
            if ($comment->status == 'approved') {
                return redirect()->back()->with('error', 'Komentar sudah disetujui sebelumnya!');
            }
            
            if ($comment->status == 'rejected') {
                return redirect()->back()->with('error', 'Komentar sudah ditolak sebelumnya!');
            }
            
            // Update status menggunakan model
            $comment->status = 'approved';
            $comment->save();
            
            Log::info('Komentar ID ' . $id . ' berhasil disetujui. Status baru: ' . $comment->status);
            
            return redirect()->back()->with('success', 'Komentar dari "' . $comment->nama_pengunjung . '" berhasil disetujui!');
            
        } catch (\Exception $e) {
            Log::error('Error approve komentar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyetujui komentar: ' . $e->getMessage());
        }
    }
    
    // 🔥 REJECT - Method POST
    public function reject($id)
    {
        try {
            Log::info('Reject comment ID: ' . $id . ' - Method: ' . $_SERVER['REQUEST_METHOD']);
            
            // Cari komentar berdasarkan ID
            $comment = Comment::find($id);
            
            if (!$comment) {
                return redirect()->back()->with('error', 'Komentar tidak ditemukan!');
            }
            
            // Log status saat ini
            Log::info('Komentar ID ' . $id . ' - Nama: ' . $comment->nama_pengunjung . ' - Status saat ini: ' . $comment->status);
            
            // Cek status saat ini
            if ($comment->status == 'approved') {
                return redirect()->back()->with('error', 'Komentar sudah disetujui sebelumnya!');
            }
            
            if ($comment->status == 'rejected') {
                return redirect()->back()->with('error', 'Komentar sudah ditolak sebelumnya!');
            }
            
            // Update status menggunakan model
            $comment->status = 'rejected';
            $comment->save();
            
            Log::info('Komentar ID ' . $id . ' berhasil ditolak. Status baru: ' . $comment->status);
            
            return redirect()->back()->with('success', 'Komentar dari "' . $comment->nama_pengunjung . '" berhasil ditolak!');
            
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
    
    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada komentar yang dipilih!');
        }
        
        if ($action == 'approve') {
            $updated = Comment::whereIn('id_comment', $ids)->where('status', 'pending')->update(['status' => 'approved']);
            $message = $updated . ' komentar terpilih berhasil disetujui!';
        } elseif ($action == 'reject') {
            $updated = Comment::whereIn('id_comment', $ids)->where('status', 'pending')->update(['status' => 'rejected']);
            $message = $updated . ' komentar terpilih berhasil ditolak!';
        } elseif ($action == 'delete') {
            $deleted = Comment::whereIn('id_comment', $ids)->delete();
            $message = $deleted . ' komentar terpilih berhasil dihapus!';
        } else {
            return redirect()->back()->with('error', 'Aksi tidak dikenal!');
        }
        
        return redirect()->back()->with('success', $message);
    }
}