<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You — GIVE ABU</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/abu_logo_white.png') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f0fdf4;
            color: #1a2e1a;
        }

        /* ── Animated background ── */
        .bg-gradient {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #047857 70%, #059669 100%);
            overflow: hidden;
        }
        .bg-gradient::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(255,255,255,0.07) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
        }
        .orb {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.06);
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; top: -100px; right: -100px; animation-delay: 0s; }
        .orb-2 { width: 250px; height: 250px; bottom: -50px; left: -80px; animation-delay: 3s; }
        .orb-3 { width: 150px; height: 150px; top: 40%; left: 10%; animation-delay: 5s; }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        /* ── Card ── */
        .card-wrap {
            position: relative; z-index: 1;
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }
        .card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.15);
            width: 100%; max-width: 560px;
            overflow: hidden;
            animation: slideUp 0.7s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Card top band ── */
        .card-top {
            background: linear-gradient(135deg, #064e3b, #059669);
            padding: 2.5rem 2rem 3.5rem;
            text-align: center;
            position: relative;
        }
        .logo-wrap { margin-bottom: 1.5rem; }
        .logo-wrap img { height: 56px; width: auto; filter: brightness(0) invert(1); }

        /* ── Check circle ── */
        .check-circle {
            width: 90px; height: 90px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            border: 3px solid rgba(255,255,255,0.4);
            animation: popIn 0.6s 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.3); }
            to   { opacity: 1; transform: scale(1); }
        }
        .check-circle svg { width: 44px; height: 44px; }
        .check-path {
            stroke-dasharray: 60;
            stroke-dashoffset: 60;
            animation: drawCheck 0.5s 0.9s ease forwards;
        }
        @keyframes drawCheck {
            to { stroke-dashoffset: 0; }
        }

        .card-top h1 {
            color: #fff; font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.5rem;
        }
        .card-top p { color: rgba(255,255,255,0.85); font-size: 0.95rem; }

        /* ── Wave divider ── */
        .wave {
            position: absolute; bottom: -1px; left: 0; right: 0;
            line-height: 0;
        }
        .wave svg { display: block; width: 100%; }

        /* ── Card body ── */
        .card-body { padding: 2rem 2rem 2.5rem; }

        /* ── Amount hero ── */
        .amount-hero {
            text-align: center; margin-bottom: 1.75rem;
        }
        .amount-hero .label {
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.1em; color: #6b7280; margin-bottom: 0.25rem;
        }
        .amount-hero .value {
            font-size: 2.8rem; font-weight: 800; color: #064e3b; line-height: 1;
            animation: countUp 0.6s 0.5s ease both;
        }
        @keyframes countUp {
            from { opacity: 0; transform: scale(0.7); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ── Details grid ── */
        .details {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.75rem;
        }
        .detail-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            gap: 1rem; padding: 0.55rem 0;
        }
        .detail-row:not(:last-child) { border-bottom: 1px solid #d1fae5; }
        .detail-row .dk { font-size: 0.8rem; color: #6b7280; font-weight: 500; flex-shrink: 0; }
        .detail-row .dv { font-size: 0.85rem; color: #1a2e1a; font-weight: 600; text-align: right; word-break: break-all; }

        /* ── Email notice ── */
        .email-notice {
            display: flex; gap: 0.75rem; align-items: flex-start;
            background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;
            padding: 1rem 1.25rem; margin-bottom: 1.75rem;
        }
        .email-notice .icon { font-size: 1.25rem; color: #3b82f6; flex-shrink: 0; margin-top: 1px; }
        .email-notice p { font-size: 0.85rem; color: #1e40af; line-height: 1.5; }
        .email-notice strong { display: block; color: #1d4ed8; margin-bottom: 0.15rem; }

        /* ── Quote ── */
        .quote {
            text-align: center; padding: 1rem 0.5rem;
            color: #6b7280; font-size: 0.88rem; font-style: italic;
            line-height: 1.6; border-top: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        /* ── Buttons ── */
        .btn-home {
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            width: 100%; padding: 0.9rem 1.5rem;
            background: linear-gradient(135deg, #064e3b, #059669);
            color: #fff; font-size: 1rem; font-weight: 700;
            border: none; border-radius: 14px; cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 6px 20px rgba(6,78,59,0.35);
        }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(6,78,59,0.45); color: #fff; }

        /* ── Footer ── */
        footer {
            position: relative; z-index: 1;
            text-align: center; padding: 1.25rem;
            color: rgba(255,255,255,0.6); font-size: 0.78rem;
        }

        /* ── Failed state ── */
        .failed .card-top { background: linear-gradient(135deg, #7f1d1d, #dc2626); }
        .failed .amount-hero .value { color: #dc2626; }
        .failed .details { background: #fef2f2; border-color: #fecaca; }
        .failed .detail-row:not(:last-child) { border-color: #fecaca; }
        .failed .btn-home { background: linear-gradient(135deg, #7f1d1d, #dc2626); box-shadow: 0 6px 20px rgba(220,38,38,0.35); }
        .failed .btn-home:hover { box-shadow: 0 10px 28px rgba(220,38,38,0.45); }

        @media (max-width: 480px) {
            .card { border-radius: 20px; }
            .card-top { padding: 2rem 1.5rem 3rem; }
            .card-body { padding: 1.5rem 1.5rem 2rem; }
            .card-top h1 { font-size: 1.4rem; }
            .amount-hero .value { font-size: 2.2rem; }
        }
    </style>
</head>
<body>
    <div class="bg-gradient">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="card-wrap">
        <div class="card {{ $success ? '' : 'failed' }}">

            <div class="card-top">
                <div class="logo-wrap">
                    <img src="{{ asset('abu_logo.png') }}" alt="GIVE ABU">
                </div>

                @if($success)
                    <div class="check-circle">
                        <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polyline class="check-path" points="14,27 22,35 38,18"
                                stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h1>Thank You, {{ $donorName }}!</h1>
                    <p>Your generosity is building the future of ABU</p>
                @else
                    <div class="check-circle">
                        <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="16" y1="16" x2="36" y2="36" stroke="white" stroke-width="5" stroke-linecap="round"/>
                            <line x1="36" y1="16" x2="16" y2="36" stroke="white" stroke-width="5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h1>Payment Incomplete</h1>
                    <p>We could not confirm your payment</p>
                @endif

                <div class="wave">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 48" preserveAspectRatio="none">
                        <path d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" fill="#ffffff"/>
                    </svg>
                </div>
            </div>

            <div class="card-body">
                @if($success)
                    <div class="amount-hero">
                        <div class="label">Donation Amount</div>
                        <div class="value">₦{{ number_format($amount, 0) }}</div>
                    </div>

                    <div class="details">
                        <div class="detail-row">
                            <span class="dk">Donor</span>
                            <span class="dv">{{ $donorName }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="dk">Email</span>
                            <span class="dv">{{ $email }}</span>
                        </div>
                        @if($tierName)
                        <div class="detail-row">
                            <span class="dk">Donor Tier</span>
                            <span class="dv" style="color:#064e3b;">🏅 {{ $tierName }}</span>
                        </div>
                        @endif
                        <div class="detail-row">
                            <span class="dk">Reference</span>
                            <span class="dv" style="font-family:monospace;font-size:0.78rem;">{{ $ref }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="dk">Date</span>
                            <span class="dv">{{ now()->format('d M Y, g:i A') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="dk">Status</span>
                            <span class="dv" style="color:#16a34a;">✓ Confirmed</span>
                        </div>
                    </div>

                    @if($emailSent)
                    <div class="email-notice">
                        <span class="icon">📧</span>
                        <p>
                            <strong>Confirmation email sent!</strong>
                            A personalised thank-you has been sent to <strong>{{ $email }}</strong>. Please check your inbox (and spam folder if needed).
                        </p>
                    </div>
                    @endif

                    <div class="quote">
                        "Every naira you give today lights the path for a student,<br>
                        funds a discovery, and strengthens a legacy that will outlive us all."
                    </div>

                @else
                    <div class="details" style="margin-bottom:1.75rem;">
                        <div class="detail-row">
                            <span class="dk">Reference</span>
                            <span class="dv" style="font-family:monospace;font-size:0.78rem;">{{ $ref ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="dk">Status</span>
                            <span class="dv" style="color:#dc2626;">✗ Not Completed</span>
                        </div>
                    </div>
                    <p style="text-align:center;color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">
                        Your payment was not completed or could not be verified. No charge has been made. Please try again.
                    </p>
                @endif

                <a href="{{ url('/') }}" class="btn-home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                    </svg>
                    Return to Home
                </a>
            </div>
        </div>
    </div>

    <footer>&copy; {{ date('Y') }} Ahmadu Bello University Giving &amp; Crowd Funding. All rights reserved.</footer>
</body>
</html>
