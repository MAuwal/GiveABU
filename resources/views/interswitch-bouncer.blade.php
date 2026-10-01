<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment...</title>
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; font-family: sans-serif; background: #fff; margin:0;">
    <div style="text-align: center;">
        <!-- Simple spinner element -->
        <svg style="margin: 0 auto; display: block; animation: spin 1s linear infinite;" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <style>
                @keyframes spin { 100% { transform: rotate(360deg); } }
            </style>
            <circle cx="12" cy="12" r="10" stroke="#f3f4f6" stroke-width="3"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <p style="color: #6b7280; margin-top: 15px; font-size: 14px;">Loading secure gateway...</p>
    </div>

    <!-- Interswitch WebPay Form -->
    <form id="isw-form" method="POST" action="{{ $action_url }}" style="display:none;">
        @foreach($form_fields as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
        @endforeach
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('isw-form').submit();
        });
    </script>
</body>
</html>
