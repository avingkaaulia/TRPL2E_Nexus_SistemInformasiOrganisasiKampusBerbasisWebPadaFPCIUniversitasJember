<?php
// app/Http/Controllers/Admin/AdminActivityLogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        // Filter berdasarkan user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan aksi
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasarkan tanggal (dari)
        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        // Filter berdasarkan tanggal (sampai)
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        // Filter berdasarkan kata kunci
        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->cari . '%')
                  ->orWhere('user_name', 'like', '%' . $request->cari . '%');
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Data buat dropdown filter
        $users = User::orderBy('nama')->get();
        $actions = ['login', 'logout', 'create', 'update', 'delete', 'approve', 'reject', 'upload'];

        // Statistik
        $totalLogs = ActivityLog::count();
        $todayLogs = ActivityLog::whereDate('created_at', today())->count();
        $activeUsers = ActivityLog::whereDate('created_at', today())->distinct('user_id')->count('user_id');

        return view('admin.activity-log.index', compact(
            'logs', 'users', 'actions', 'totalLogs', 'todayLogs', 'activeUsers'
        ));
    }

    // Hapus log lama (minimal 30 hari)
    public function clear(Request $request)
    {
        $request->validate([
            'hari' => 'required|integer|min:1'
        ]);

        $deleted = ActivityLog::where('created_at', '<', now()->subDays($request->hari))->delete();

        return redirect()->route('admin.activity-log.index')
            ->with('success', $deleted . ' log lama berhasil dihapus.');
    }
}