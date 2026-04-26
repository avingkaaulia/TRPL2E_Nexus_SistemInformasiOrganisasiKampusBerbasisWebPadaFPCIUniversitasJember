<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostGallery extends Model
{
    protected $table = 'post_gallery';
    protected $primaryKey = 'id_gallery';
    public $timestamps = false;

    public function post()
    {
        return $this->belongsTo(Post::class, 'id_post');
    }
}