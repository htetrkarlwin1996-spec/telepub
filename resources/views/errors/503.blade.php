<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>We'll Be Right Back | {{ config('app.name', 'TeleMusic') }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #171717;
            --paper: #fffaf0;
            --card: #ffffff;
            --brand: #ff4d00;
            --yellow: #ffd84d;
            --mint: #83e6c1;
            --blue: #78b7ff;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 216, 77, .45) 0 4px, transparent 5px),
                radial-gradient(circle at 88% 72%, rgba(255, 77, 0, .20) 0 5px, transparent 6px),
                var(--paper);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page {
            min-height: 100vh;
            min-height: 100svh;
            display: grid;
            place-items: center;
            padding: 32px 20px;
        }

        .card {
            position: relative;
            width: min(920px, 100%);
            padding: clamp(28px, 5vw, 58px);
            border: 4px solid var(--ink);
            background: var(--card);
            box-shadow: 12px 12px 0 var(--ink);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: clamp(30px, 5vw, 52px);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .brand img {
            width: 46px;
            height: 46px;
            border: 3px solid var(--ink);
            object-fit: cover;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 2px solid var(--ink);
            background: var(--yellow);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border: 2px solid var(--ink);
            border-radius: 50%;
            background: var(--brand);
            animation: pulse 1.4s ease-in-out infinite;
        }

        .content {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            align-items: center;
            gap: clamp(32px, 6vw, 70px);
        }

        .eyebrow {
            display: inline-block;
            margin: 0 0 16px;
            padding: 6px 10px;
            background: var(--ink);
            color: #fff;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .13em;
            text-transform: uppercase;
            transform: rotate(-1deg);
        }

        h1 {
            max-width: 560px;
            margin: 0;
            font-size: clamp(38px, 6vw, 72px);
            line-height: 1.04;
            letter-spacing: -.055em;
        }

        .accent {
            position: relative;
            z-index: 0;
            white-space: nowrap;
        }

        .accent::after {
            position: absolute;
            z-index: -1;
            right: -4px;
            bottom: 4px;
            left: -4px;
            height: .28em;
            background: var(--yellow);
            content: "";
            transform: rotate(-1.5deg);
        }

        .message {
            max-width: 540px;
            margin: 22px 0 0;
            color: #4c4c4c;
            font-size: clamp(15px, 2vw, 18px);
            font-weight: 600;
            line-height: 1.75;
        }

        .countdown {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            max-width: 500px;
            margin-top: 24px;
        }

        .countdown-unit {
            padding: 12px 6px;
            border: 3px solid var(--ink);
            background: var(--yellow);
            box-shadow: 4px 4px 0 var(--ink);
            text-align: center;
        }

        .countdown-value {
            display: block;
            font-size: clamp(20px, 4vw, 30px);
            font-weight: 950;
            font-variant-numeric: tabular-nums;
        }

        .countdown-label {
            display: block;
            margin-top: 2px;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .scene {
            position: relative;
            min-height: 360px;
            display: grid;
            place-items: center;
        }

        .maintenance-illustration {
            position: relative;
            z-index: 1;
            display: block;
            width: min(440px, 112%);
            height: auto;
            filter: drop-shadow(9px 10px 0 rgba(23, 23, 23, .16));
            animation: worker-float 3.5s ease-in-out infinite;
        }

        .scene-badge {
            position: absolute;
            z-index: 2;
            right: -8px;
            bottom: 16px;
            padding: 8px 12px;
            border: 3px solid var(--ink);
            background: var(--yellow);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
            transform: rotate(-3deg);
        }

        .screen {
            position: relative;
            width: min(310px, 86%);
            height: 190px;
            padding: 28px 24px;
            overflow: hidden;
            border: 4px solid var(--ink);
            background: #262626;
            box-shadow: 9px 9px 0 var(--blue);
            transform: rotate(1.5deg);
        }

        .screen::before {
            position: absolute;
            top: 11px;
            left: 13px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff645d;
            box-shadow: 15px 0 #ffd84d, 30px 0 #83e6c1;
            content: "";
        }

        .code {
            height: 9px;
            margin: 13px 0;
            background: var(--mint);
            transform-origin: left;
            animation: code 2.3s ease-in-out infinite;
        }

        .code:nth-child(2) { width: 76%; animation-delay: -.4s; background: var(--blue); }
        .code:nth-child(3) { width: 48%; animation-delay: -.9s; }
        .code:nth-child(4) { width: 88%; animation-delay: -1.3s; background: var(--yellow); }
        .code:nth-child(5) { width: 63%; animation-delay: -1.7s; background: #ff8c5c; }

        .base {
            width: min(350px, 98%);
            height: 24px;
            margin-top: -4px;
            border: 4px solid var(--ink);
            background: #d9d9d9;
            transform: perspective(80px) rotateX(5deg);
        }

        .gear {
            position: absolute;
            display: grid;
            place-items: center;
            border: 4px solid var(--ink);
            border-radius: 50%;
            background: var(--brand);
            font-size: 30px;
            font-weight: 900;
            animation: spin 6s linear infinite;
        }

        .gear-one { top: 3px; right: 1px; width: 72px; height: 72px; }
        .gear-two { bottom: 4px; left: 0; width: 56px; height: 56px; background: var(--yellow); animation-direction: reverse; }

        .note {
            position: absolute;
            top: 2px;
            left: 12px;
            font-size: 42px;
            font-weight: 900;
            animation: float 2.4s ease-in-out infinite;
        }

        .equalizer {
            display: flex;
            align-items: end;
            justify-content: center;
            gap: 5px;
            height: 36px;
            margin-top: 30px;
        }

        .equalizer span {
            width: 7px;
            height: 9px;
            border: 2px solid var(--ink);
            background: var(--brand);
            animation: bars .9s ease-in-out infinite alternate;
        }

        .equalizer span:nth-child(2) { animation-delay: -.6s; background: var(--yellow); }
        .equalizer span:nth-child(3) { animation-delay: -.3s; background: var(--mint); }
        .equalizer span:nth-child(4) { animation-delay: -.8s; background: var(--blue); }
        .equalizer span:nth-child(5) { animation-delay: -.15s; background: var(--brand); }

        .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-top: clamp(36px, 6vw, 62px);
            padding-top: 20px;
            border-top: 3px solid var(--ink);
            color: #666;
            font-size: 12px;
            font-weight: 800;
        }

        .footer strong { color: var(--ink); }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .38; transform: scale(.72); }
        }

        @keyframes code {
            0%, 100% { transform: scaleX(.55); opacity: .65; }
            50% { transform: scaleX(1); opacity: 1; }
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-7deg); }
            50% { transform: translateY(-14px) rotate(7deg); }
        }

        @keyframes worker-float {
            0%, 100% { transform: translateY(0) rotate(.3deg); }
            50% { transform: translateY(-8px) rotate(-.3deg); }
        }

        @keyframes bars {
            from { height: 8px; }
            to { height: 34px; }
        }

        @media (max-width: 720px) {
            .page { padding: 18px 14px 28px; }
            .card { padding: 24px 20px 28px; box-shadow: 7px 7px 0 var(--ink); }
            .topbar { align-items: flex-start; }
            .brand span { display: none; }
            .content { grid-template-columns: 1fr; text-align: center; }
            .eyebrow { margin-inline: auto; }
            .message { margin-inline: auto; }
            .countdown { margin-inline: auto; }
            .scene { min-height: 280px; }
            .maintenance-illustration { width: min(430px, 105%); }
            .scene-badge { right: 2px; bottom: 2px; }
            .footer { flex-direction: column; text-align: center; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="card" aria-labelledby="maintenance-title">
            <header class="topbar">
                <div class="brand">
                    <img src="/logo.png" alt="">
                    <span>{{ config('app.name', 'TeleMusic') }}</span>
                </div>
                <div class="status">
                    <span class="status-dot" aria-hidden="true"></span>
                    Maintenance in progress
                </div>
            </header>

            <div class="content">
                <div>
                    <p class="eyebrow">We will be back soon</p>
                    <h1 id="maintenance-title">
                        We're making things<br><span class="accent">even better.</span>
                    </h1>
                    <p class="message">
                        Our engineering team is tuning the servers and upgrading the system
                        to bring you a better music experience. We’ll be back online shortly.
                    </p>

                    @if(isset($remainingSeconds) && $remainingSeconds > 0)
                        <div class="countdown" id="maintenance-countdown" data-seconds="{{ $remainingSeconds }}" aria-label="Time remaining until maintenance ends">
                            <div class="countdown-unit"><span class="countdown-value" data-unit="days">0</span><span class="countdown-label">Days</span></div>
                            <div class="countdown-unit"><span class="countdown-value" data-unit="hours">00</span><span class="countdown-label">Hours</span></div>
                            <div class="countdown-unit"><span class="countdown-value" data-unit="minutes">00</span><span class="countdown-label">Minutes</span></div>
                            <div class="countdown-unit"><span class="countdown-value" data-unit="seconds">00</span><span class="countdown-label">Seconds</span></div>
                        </div>
                    @endif

                    <div class="equalizer" aria-hidden="true">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>

                <div class="scene">
                    <img
                        class="maintenance-illustration"
                        src="/images/maintenance-engineer.png"
                        alt="An engineer using tools to repair music servers"
                    >
                    <span class="scene-badge" aria-hidden="true">Server tune-up</span>
                </div>
            </div>

            <footer class="footer">
                <span>Thanks for your patience — <strong>{{ config('app.name', 'TeleMusic') }} Team</strong></span>
                <span>HTTP 503 · Service temporarily unavailable</span>
            </footer>
        </section>
    </main>
    @if(isset($remainingSeconds) && $remainingSeconds > 0)
        <script>
            (() => {
                const countdown = document.getElementById('maintenance-countdown');
                let remaining = Number(countdown.dataset.seconds || 0);
                const pad = value => String(value).padStart(2, '0');
                const set = (unit, value) => countdown.querySelector(`[data-unit="${unit}"]`).textContent = value;
                const tick = () => {
                    set('days', Math.floor(remaining / 86400));
                    set('hours', pad(Math.floor((remaining % 86400) / 3600)));
                    set('minutes', pad(Math.floor((remaining % 3600) / 60)));
                    set('seconds', pad(remaining % 60));
                    if (remaining <= 0) { window.location.reload(); return; }
                    remaining--;
                    setTimeout(tick, 1000);
                };
                tick();
            })();
        </script>
    @endif
</body>
</html>
