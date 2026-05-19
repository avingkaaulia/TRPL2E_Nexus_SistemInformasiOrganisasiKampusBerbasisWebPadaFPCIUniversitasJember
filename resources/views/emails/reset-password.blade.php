{{-- resources/views/emails/reset-password.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - FPCI UNEJ</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #5C6844; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { background: #5C6844; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
        .warning { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>FPCI UNEJ</h2>
            <p>Reset Password</p>
        </div>
        <div class="content">
            <h3>Yth. {{ $nama }}</h3>
            <p>Kami menerima permintaan untuk mereset password akun FPCI UNEJ Anda.</p>
            
            <p>Klik tombol di bawah ini untuk mereset password:</p>
            <p style="text-align: center;">
                <a href="{{ $reset_url }}" class="button">Reset Password</a>
            </p>
            
            <div class="warning">
                <strong>⚠️ Perhatian:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Link ini hanya berlaku selama {{ $expires_in }} menit</li>
                    <li>Jika tidak melakukan permintaan reset password, abaikan email ini</li>
                    <li>Jangan berikan link ini kepada siapapun</li>
                </ul>
            </div>
            
            <p>Atau salin link berikut ke browser Anda:</p>
            <p style="word-break: break-all; font-size: 12px; background: #eee; padding: 10px; border-radius: 5px;">
                {{ $reset_url }}
            </p>
            
            <p>Salam,<br>
            <strong>FPCI UNEJ</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} FPCI UNEJ. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
        </div>
    </div>
</body>
</html>