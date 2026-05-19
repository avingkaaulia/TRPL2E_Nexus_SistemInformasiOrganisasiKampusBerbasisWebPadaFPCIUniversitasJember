{{-- resources/views/emails/accepted-member.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Selamat! Anda Diterima</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #5C6844; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .credentials { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
        .btn { background: #5C6844; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
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
            <p>Selamat! Berdasarkan hasil seleksi, Anda dinyatakan <strong>DITERIMA</strong> sebagai anggota FPCI UNEJ.</p>
            
            <div class="credentials">
                <h4>Informasi Login:</h4>
                <p><strong>Username:</strong> {{ $username }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
                <p style="margin-top: 10px; font-size: 12px; color: #856404;">
                    <i>⚠️ Disarankan untuk segera mengganti password setelah login melalui halaman Profil.</i>
                </p>
            </div>
            
            <p>Anda dapat login melalui website resmi FPCI UNEJ:</p>
            <p style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn">Login Sekarang</a>
            </p>
            
            <p>Salam hangat,<br>
            <strong>FPCI UNEJ</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} FPCI UNEJ. All rights reserved.</p>
        </div>
    </div>
</body>
</html>