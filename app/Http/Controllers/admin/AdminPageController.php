<?php
// app/Http/Controllers/Admin/AdminPageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    public function index()
    {
        $pages = Post::where('post_type', 'page')
            ->orderBy('title')
            ->paginate(15);
        
        return view('admin.pages.index', compact('pages'));
    }
}