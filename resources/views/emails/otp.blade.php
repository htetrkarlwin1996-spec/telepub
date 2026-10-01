<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title }}</title></head>
<body style="margin:0;background:#fff8e7;font-family:Arial,sans-serif;color:#111">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff8e7;padding:28px 12px"><tr><td align="center">
    <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:100%;background:#fff;border:2px solid #111">
        <tr><td style="padding:22px 28px;background:#ffe500;border-bottom:2px solid #111;text-align:center">
            <img src="{{ asset('logo.png') }}" width="58" height="58" alt="TeleMusic" style="display:inline-block;border:2px solid #111">
            <div style="margin-top:8px;font-size:24px;font-weight:900">TeleMusic</div>
        </td></tr>
        <tr><td style="padding:32px 28px;text-align:center">
            <h1 style="margin:0 0 14px;font-size:26px">{{ $title }}</h1>
            <p style="margin:0 auto 24px;max-width:480px;font-size:16px;line-height:1.6;color:#444">{{ $intro }}</p>
            <div style="display:inline-block;border:2px solid #111;background:#fff4a8;padding:16px 28px;font-family:monospace;font-size:34px;font-weight:900;letter-spacing:8px">{{ $otp }}</div>
            <p style="margin:24px 0 0;font-size:14px;font-weight:700">This code expires in 10 minutes.</p>
            <p style="margin:8px 0 0;font-size:13px;color:#666">If you did not request this, you can safely ignore this email.</p>
        </td></tr>
        <tr><td style="padding:16px 28px;border-top:2px solid #111;background:#f7f7f7;text-align:center;font-size:12px;color:#666">© {{ now()->year }} TeleMusic</td></tr>
    </table>
</td></tr></table>
</body>
</html>
