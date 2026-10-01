<!DOCTYPE html>
<html>
<head>
    <title>GIVE ABU - Email Verification</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8fafc;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <!-- Header -->
        <div style="background-color: #006B3F; padding: 30px; text-align: center;">
            <img src="https://abu-endowment.cloud/abu_logo_white_for_email.png" alt="ABU Logo" width="80" style="display:block;margin:0 auto 12px auto;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;">GIVE ABU</h1>
            <p style="color: #d4f0df; margin: 10px 0 0 0; font-size: 14px;">Email Verification</p>
        </div>

        <!-- Content -->
        <div style="padding: 40px 30px;">
            <h2 style="color: #1f2937; margin: 0 0 20px 0; font-size: 20px;">Verify Your Email Address</h2>

            <p style="color: #4b5563; margin: 0 0 20px 0; line-height: 1.6;">
                Thank you for registering with GIVE ABU. To complete your registration, please use the verification code below:
            </p>

            <!-- Verification Code -->
            <div style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); padding: 25px; text-align: center; border-radius: 8px; margin: 30px 0; border: 2px solid #d1d5db;">
                <div style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #1f2937; font-family: 'Courier New', monospace;">
                    {{ $code }}
                </div>
            </div>

            <p style="color: #6b7280; margin: 0 0 20px 0; font-size: 14px;">
                This verification code will expire in <strong>10 minutes</strong>.
            </p>

            <div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <p style="color: #92400e; margin: 0; font-size: 14px;">
                    <strong>Security Notice:</strong> If you didn't request this verification code, please ignore this email and contact our support team immediately.
                </p>
            </div>

            <p style="color: #4b5563; margin: 30px 0 0 0; line-height: 1.6;">
                Thank you for supporting GIVE ABU. Together, we can make a difference in education and community development.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="color: #6b7280; margin: 0; font-size: 12px;">
                &copy; {{ date('Y') }} GIVE ABU. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
