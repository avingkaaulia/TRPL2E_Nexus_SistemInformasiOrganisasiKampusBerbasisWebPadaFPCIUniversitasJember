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
        'field_name', 'field_label', 'field_type', 
        'is_required', 'placeholder', 'sort_order', 'is_active'
    ];
    
    public static function getActiveFields()
    {
        return self::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();
    }
}