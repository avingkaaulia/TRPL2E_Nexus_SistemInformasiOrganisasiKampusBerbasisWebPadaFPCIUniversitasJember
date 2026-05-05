<?php
// app/Http/Controllers/WritingController.php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Comment;
class WritingController extends Controller
{
    // Halaman Writings dengan Carousel
    public function index(Request $request)
    {
        try {
            // 🔥 CAROUSEL UNTUK HALAMAN WRITINGS
            $carousel = Post::where('status', 'publish')
                ->whereHas('category', function($q) {
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published', 'desc')
                ->get();
            
            // Sub-kategori writings: Foreign Policy, Technology, Economy, Security
            $subCategories = PostCategory::whereIn('category_name', ['Foreign Policy', 'Technology', 'Economy', 'Security'])->get();
            
            // Query posts - hanya dari sub-kategori writings
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->whereIn('id_post_category', $subCategories->pluck('id_category'));
            
            // Search
            $search = $request->get('search');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            // Hot topics
            $hotTopics = (clone $query)->orderBy('date_published', 'desc')->take(6)->get();
            
            // Posts dengan pagination
            $posts = $query->orderBy('date_published', 'desc')->paginate(8);
            
            // Filter kategori yang dipilih
            $currentCategory = null;
            if ($request->get('category')) {
                $currentCategory = PostCategory::find($request->get('category'));
            }
            
            return view('writings.index', compact('carousel', 'subCategories', 'hotTopics', 'posts', 'search', 'currentCategory'));
            
        } catch (\Exception $e) {
            Log::error('WritingController@index error: ' . $e->getMessage());
            return view('writings.index', [
                'carousel' => collect(),
                'subCategories' => collect(),
                'hotTopics' => collect(),
                'posts' => collect()
            ]);
        }
    }
    
    // Filter berdasarkan sub-kategori
    public function category(Request $request, $categoryId)
    {
        try {
            // 🔥 CAROUSEL TETAP MUNCUL
            $carousel = Post::where('status', 'publish')
                ->whereHas('category', function($q) {
                    $q->where('category_name', 'carousel');
                })
                ->orderBy('date_published', 'desc')
                ->get();
            
            $currentCategory = PostCategory::findOrFail($categoryId);
            $subCategories = PostCategory::whereIn('category_name', ['Foreign Policy', 'Technology', 'Economy', 'Security'])->get();
            
            $search = $request->get('search');
            
            $query = Post::with(['category', 'user'])
                ->where('status', 'publish')
                ->where('post_type', 'post')
                ->where('id_post_category', $categoryId);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }
            
            $hotTopics = (clone $query)->orderBy('date_published', 'desc')->take(6)->get();
            $posts = $query->orderBy('date_published', 'desc')->paginate(8);
            
            return view('writings.index', compact('carousel', 'subCategories', 'hotTopics', 'posts', 'search', 'currentCategory'));
            
        } catch (\Exception $e) {
            return redirect()->route('writings');
        }
    }
    
    /// Detail postingan writings
public function show($id)
{
    try {
        $post = Post::with(['category', 'user', 'gallery', 'comments'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->findOrFail($id);
        
        $relatedPosts = Post::with(['category', 'user'])
            ->where('status', 'publish')
            ->where('post_type', 'post')
            ->where('id_post_category', $post->id_post_category)
            ->where('id_post', '!=', $id)
            ->take(3)
            ->get();
        
        $comments = Comment::where('id_post', $id)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Gunakan view show.blade.php yang sama
        return view('show', compact('post', 'relatedPosts', 'comments'));
        
    } catch (\Exception $e) {
        abort(404, 'Post not found');
    }
}
}