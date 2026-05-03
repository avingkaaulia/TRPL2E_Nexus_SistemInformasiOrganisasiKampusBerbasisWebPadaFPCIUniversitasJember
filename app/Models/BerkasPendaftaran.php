<?php
// app/Models/BerkasPendaftaran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasPendaftaran extends Model
{
    protected $table = 'berkas_pendaftaran';
    protected $primaryKey = 'id_berkas';
    public $timestamps = false;
    
    protected $fillable = [
        'id_pendaftaran', 'id_jenis', 'file_path'
    ];
    
    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'id_pendaftaran');
    }
    
    public function jenisBerkas()
    {
        return $this->belongsTo(JenisBerkas::class, 'id_jenis');
    }
}