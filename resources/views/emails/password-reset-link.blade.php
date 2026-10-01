<x-mail::message>
# Password Reset Requested

Hi {{ $username }},

We received a request to reset your password. Click the button below to choose a new password. This link expires in 10 minutes.

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

If you did not request this, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
