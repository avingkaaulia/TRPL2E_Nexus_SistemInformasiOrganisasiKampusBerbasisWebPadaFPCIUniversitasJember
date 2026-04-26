<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}