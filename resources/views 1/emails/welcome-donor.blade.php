<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to GIVE ABU</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #f9f9f9; }
        .header { background-color: #006B3F; padding: 30px 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .header h1 { color: #ffffff; margin: 10px 0 0 0; font-size: 26px; }
        .content { background-color: #ffffff; padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
        .credentials { background-color: #f0f9f4; border-left: 4px solid #006B3F; padding: 15px; margin: 20px 0; }
        .button { display: inline-block; padding: 10px 20px; background-color: #006B3F; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://abu-endowment.cloud/abu_logo_white_for_email.png" alt="ABU Logo" width="80" style="display:block;margin:0 auto 12px auto;">
            <h1>Welcome to GIVE ABU</h1>
        </div>
        <div class="content">
            <p>Dear {{ $donor->name }},</p>

            <p>Thank you for registering with the Ahmadu Bello University Endowment Foundation. We are thrilled to have you as part of our community dedicated to supporting the university's growth and development.</p>

            <p>An account has been created for you to track your donations and manage your profile. Please find your login credentials below:</p>

            <div class="credentials">
                <p><strong>Username:</strong> {{ $username }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
            </div>

            <p>We recommend that you change your password after your first login for security purposes.</p>

            <center>
                <a href="{{ config('app.url') }}/login" class="button">Login to Dashboard</a>
            </center>

            <p>If you have any questions or need assistance, please do not hesitate to contact our support team.</p>

            <p>Best regards,<br>The GIVE ABU Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} GIVE ABU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
