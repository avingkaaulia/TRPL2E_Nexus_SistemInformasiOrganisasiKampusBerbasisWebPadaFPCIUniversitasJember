<?php
// app/Http/Controllers/Admin/AdminTitleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class AdminTitleController extends Controller
{
    public function index()
    {
        $siteTitle = Setting::getSiteTitle();
        return view('admin.title.index', compact('siteTitle'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'site_title' => 'required|string|max:100'
        ], [
            'site_title.required' => 'Title website wajib diisi',
            'site_title.max' => 'Title website maksimal 100 karakter'
        ]);
        
        Setting::setSiteTitle($request->site_title);
        
        return redirect()->route('admin.title.index')
            ->with('success', 'Title website berhasil diperbarui!');
    }
}