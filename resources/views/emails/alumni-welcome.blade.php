<!DOCTYPE html>
<html>
<head>
    <title>Welcome to GIVE ABU</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="background-color: #006B3F; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
            <img src="https://abu-endowment.cloud/abu_logo_white_for_email.png" alt="ABU Logo" width="80" style="display:block;margin:0 auto 12px auto;">
            <h1 style="color: #ffffff; margin: 0; font-size: 22px;">GIVE ABU</h1>
            <p style="color: #d4f0df; margin: 5px 0 0 0; font-size: 13px;">Ahmadu Bello University, Zaria</p>
        </div>
        <div style="background: #ffffff; padding: 30px; border: 1px solid #eee; border-top: none; border-radius: 0 0 8px 8px;">
            <h2 style="color: #006B3F;">Welcome, {{ $donor->name }}!</h2>
            <p>We are pleased to inform you that your alumni record has been successfully created on the GIVE ABU platform.</p>

            <p>An account has been created for you. You can login using the following credentials:</p>

            <div style="background: #f0f9f4; border-left: 4px solid #006B3F; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Username:</strong> {{ $username }}</p>
                <p style="margin: 8px 0 0 0;"><strong>Password:</strong> {{ $password }}</p>
            </div>

            <p>Please login and change your password as soon as possible.</p>

            <p><a href="{{ url('/') }}" style="color: #006B3F;">Visit our website</a></p>

            <p>Thank you for your continued support!</p>

            <p>Best regards,<br><strong>GIVE ABU Team</strong></p>
        </div>
        <div style="text-align: center; padding: 15px; font-size: 12px; color: #999;">
            <p>&copy; {{ date('Y') }} GIVE ABU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
