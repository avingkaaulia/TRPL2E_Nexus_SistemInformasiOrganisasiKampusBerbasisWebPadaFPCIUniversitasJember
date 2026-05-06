<?php
// app/Models/PostCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory;
    
    protected $table = 'post_category';
    protected $primaryKey = 'id_category';
    public $timestamps = false;
    
    protected $fillable = ['category_name', 'parent_id'];
    
    // Relasi ke parent (kategori induk)
    public function parent()
    {
        return $this->belongsTo(PostCategory::class, 'parent_id', 'id_category');
    }
    
    // Relasi ke children (sub-kategori)
    public function children()
    {
        return $this->hasMany(PostCategory::class, 'parent_id', 'id_category');
    }
    
    // Ambil nama parent category, jika tidak ada parent maka nama sendiri
    public function getDisplayCategoryNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->category_name;
        }
        return $this->category_name;
    }
    
    // Ambil semua ID kategori termasuk sub-kategorinya
    public function getAllChildrenIds()
    {
        $ids = [$this->id_category];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        return $ids;
    }
    
    // Ambil semua kategori utama
    public static function getMainCategories()
    {
        return self::whereNull('parent_id')->orWhere('parent_id', 0)->get();
    }
}