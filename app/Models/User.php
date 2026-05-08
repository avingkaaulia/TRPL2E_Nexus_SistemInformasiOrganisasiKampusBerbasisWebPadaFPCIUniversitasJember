<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Anggota;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama',
        'id_role',
        'tanggal_daftar'
    ];
       public function anggota()
    {
        return $this->hasOne(Anggota::class, 'id_user', 'id_user');
    }

    protected $hidden = [
        'password',
    ];

    // 🔗 RELASI KE POSTS
    public function posts()
    {
        return $this->hasMany(Post::class, 'id_user');
    }


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
}
