<?php
// app/Models/Pendaftaran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';
    protected $primaryKey = 'id_pendaftaran';
    public $timestamps = false;
    
    // 🔥 GUARDED KOSONG AGAR BISA MENERIMA FIELD DINAMIS
    protected $guarded = [];
    
    public function periode()
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'id_periode');
    }
    
    public function berkas()
    {
        return $this->hasMany(BerkasPendaftaran::class, 'id_pendaftaran');
    }
}