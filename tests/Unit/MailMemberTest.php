<?php

namespace Tests\Unit;

use App\Mail\AcceptedMemberMail;
use App\Mail\RejectedMemberMail;
use Tests\DatabaseTestCase;

class MailMemberTest extends DatabaseTestCase
{
    // ---- AcceptedMemberMail ----
    public function test_accepted_mail_menyimpan_data_dari_constructor()
    {
        $pendaftar = $this->buatPendaftar(['nama' => 'Avingka']);

        $mail = new AcceptedMemberMail($pendaftar, '240123', 'rahasia1');

        $this->assertEquals('Avingka', $mail->pendaftaran->nama);
        $this->assertEquals('240123', $mail->username);
        $this->assertEquals('rahasia1', $mail->password);
    }

    public function test_accepted_mail_subjek_sesuai()
    {
        $mail = (new AcceptedMemberMail($this->buatPendaftar(), 'u', 'p'))->build();

        $this->assertEquals('Selamat! Anda Diterima Menjadi Anggota FPCI UNEJ', $mail->subject);
    }

    public function test_accepted_mail_memakai_view_accepted_member()
    {
        $mail = (new AcceptedMemberMail($this->buatPendaftar(), 'u', 'p'))->build();

        $this->assertEquals('emails.accepted-member', $mail->view);
    }

    // ---- RejectedMemberMail ----
    public function test_rejected_mail_menyimpan_data_pendaftar()
    {
        $pendaftar = $this->buatPendaftar(['nama' => 'Siti']);

        $mail = new RejectedMemberMail($pendaftar);

        $this->assertEquals('Siti', $mail->pendaftaran->nama);
    }

    public function test_rejected_mail_subjek_sesuai()
    {
        $mail = (new RejectedMemberMail($this->buatPendaftar()))->build();

        $this->assertEquals('Informasi Hasil Seleksi - FPCI UNEJ', $mail->subject);
    }

    public function test_rejected_mail_memakai_view_rejected_member()
    {
        $mail = (new RejectedMemberMail($this->buatPendaftar()))->build();

        $this->assertEquals('emails.rejected-member', $mail->view);
    }
}
