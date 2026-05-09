<?php
// app/Http/Controllers/ContactController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Menu;
use App\Models\Post;

class ContactController extends Controller
{
   // Di ContactController.php
public function index()
{
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
    
    foreach ($carousel as $item) {
        $item->image_url = $this->getImageUrl($item->featured_image_path);
    }
    
    $contact = Contact::first();
    $menus = Menu::where('id_menu_parent', 0)->get();
    
    return view('contact.index', compact('carousel', 'contact', 'menus'));
}

private function getImageUrl($path)
{
    if (!$path) return asset('assets/img/default-image.jpg');
    
    if (file_exists(storage_path('app/public/' . $path))) {
        return asset('storage/' . $path);
    }
    if (file_exists(public_path($path))) {
        return asset($path);
    }
    if (file_exists(public_path('assets/' . $path))) {
        return asset('assets/' . $path);
    }
    return asset('assets/img/default-image.jpg');
}
}