<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id_setting';
    
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'created_at',
        'updated_at'
    ];
    
    public $timestamps = true;
    
    // Helper untuk mendapatkan nilai setting
    public static function get($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }
    
    // Helper untuk menyimpan setting
    public static function set($key, $value, $type = 'text')
    {
        $setting = self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'setting_type' => $type]
        );
        return $setting;
    }
     // 🔥 TAMBAH METHOD INI: Cek apakah komentar aktif
    public static function isCommentsEnabled()
    {
        $value = self::get('comments_enabled', 'true');
        return $value === 'true' || $value === '1' || $value === 1;
    }
    
    // 🔥 TAMBAH METHOD INI: Toggle komentar
    public static function toggleComments($status)
    {
        $value = $status ? 'true' : 'false';
        return self::set('comments_enabled', $value, 'boolean');
    }
}