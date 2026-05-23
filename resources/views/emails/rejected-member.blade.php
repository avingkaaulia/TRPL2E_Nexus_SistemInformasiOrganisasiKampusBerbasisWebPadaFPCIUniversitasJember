{{-- resources/views/emails/rejected-member.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Informasi Hasil Seleksi</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
        .btn { background: #5C6844; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>FPCI UNEJ</h2>
            <p>Foreign Policy Community Indonesia - Universitas Jember</p>
        </div>
        <div class="content">
            <h3>Yth. {{ $pendaftaran->nama }}</h3>
            <p>Berdasarkan hasil seleksi administrasi dan wawancara, kami informasikan bahwa Anda dinyatakan <strong>TIDAK DITERIMA</strong> sebagai anggota FPCI UNEJ pada periode ini.</p>
            
            <p>Keputusan ini diambil berdasarkan hasil seleksi yang objektif dan transparan.</p>
            
            <p>Kami sangat mengapresiasi minat dan partisipasi Anda. Jangan berkecil hati, kami mengundang Anda untuk kembali mendaftar pada periode pendaftaran berikutnya.</p>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/') }}" class="btn">Kunjungi Website Kami</a>
            </p>
            
            <p>Salam hangat,<br>
            <strong>FPCI UNEJ</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} FPCI UNEJ. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
        </div>
    </div>
</body>
</html>