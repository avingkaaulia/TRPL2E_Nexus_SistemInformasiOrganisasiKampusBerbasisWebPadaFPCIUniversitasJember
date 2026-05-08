<?php
// app/Models/Anggota.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_divisi',
        'jabatan',
        'periode',
        'foto',
        'no_urut',
        'link'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id_divisi');
    }
}