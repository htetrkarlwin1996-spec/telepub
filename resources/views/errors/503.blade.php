<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Maintenance | Tele Music</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; color: #f8fafc; background: radial-gradient(circle at 15% 15%, #0c4a6e 0, transparent 34%), radial-gradient(circle at 85% 75%, #831843 0, transparent 34%), #020617; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .card { width: min(680px, 100%); padding: 42px 28px; text-align: center; border: 1px solid rgba(255,255,255,.12); border-radius: 32px; background: rgba(15,23,42,.78); box-shadow: 0 30px 80px rgba(0,0,0,.4); backdrop-filter: blur(18px); }
        .logo { width: 76px; height: 76px; padding: 8px; border-radius: 22px; background: #fff; box-shadow: 0 12px 40px rgba(236,72,153,.25); }
        .eyebrow { margin: 24px 0 8px; color: #7dd3fc; font-size: 13px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(30px, 7vw, 52px); line-height: 1.1; }
        .message { margin: 18px auto 0; max-width: 520px; color: #cbd5e1; line-height: 1.7; }
        .countdown { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 30px auto 0; max-width: 430px; }
        .unit { padding: 18px 8px; border: 1px solid rgba(125,211,252,.16); border-radius: 20px; background: rgba(14,165,233,.08); }
        .number { display: block; font-size: clamp(25px, 7vw, 38px); font-weight: 900; font-variant-numeric: tabular-nums; }
        .label { display: block; margin-top: 4px; color: #94a3b8; font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .note { margin-top: 24px; color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>
@php
    $countdownSeconds = (int) ($retryAfter ?? $exception?->getHeaders()['Retry-After'] ?? 3600);
@endphp
    <main class="card">
        <img class="logo" src="/assets/images/logo.png" alt="Tele Music">
        <p class="eyebrow">Scheduled Maintenance</p>
        <h1>We'll be back soon.</h1>
        <p class="message">Tele Music ကို ပိုကောင်းအောင် ပြုပြင်နေပါတယ်။ ခဏစောင့်ပြီး အချိန်ပြည့်သွားချိန်မှာ ဒီစာမျက်နှာက အလိုအလျောက် ပြန်ဖွင့်ပေးပါမယ်။</p>
        <div class="countdown" aria-label="Maintenance countdown">
            <div class="unit"><span class="number" id="hours">00</span><span class="label">Hours</span></div>
            <div class="unit"><span class="number" id="minutes">00</span><span class="label">Minutes</span></div>
            <div class="unit"><span class="number" id="seconds">00</span><span class="label">Seconds</span></div>
        </div>
        <p class="note">Thank you for your patience.</p>
    </main>
    <script>
        let remaining = {{ max(0, $countdownSeconds) }};
        const pad = (value) => String(value).padStart(2, '0');
        const render = () => {
            document.getElementById('hours').textContent = pad(Math.floor(remaining / 3600));
            document.getElementById('minutes').textContent = pad(Math.floor((remaining % 3600) / 60));
            document.getElementById('seconds').textContent = pad(remaining % 60);
            if (remaining <= 0) { window.location.reload(); return; }
            remaining -= 1;
            setTimeout(render, 1000);
        };
        render();
    </script>
</body>
</html>
