<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use Illuminate\Support\Str;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id_post';
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'content',
        'id_post_category',
        'post_type',
        'id_user',
        'date_published',
        'status',
        'featured_image_path'
    ];

    // 🔹 relasi ke kategori
    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'id_post_category');
    }

    // 🔹 relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // 🔹 relasi ke gallery
    public function gallery()
    {
        return $this->hasMany(PostGallery::class, 'id_post');
    }

    // 🔥 BIAR GAMBAR MUNCUL
    public function getImageAttribute()
    {
        return asset($this->featured_image_path);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'id_post');
    }

    // Ambil nama parent category untuk ditampilkan
public function getParentCategoryNameAttribute()
{
    if ($this->category && $this->category->parent) {
        return $this->category->parent->category_name;
    }
    return $this->category->category_name ?? 'Uncategorized';
}

// Ambil ID parent category
public function getParentCategoryIdAttribute()
{
    if ($this->category && $this->category->parent) {
        return $this->category->parent->id_category;
    }
    return $this->category->id_category ?? null;
}

public function getSlugAttribute()
{
    return Str::slug($this->title);
}

// Untuk mendapatkan URL page
public function getPageUrlAttribute()
{
    return route('page.show', $this->slug);
}
}