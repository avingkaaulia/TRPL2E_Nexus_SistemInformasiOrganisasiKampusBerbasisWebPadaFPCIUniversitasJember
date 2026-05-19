<?php
// app/Http/Controllers/Auth/ForgotPasswordController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // Tampilkan form lupa password
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }
    
    // Proses kirim link reset password
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar dalam sistem'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        // Generate token reset
        $token = Str::random(60);
        $expires = Carbon::now()->addMinutes(60); // Token berlaku 60 menit
        
        $user->reset_token = $token;
        $user->reset_token_expires = $expires;
        $user->save();
        
        // Kirim email reset password
        try {
            $this->sendResetEmail($user, $token);
            
            return redirect()->route('login')
                ->with('success', 'Link reset password telah dikirim ke email Anda. Cek inbox atau spam.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email. Silahkan coba lagi.');
        }
    }
    
    // Kirim email reset password
    private function sendResetEmail($user, $token)
    {
        $resetUrl = route('password.reset', ['token' => $token]);
        
        $data = [
            'nama' => $user->nama,
            'email' => $user->email,
            'reset_url' => $resetUrl,
            'expires_in' => 60
        ];
        
        Mail::send('emails.reset-password', $data, function($message) use ($user) {
            $message->to($user->email, $user->nama)
                    ->subject('Reset Password - FPCI UNEJ');
        });
    }
    
    // Tampilkan form reset password
    public function showResetForm($token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expires', '>', Carbon::now())
            ->first();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Link reset password tidak valid atau sudah kadaluarsa.');
        }
        
        return view('auth.reset-password', compact('token'));
    }
    
    // Proses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required'
        ], [
            'password.required' => 'Password baru wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai'
        ]);
        
        $user = User::where('reset_token', $request->token)
            ->where('reset_token_expires', '>', Carbon::now())
            ->first();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Link reset password tidak valid atau sudah kadaluarsa.');
        }
        
        // Update password
        $user->password = bcrypt($request->password);
        $user->reset_token = null;
        $user->reset_token_expires = null;
        $user->save();
        
        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silahkan login dengan password baru Anda.');
    }
}