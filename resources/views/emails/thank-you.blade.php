<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Donation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            text-align: center;
            background-color: #006B3F;
            padding: 30px 20px;
        }
        .header h1 {
            color: #ffffff;
            margin: 10px 0 0 0;
            font-size: 28px;
        }
        .header p {
            color: #d4f0df;
            margin: 5px 0 0 0;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #006B3F;
            font-size: 22px;
            margin-top: 0;
        }
        .donation-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #006B3F;
        }
        .donation-details p {
            margin: 10px 0;
            font-size: 16px;
        }
        .donation-details strong {
            color: #006B3F;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #006B3F;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .signature {
            margin-top: 30px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $logoUrl ?? 'https://abu-endowment.cloud/abu_logo_white_for_email.png' }}" alt="ABU Logo" width="80" style="display:block;margin:0 auto 12px auto;">
            <h1>GIVE ABU</h1>
            <p>Ahmadu Bello University, Zaria</p>
        </div>

        <div class="content">
            <h2>Dear {{ $donorName }},</h2>

            <p>On behalf of the entire Ahmadu Bello University community, we extend our heartfelt gratitude for your generous donation to GIVE ABU.</p>

            <p>Your contribution of <strong>₦{{ $amount }}</strong> to the <strong>{{ $donationType }}</strong> will make a significant impact on the lives of students, researchers, and the broader ABU community.</p>

            <div class="donation-details">
                <h3 style="margin-top: 0; color: #006B3F;">Donation Details</h3>
                <p><strong>Amount:</strong> <span class="amount">₦{{ $amount }}</span></p>
                <p><strong>Donation Type:</strong> {{ $donationType }}</p>
                @if($projectName !== 'GIVE ABU')
                <p><strong>Project:</strong> {{ $projectName }}</p>
                @endif
                <p><strong>Payment Reference:</strong> {{ $reference }}</p>
                <p><strong>Date:</strong> {{ $donationDate->format('F d, Y \a\t h:i A') }}</p>
            </div>

            <p>Your support helps us:</p>
            <ul>
                <li>Provide scholarships to deserving students</li>
                <li>Fund groundbreaking research projects</li>
                <li>Improve infrastructure and learning facilities</li>
                <li>Support community development initiatives</li>
            </ul>

            <p>Every contribution, no matter the size, plays a crucial role in building a brighter future for ABU and the generations to come. Your generosity is truly appreciated and will leave a lasting legacy.</p>

            <p>We will keep you updated on how your donation is being used to make a positive impact. Thank you for being part of the ABU family and for your commitment to excellence in education.</p>

            <div class="signature">
                <p>With sincere appreciation,</p>
                <p><strong>The GIVE ABU Team</strong><br>
                Ahmadu Bello University, Zaria<br>
                Kaduna State, Nigeria</p>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>For inquiries, please contact us at: endowment@abu.edu.ng</p>
            <p>&copy; {{ date('Y') }} GIVE ABU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
