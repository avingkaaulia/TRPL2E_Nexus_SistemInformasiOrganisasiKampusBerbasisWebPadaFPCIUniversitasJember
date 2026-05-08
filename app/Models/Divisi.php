<?php
// app/Models/Divisi.php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisi';
    protected $primaryKey = 'id_divisi';
    public $timestamps = false;

    protected $fillable = ['nama_divisi'];

    public function anggota()
    {
        return $this->hasMany(Anggota::class, 'id_divisi', 'id_divisi');
    }
}