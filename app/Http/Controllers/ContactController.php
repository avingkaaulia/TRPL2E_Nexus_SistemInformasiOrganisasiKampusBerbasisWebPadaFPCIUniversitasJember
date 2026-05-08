<?php
// app/Http/Controllers/ContactController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Menu;
use App\Models\Post;

class ContactController extends Controller
{
    public function index()
    {
        // 🔥 Ambil data contact dari database
        $contact = Contact::first();
        
        // 🔥 Ambil menu untuk navbar & footer
        $menus = Menu::where('id_menu_parent', 0)->get();
        
        
    // 🔥 CAROUSEL CONTACT - khusus kategori carousel_contact
    $carousel = Post::where('status', 'publish')
        ->whereHas('category', function($q) {
            $q->where('category_name', 'carousel_contact');
        })
        ->orderBy('date_published', 'desc')
        ->get();
    
    if ($carousel->isEmpty()) {
        $carousel = Post::where('status', 'publish')
            ->whereHas('category', function($q) {
                $q->where('category_name', 'carousel');
            })
            ->orderBy('date_published', 'desc')
            ->get();
    }
        
        return view('contact.index', compact('contact', 'menus', 'carousel'));
    }
}