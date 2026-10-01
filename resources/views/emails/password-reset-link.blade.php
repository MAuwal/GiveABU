<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="https://abu-endowment.cloud/abu_logo_white_for_email.png" alt="GiveABU logo" width="80" style="width:80px;height:auto;background-color:#006B3F;padding:12px;">
<br>GiveABU
</x-mail::header>
</x-slot:header>

# Password Reset Requested

Hi {{ $username }},

We received a request to reset your password. Click the button below to choose a new password. This link expires in 10 minutes.

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

If you did not request this, you can safely ignore this email.

Thanks,<br>
GiveABU Team

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} ABU. All rights reserved. Powered by @@KADICT Hub.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
