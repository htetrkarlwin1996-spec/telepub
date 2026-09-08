<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectText ?? 'Tele Music Notification' }}</title>
</head>
<body style="margin:0;padding:0;background:#0f172a;font-family:Arial,Helvetica,sans-serif;color:#ffffff;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;padding:30px 15px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#111827;border:1px solid rgba(255,255,255,0.08);border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 30px;text-align:center;background:linear-gradient(135deg,#991b1b,#111827);">
                            <img
                                src="{{ asset('assets/images/logo.png') }}"
                                alt="Tele Music"
                                style="max-width:120px;height:auto;margin-bottom:14px;"
                            >

                            <h1 style="margin:0;font-size:24px;line-height:32px;color:#ffffff;">
                                {{ $title ?? 'Tele Music' }}
                            </h1>

                            @isset($subtitle)
                                <p style="margin:8px 0 0;font-size:14px;line-height:22px;color:#fecaca;">
                                    {{ $subtitle }}
                                </p>
                            @endisset
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;">
                            <p style="margin:0 0 18px;font-size:15px;line-height:24px;color:#d1d5db;">
                                Dear {{ $userName ?? 'User' }},
                            </p>

                            @yield('content')

                            <p style="margin:26px 0 0;font-size:15px;line-height:24px;color:#d1d5db;">
                                Thank you,<br>
                                <strong style="color:#ffffff;">Tele Music Team</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 30px;background:#020617;text-align:center;">
                            <p style="margin:0;font-size:12px;line-height:20px;color:#64748b;">
                                © {{ date('Y') }} Tele Music. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:18px 0 0;font-size:12px;color:#64748b;">
                    This is an automated email. Please do not reply directly to this message.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>