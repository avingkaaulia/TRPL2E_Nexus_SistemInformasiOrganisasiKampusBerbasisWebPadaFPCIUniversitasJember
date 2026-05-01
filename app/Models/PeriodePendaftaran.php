<?php
// app/Models/PeriodePendaftaran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PeriodePendaftaran extends Model
{
    protected $table = 'periode_pendaftaran';
    protected $primaryKey = 'id_periode';
    public $timestamps = false;
    
    protected $fillable = [
        'tahun_ajaran', 'nama_periode', 'tanggal_mulai', 
        'tanggal_selesai', 'is_active', 'kuota', 'deskripsi'
    ];
    
    public function getJumlahPendaftarAttribute()
    {
        return Pendaftaran::where('id_periode', $this->id_periode)->count();
    }
    
    public function getSisaKuotaAttribute()
    {
        return $this->kuota - $this->jumlah_pendaftar;
    }
    
    public function isAktif()
    {
        $now = date('Y-m-d');
        return $this->is_active && $now >= $this->tanggal_mulai && $now <= $this->tanggal_selesai;
    }
}