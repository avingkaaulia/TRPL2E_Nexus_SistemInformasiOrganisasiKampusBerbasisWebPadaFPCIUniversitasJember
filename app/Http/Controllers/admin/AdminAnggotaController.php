<?php
// app/Http/Controllers/Admin/AdminAnggotaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\AcceptedMemberMail;

class AdminAnggotaController extends Controller
{
    // Halaman utama kelola anggota
    public function index(Request $request)
    {
        $query = Anggota::with(['user', 'divisi']);
        
        if ($request->has('divisi') && $request->divisi != '') {
            $query->where('id_divisi', $request->divisi);
        }
        
        if ($request->has('periode') && $request->periode != '') {
            $query->where('periode', $request->periode);
        }
        
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }
        
        $anggota = $query->orderBy('no_urut')->paginate(15);
        $divisiList = Divisi::all();
        $periodeList = Anggota::select('periode')->distinct()->pluck('periode');
        
        // Ambil semua email dari tabel anggota
        $emailAnggota = Anggota::with('user')->get()->pluck('user.email')->filter()->toArray();
        
        $pendaftaranDiterima = Pendaftaran::where('status', 'diterima')
            ->whereNotIn('email', $emailAnggota)
            ->get();
        
        return view('admin.anggota.index', compact('anggota', 'divisiList', 'periodeList', 'pendaftaranDiterima'));
    }
    
    // Detail anggota
    public function show($id)
    {
        $anggota = Anggota::with(['user', 'divisi'])->findOrFail($id);
        return view('admin.anggota.show', compact('anggota'));
    }
    
    // Form edit anggota
    public function edit($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);
        $divisiList = Divisi::all();
        $periodeList = ['2024/2025', '2025/2026', '2026/2027'];
        return view('admin.anggota.edit', compact('anggota', 'divisiList', 'periodeList'));
    }
    
    // Update anggota
    public function update(Request $request, $id)
    {
        $anggota = Anggota::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $anggota->id_user . ',id_user',
            'username' => 'required|string|max:50|unique:users,username,' . $anggota->id_user . ',id_user',
            'password' => 'nullable|string|min:6',
            'id_divisi' => 'required|exists:divisi,id_divisi',
            'jabatan' => 'required|string|max:100',
            'periode' => 'required|string|max:20',
            'no_urut' => 'required|integer|unique:anggota,no_urut,' . $id . ',id_anggota',
            'link' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:4048'
        ]);
        
        $userData = [
            'username' => $request->username,
            'email' => $request->email,
            'nama' => $request->nama,
        ];
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        
        $anggota->user->update($userData);
        
        if ($request->hasFile('foto')) {
            if ($anggota->foto && file_exists(public_path($anggota->foto))) {
                unlink(public_path($anggota->foto));
            }
            $file = $request->file('foto');
            $filename = time() . '_' . $request->username . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('anggota', $filename, 'public');
            $anggota->foto = 'storage/' . $path;
        }
        
        $anggota->update([
            'id_divisi' => $request->id_divisi,
            'jabatan' => $request->jabatan,
            'periode' => $request->periode,
            'no_urut' => $request->no_urut,
            'link' => $request->link ?? ''
        ]);
        
        return redirect()->route('admin.anggota.index')
            ->with('success', 'Anggota berhasil diupdate');
    }
    
    // Hapus anggota
    public function destroy($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);
        
        if ($anggota->foto && $anggota->foto != 'assets/img/avatars/default-avatar.png') {
            $fotoPath = public_path($anggota->foto);
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }
        
        $idUser = $anggota->id_user;
        $anggota->delete();
        
        $user = User::find($idUser);
        if ($user) {
            $user->delete();
        }
        
        return redirect()->route('admin.anggota.index')
            ->with('success', 'Anggota berhasil dihapus');
    }
    
    // 🔥 KONVERSI PENDAFTAR MENJADI ANGGOTA (TANPA EMAIL, KARENA EMAIL SUDAH DIKIRIM SAAT DITERIMA)
    public function convertFromPendaftaran($id)
    {
        $pendaftaran = Pendaftaran::findOrFail($id);
        
        // Cek apakah email sudah terdaftar di tabel anggota
        $existingAnggota = Anggota::whereHas('user', function($q) use ($pendaftaran) {
            $q->where('email', $pendaftaran->email);
        })->first();
        
        if ($existingAnggota) {
            return redirect()->back()->with('error', 'Email ' . $pendaftaran->email . ' sudah terdaftar sebagai anggota!');
        }
        
        // Cek apakah user sudah ada
        $user = User::where('email', $pendaftaran->email)->first();
        
        if (!$user) {
            // Buat username dan password menggunakan NIM
            $username = $pendaftaran->nim;
            $password = $pendaftaran->nim;
            
            $existingUser = User::where('username', $username)->first();
            if ($existingUser) {
                $username = $pendaftaran->nim . rand(10, 99);
            }
            
            $user = User::create([
                'username' => $username,
                'email' => $pendaftaran->email,
                'password' => Hash::make($password),
                'nama' => $pendaftaran->nama,
                'id_role' => 2,
                'tanggal_daftar' => now()
            ]);
        }
        
        $defaultFoto = 'assets/img/avatars/default-avatar.png';
        $lastNoUrut = Anggota::max('no_urut') ?? 0;
        
        $divisiId = 1;
        if (!empty($pendaftaran->divisi)) {
            $divisi = Divisi::where('nama_divisi', $pendaftaran->divisi)->first();
            if ($divisi) {
                $divisiId = $divisi->id_divisi;
            }
        }
        
        Anggota::create([
            'id_user' => $user->id_user,
            'id_divisi' => $divisiId,
            'jabatan' => 'Staff',
            'periode' => '2025/2026',
            'foto' => $defaultFoto,
            'no_urut' => $lastNoUrut + 1,
            'link' => ''
        ]);
        
        // Update status pendaftaran jika belum diterima
        if ($pendaftaran->status != 'diterima') {
            $pendaftaran->status = 'diterima';
            $pendaftaran->save();
        }
        
        // 🔥 TIDAK KIRIM EMAIL LAGI (sudah dikirim saat accept)
        return redirect()->route('admin.anggota.index')
            ->with('success', 'Pendaftar ' . $pendaftaran->nama . ' berhasil dikonversi menjadi anggota!');
    }
}