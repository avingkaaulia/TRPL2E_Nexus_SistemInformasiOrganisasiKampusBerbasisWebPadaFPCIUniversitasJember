<?php
// app/Models/Menu.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    public $timestamps = false;
    
    protected $fillable = [
        'menu_label', 'id_menu_parent', 'link'
    ];
    
    // Relasi ke parent menu
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'id_menu_parent', 'id_menu');
    }
    
    // Relasi ke child menu
    public function children()
    {
        return $this->hasMany(Menu::class, 'id_menu_parent', 'id_menu');
    }
    
    // Ambil semua menu utama (parent = 0)
    public static function getMainMenus()
    {
        return self::where('id_menu_parent', 0)->orderBy('id_menu')->get();
    }
    
    // Ambil sub menu dari parent tertentu
    public static function getSubMenus($parentId)
    {
        return self::where('id_menu_parent', $parentId)->orderBy('id_menu')->get();
    }
    
    // Cek apakah menu memiliki sub menu
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }
}