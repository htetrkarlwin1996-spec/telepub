@extends('emails.layout')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:24px;color:#d1d5db;">
        Your payout has been transferred successfully.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;background:#020617;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
        <tr>
            <td style="padding:18px;">
                <p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">Amount</p>
                <p style="margin:0;font-size:24px;font-weight:bold;color:#34d399;">
                    {{ number_format((float) $amount, 2) }} {{ $currency }}
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:0 18px 18px;">
                <p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">Status</p>
                <span style="display:inline-block;background:rgba(52,211,153,0.12);color:#34d399;border:1px solid rgba(52,211,153,0.25);padding:8px 12px;border-radius:999px;font-size:13px;font-weight:bold;">
                    Paid
                </span>
            </td>
        </tr>

        @if(!empty($adminNote))
            <tr>
                <td style="padding:0 18px 18px;">
                    <p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">Admin Note</p>
                    <p style="margin:0;font-size:14px;line-height:22px;color:#e5e7eb;">
                        {{ $adminNote }}
                    </p>
                </td>
            </tr>
        @endif
    </table>
@endsection