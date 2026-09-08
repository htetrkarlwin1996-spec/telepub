@extends('emails.layout')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:24px;color:#d1d5db;">
        Your take down request status has been updated.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;background:#020617;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
        <tr>
            <td style="padding:18px;">
                <p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">Song Name</p>
                <p style="margin:0;font-size:18px;font-weight:bold;color:#ffffff;">
                    {{ $songName }}
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:0 18px 18px;">
                <p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">Album Name</p>
                <p style="margin:0;font-size:14px;color:#e5e7eb;">
                    {{ $albumName ?: '-' }}
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:0 18px 18px;">
                <p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">Status</p>

                @if($requestStatus === 'completed')
                    <span style="display:inline-block;background:rgba(52,211,153,0.12);color:#34d399;border:1px solid rgba(52,211,153,0.25);padding:8px 12px;border-radius:999px;font-size:13px;font-weight:bold;">
                        Completed
                    </span>
                @elseif($requestStatus === 'rejected')
                    <span style="display:inline-block;background:rgba(248,113,113,0.12);color:#f87171;border:1px solid rgba(248,113,113,0.25);padding:8px 12px;border-radius:999px;font-size:13px;font-weight:bold;">
                        Rejected
                    </span>
                @else
                    <span style="display:inline-block;background:rgba(96,165,250,0.12);color:#60a5fa;border:1px solid rgba(96,165,250,0.25);padding:8px 12px;border-radius:999px;font-size:13px;font-weight:bold;">
                        {{ ucfirst($requestStatus) }}
                    </span>
                @endif
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