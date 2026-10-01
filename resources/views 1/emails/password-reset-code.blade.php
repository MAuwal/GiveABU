<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; background-color: #006B3F; padding: 30px 20px; border-radius: 10px 10px 0 0;">
            <img src="https://abu-endowment.cloud/abu_logo_white_for_email.png" alt="ABU Logo" style="max-width: 80px; height: auto; display: block; margin: 0 auto 12px auto;">
            <h1 style="color: #ffffff; margin: 0; font-size: 22px;">GIVE ABU</h1>
            <p style="color: #d4f0df; margin: 5px 0 0 0; font-size: 13px;">Ahmadu Bello University, Zaria</p>
        </div>

        <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
            <h2 style="color: #006B3F;">Password Reset Request</h2>

            <p>Hello {{ $name }},</p>

            <p>You have requested to reset your password. Use the code below to verify your identity:</p>

            <div style="text-align: center; margin: 30px 0;">
                <div style="display: inline-block; background: #006B3F; color: white; padding: 15px 30px; border-radius: 8px; font-size: 32px; font-weight: bold; letter-spacing: 5px;">
                    {{ $code }}
                </div>
            </div>

            <p><strong>This code will expire in 20 minutes.</strong></p>

            <p>If you did not request this password reset, please ignore this email.</p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>GIVE ABU Team</strong>
            </p>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} GIVE ABU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
