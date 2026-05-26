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
    
    
    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'Berhasil logout');
    }
}