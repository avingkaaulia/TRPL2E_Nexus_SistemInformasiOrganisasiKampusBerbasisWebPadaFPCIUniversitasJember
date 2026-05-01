<?php
// app/Models/PendaftaranModel.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';
    protected $primaryKey = 'id_pendaftaran';
    public $timestamps = false;
    
    protected $fillable = [
        'nama', 'email', 'no_hp', 'alamat', 'nim', 'jurusan', 
        'fakultas', 'alasan', 'status', 'tanggal_daftar', 'id_periode'
    ];
}

