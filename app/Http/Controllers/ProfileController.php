<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // 🔥 VALIDASI DATA PROFIL
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id_user . ',id_user',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi',
            'nama.max' => 'Nama lengkap maksimal 100 karakter',
            'username.required' => 'Username wajib diisi',
            'username.max' => 'Username maksimal 50 karakter',
            'username.unique' => 'Username sudah digunakan, silahkan pilih username lain',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 100 karakter',
            'email.unique' => 'Email sudah terdaftar, silahkan gunakan email lain',
        ]);
        
        // Update data user
        User::where('id_user', $user->id_user)->update([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
        ]);
        
        // 🔥 VALIDASI UPDATE PASSWORD DENGAN VERIFIKASI PASSWORD LAMA
        if ($request->filled('password') || $request->filled('current_password') || $request->filled('password_confirmation')) {
            
            // Validasi untuk field password
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6'
            ], [
                'current_password.required' => 'Password saat ini wajib diisi untuk mengubah password',
                'password.required' => 'Password baru wajib diisi',
                'password.min' => 'Password baru minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password baru tidak sesuai',
                'password_confirmation.required' => 'Konfirmasi password baru wajib diisi',
            ]);
            
            // Cek apakah password lama sesuai
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->with('error', 'Password saat ini tidak sesuai!')
                    ->withInput($request->except('current_password', 'password', 'password_confirmation'));
            }
            
            // Cek apakah password baru sama dengan password lama
            if (Hash::check($request->password, $user->password)) {
                return redirect()->back()
                    ->with('error', 'Password baru tidak boleh sama dengan password saat ini!')
                    ->withInput($request->except('current_password', 'password', 'password_confirmation'));
            }
            
            User::where('id_user', $user->id_user)->update([
                'password' => Hash::make($request->password),
            ]);
        }
        
        return redirect()->back()->with('success', 'Profil berhasil diupdate');
    }
}