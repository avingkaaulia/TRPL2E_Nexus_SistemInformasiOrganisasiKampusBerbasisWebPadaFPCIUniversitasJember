<?php
// app/Models/FormField.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $table = 'form_fields';
    protected $primaryKey = 'id_field';
    public $timestamps = false;
    
    protected $fillable = [
        'field_name', 'field_label', 'field_type', 'field_options',
        'is_required', 'placeholder', 'sort_order', 'is_active'
    ];
    
    protected $casts = [
        'field_options' => 'array'
    ];
    
    public static function getActiveFields()
    {
        return self::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();
    }
    
    // Ambil opsi dalam bentuk array
    public function getOptionsArrayAttribute()
    {
        if (empty($this->field_options)) {
            return [];
        }
        
        // Jika sudah dalam bentuk array
        if (is_array($this->field_options)) {
            return $this->field_options;
        }
        
        // Jika JSON
        $options = json_decode($this->field_options, true);
        if (is_array($options)) {
            return $options;
        }
        
        // Jika format teks dengan koma
        return explode(',', $this->field_options);
    }
}