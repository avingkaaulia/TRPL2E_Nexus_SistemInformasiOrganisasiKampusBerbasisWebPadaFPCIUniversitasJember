<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Halaman Login
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    // Proses Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        
        if (Auth::attempt($credentials, $request->remember)) {
            $user = Auth::user();
            
            // Redirect berdasarkan role
            if ($user->id_role == 1) {
                return redirect()->route('admin.dashboard')->with('success', 'Selamat datang Admin!');
            } else {
                return redirect()->route('home')->with('success', 'Selamat datang ' . $user->nama . '!');
            }
        }
        
        return back()->with('error', 'Email atau password salah!')->withInput($request->only('email'));
    }
    
    // Halaman Register
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    
    // Proses Register
    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);
        
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'id_role' => 2, // Default role: anggota
            'tanggal_daftar' => now()
        ]);
        
        // Auto login setelah register
        Auth::login($user);
        
        return redirect()->route('home')->with('success', 'Pendaftaran berhasil! Selamat datang ' . $user->nama);
    }
    
    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'Berhasil logout');
    }
}