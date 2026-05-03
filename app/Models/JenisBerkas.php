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
    
    public function berkasPendaftaran()
    {
        return $this->hasMany(BerkasPendaftaran::class, 'id_jenis');
    }
    
    // Format ukuran file agar mudah dibaca
    public function getMaxSizeReadableAttribute()
    {
        if ($this->max_size >= 1024) {
            return round($this->max_size / 1024, 1) . ' MB';
        }
        return $this->max_size . ' KB';
    }
}