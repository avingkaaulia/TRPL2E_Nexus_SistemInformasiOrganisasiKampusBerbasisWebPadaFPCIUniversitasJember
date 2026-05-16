<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->id_role == 1) {
            return $next($request);
        }
        
        return redirect('/')->with('error', 'Akses ditolak! Anda bukan admin.');
    }
}