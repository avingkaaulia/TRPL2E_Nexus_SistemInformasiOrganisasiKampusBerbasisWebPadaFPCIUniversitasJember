<?php
// app/Mail/AcceptedMemberMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AcceptedMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $username;
    public $password;

    public function __construct($pendaftaran, $username, $password)
    {
        $this->pendaftaran = $pendaftaran;
        $this->username = $username;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Selamat! Anda Diterima Menjadi Anggota FPCI UNEJ')
                    ->view('emails.accepted-member');
    }
}