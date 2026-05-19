<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    
    protected $fillable = [
    'username',
    'email',
    'password',
    'nama',
    'id_role',
    'tanggal_daftar',
    'reset_token',
    'reset_token_expires'
];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    public $timestamps = false;
    
    // Relasi ke role
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }
    
    // Cek apakah user admin
    public function isAdmin()
    {
        return $this->id_role == 1;
    }
    
    // Cek apakah user anggota
    public function isAnggota()
    {
        return $this->id_role == 2;
    }
}