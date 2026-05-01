<?php
// app/Models/JenisBerkas.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBerkas extends Model
{
    protected $table = 'jenis_berkas';
    protected $primaryKey = 'id_jenis';
    public $timestamps = false;
    
    protected $fillable = [
        'nama_jenis', 'is_required', 'file_type', 'max_size'
    ];
}