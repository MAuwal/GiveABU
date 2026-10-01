<!DOCTYPE html>
<html>
<head>
    <title>GIVE ABU - Verify Your Email</title>
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
            <h2 style="color: #1f2937; margin: 0 0 16px 0; font-size: 22px;">Verify Your Email Address</h2>

            @if($recipientName)
            <p style="color: #4b5563; margin: 0 0 16px 0; line-height: 1.6;">
                Hi <strong>{{ $recipientName }}</strong>,
            </p>
            @endif

            <p style="color: #4b5563; margin: 0 0 24px 0; line-height: 1.6;">
                Thank you for registering with GIVE ABU. Please click the button below to verify your email address
                and complete your account setup.
            </p>

            <!-- Verification Button -->
            <div style="text-align: center; margin: 32px 0;">
                <a href="{{ $verificationUrl }}"
                   style="display: inline-block; background-color: #006B3F; color: #ffffff; text-decoration: none;
                          padding: 14px 40px; border-radius: 6px; font-size: 16px; font-weight: bold;
                          letter-spacing: 0.3px;">
                    Verify My Email
                </a>
            </div>

            <p style="color: #6b7280; margin: 0 0 16px 0; font-size: 14px; line-height: 1.6;">
                If the button does not work, copy and paste this link into your browser:
            </p>
            <p style="margin: 0 0 24px 0;">
                <a href="{{ $verificationUrl }}" style="color: #006B3F; font-size: 13px; word-break: break-all;">
                    {{ $verificationUrl }}
                </a>
            </p>

            <div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 24px 0;">
                <p style="color: #92400e; margin: 0; font-size: 14px;">
                    <strong>Note:</strong> This link will remain valid. If you did not create a GIVE ABU account,
                    you can safely ignore this email.
                </p>
            </div>

            <p style="color: #4b5563; margin: 24px 0 0 0; line-height: 1.6;">
                Thank you for supporting GIVE ABU. Together, we can make a difference in education and
                community development at Ahmadu Bello University.
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
