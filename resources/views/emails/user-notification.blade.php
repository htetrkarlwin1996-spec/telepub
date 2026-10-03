<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title }}</title></head>
<body style="margin:0;background:#fff8e7;font-family:Arial,sans-serif;color:#111">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff8e7;padding:28px 12px"><tr><td align="center">
    <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:100%;background:#fff;border:2px solid #111">
        <tr><td style="padding:22px 28px;background:#ffe500;border-bottom:2px solid #111">
            <table role="presentation" cellspacing="0" cellpadding="0"><tr>
                <td><img src="{{ asset('logo.png') }}" width="54" height="54" alt="TeleMusic" style="display:block;border:2px solid #111"></td>
                <td style="padding-left:14px"><div style="font-size:24px;font-weight:900">TeleMusic</div><div style="font-size:12px;font-weight:700;text-transform:uppercase">Account Notification</div></td>
            </tr></table>
        </td></tr>
        <tr><td style="padding:30px 28px">
            <h1 style="margin:0 0 14px;font-size:26px;line-height:1.2">{{ $title }}</h1>
            <p style="margin:0 0 22px;font-size:16px;line-height:1.6">{{ $messageText }}</p>
            @if(!empty($details))
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:24px;border:1px solid #111">
                    @foreach($details as $label => $value)
                        <tr>
                            <td style="padding:10px 12px;border-bottom:1px solid #ddd;font-weight:700;width:36%">{{ $label }}</td>
                            <td style="padding:10px 12px;border-bottom:1px solid #ddd;word-break:break-word">{{ is_array($value) ? json_encode($value) : $value }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
            <a href="{{ $actionUrl }}" style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:13px 20px;font-weight:800;border:2px solid #111">{{ $actionText }}</a>
            <p style="margin:22px 0 0;font-size:12px;line-height:1.5;color:#666">If you did not perform this action or believe something is wrong, contact TeleMusic support immediately.</p>
        </td></tr>
        <tr><td style="padding:16px 28px;border-top:2px solid #111;background:#f7f7f7;font-size:12px;color:#666">Automated notification from {{ config('app.name') }} · {{ now()->format('Y-m-d H:i T') }}</td></tr>
    </table>
</td></tr></table>
</body>
</html>
