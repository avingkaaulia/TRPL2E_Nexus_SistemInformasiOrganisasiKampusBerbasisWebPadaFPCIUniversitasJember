<?php
// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'description',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    // Relasi ke user (optional, biar bisa join)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    // Label untuk aksi (biar tampilannya cantik)
    public function getActionLabelAttribute()
    {
        $labels = [
            'login'         => 'Login',
            'logout'        => 'Logout',
            'create'        => 'Tambah Data',
            'update'        => 'Edit Data',
            'delete'        => 'Hapus Data',
            'approve'       => 'Setujui',
            'reject'        => 'Tolak',
            'upload'        => 'Upload',
            'reply'         => 'Balas', 
        ];

        return $labels[$this->action] ?? ucfirst($this->action);
    }

    // Warna badge berdasarkan aksi
    public function getActionColorAttribute()
    {
        $colors = [
            'login'     => 'success',
            'logout'    => 'secondary',
            'create'    => 'primary',
            'update'    => 'warning',
            'delete'    => 'danger',
            'approve'   => 'success',
            'reject'    => 'danger',
            'upload'    => 'info',
            'reply'         => 'info',
        ];

        return $colors[$this->action] ?? 'secondary';
    }
}