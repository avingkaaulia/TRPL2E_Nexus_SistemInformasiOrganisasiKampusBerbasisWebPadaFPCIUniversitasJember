<?php
// app/Http/Controllers/Admin/AdminContactController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Menu;
use App\Models\Post;

class AdminContactController extends Controller
{
    // Halaman utama kelola kontak
    public function index()
    {
        $contact = Contact::first();
        return view('admin.contact.index', compact('contact'));
    }
    
    // Form edit kontak
    public function edit()
    {
        $contact = Contact::first();
        if (!$contact) {
            $contact = new Contact();
        }
        return view('admin.contact.edit', compact('contact'));
    }
    
    // Update kontak
    public function update(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'instagram' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'youtube' => 'nullable|string|max:100',
            'x' => 'nullable|string|max:100',
        ]);
        
        $contact = Contact::first();
        
        if ($contact) {
            $contact->update([
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'instagram' => $request->instagram,
                'linkedin' => $request->linkedin,
                'tiktok' => $request->tiktok,
                'youtube' => $request->youtube,
                'x' => $request->x,
            ]);
        } else {
            Contact::create([
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'instagram' => $request->instagram,
                'linkedin' => $request->linkedin,
                'tiktok' => $request->tiktok,
                'youtube' => $request->youtube,
                'x' => $request->x,
            ]);
        }
        
        return redirect()->route('admin.contact.index')
            ->with('success', 'Informasi kontak berhasil diperbarui!');
    }
}