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

    public function children()
    {
        return $this->hasMany(Menu::class, 'id_menu_parent', 'id_menu');
    }
}