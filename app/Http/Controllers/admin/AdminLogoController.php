<?php
// app/Http/Controllers/Admin/AdminLogoController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminLogoController extends Controller
{
    public function index()
    {
        $logo = Setting::get('site_logo', 'assets/img/logo.png');
        $favicon = Setting::get('site_favicon', 'assets/img/favicon.ico');
        
        return view('admin.logo.index', compact('logo', 'favicon'));
    }
    
    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:5048'
        ]);
        
        $oldLogo = Setting::get('site_logo', 'assets/img/logo.png');
        
        // Upload new logo
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');
        
        // Delete old logo if not default
        if ($oldLogo != 'assets/img/logo.png' && file_exists(public_path($oldLogo))) {
            unlink(public_path($oldLogo));
        }
        
        Setting::set('site_logo', 'storage/' . $path, 'image');
        
        return redirect()->route('admin.logo.index')
            ->with('success', 'Logo berhasil diperbarui!');
    }
    
    public function updateFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png,jpg,jpeg|max:512'
        ]);
        
        $oldFavicon = Setting::get('site_favicon', 'assets/img/favicon.ico');
        
        // Upload new favicon
        $file = $request->file('favicon');
        $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');
        
        // Delete old favicon if not default
        if ($oldFavicon != 'assets/img/favicon.ico' && file_exists(public_path($oldFavicon))) {
            unlink(public_path($oldFavicon));
        }
        
        Setting::set('site_favicon', 'storage/' . $path, 'image');
        
        return redirect()->route('admin.logo.index')
            ->with('success', 'Favicon berhasil diperbarui!');
    }
    
    public function resetLogo()
    {
        $oldLogo = Setting::get('site_logo', 'assets/img/logo.png');
        
        if ($oldLogo != 'assets/img/logo.png' && file_exists(public_path($oldLogo))) {
            unlink(public_path($oldLogo));
        }
        
        Setting::set('site_logo', 'assets/img/logo.png', 'image');
        
        return redirect()->route('admin.logo.index')
            ->with('success', 'Logo berhasil direset ke default!');
    }
}