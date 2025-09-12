@php
    $red   = 'rgb(220,38,38)'; /* Tailwind red-600 */
    $muted = '#d1d5db';
    $row   = '#111827';
    $border= '#374151';
@endphp

@component('mail::message')

<!-- Brand -->
<div style="text-align:center; margin-bottom:20px;">
    <a href="{{ url('/') }}"
       style="color:{{ $red }}; font-size:28px; font-weight:800; text-decoration:none;">
        {{ $appName ?? 'AJURNIE' }}
    </a>
</div>

<h2 style="color:{{ $red }}; text-align:center; margin-bottom:10px;">
    Reset your password
</h2>

<p style="text-align:center; color:{{ $muted }}; font-size:15px;">
    You requested a password reset. Click the button below to set a new password.
</p>

<div style="text-align:center; margin: 22px 0;">
    <a href="{{ $url }}"
       style="background:{{ $red }}; color:#fff; text-decoration:none; display:inline-block;
              padding:12px 20px; border-radius:10px; font-weight:600;">
        Reset Password
    </a>
</div>

<p style="color:{{ $muted }}; font-size:14px; margin-top:16px;">
    If the button doesn’t work, copy and paste this link into your browser:
</p>
<p style="word-break:break-all; font-size:13px; margin:8px 0;">
    <a href="{{ $url }}" style="color:#93c5fd; text-decoration:underline;">{{ $url }}</a>
</p>

<p style="color:#9ca3af; font-size:13px; margin-top:24px;">
    If you didn't request this, you can safely ignore this email.
</p>

@endcomponent
