<?php
// app/Models/Contact.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contact';
    protected $primaryKey = 'id_contact';
    public $timestamps = false;

    protected $fillable = [
        'email', 'no_hp', 'alamat', 'instagram', 'linkedin', 'tiktok', 'youtube', 'x'
    ];
}