<?php
// app/Models/Comment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model 
{
    protected $table = 'comments';
    protected $primaryKey = 'id_comment';
    public $timestamps = false;
    
    protected $fillable = [
        'id_post', 
        'nama_pengunjung', 
        'email', 
        'isi_komentar', 
        'tanggal',
        'reply',
        'reply_by',
        'reply_date',
        'is_replied',
        'status'
    ];
    
    protected $casts = [
        'is_replied' => 'boolean',
        'reply_date' => 'datetime'
    ];
    
    public function post() 
    {
        return $this->belongsTo(Post::class, 'id_post');
    }
    
    // 🔥 SCOPE UNTUK KOMENTAR YANG DISETUJUI
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    // 🔥 SCOPE UNTUK KOMENTAR PENDING
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    // 🔥 SCOPE UNTUK KOMENTAR DITOLAK
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}