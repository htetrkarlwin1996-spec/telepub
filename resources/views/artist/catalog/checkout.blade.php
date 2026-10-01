<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7] py-8 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="mb-8 text-center"><div class="inline-flex w-10 h-10 items-center justify-center bg-brand-500 border-2 border-black font-black">5</div><h1 class="mt-3 text-3xl font-black">Checkout & Submit</h1><p class="font-bold text-black/60">{{ $album->title }} · {{ ucfirst($album->release_type) }}</p></div>
            @if(session('success'))<div class="mb-6 p-4 bg-emerald-300 border-2 border-black font-bold">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-6 p-4 bg-red-100 border-2 border-black font-bold">{{ $errors->first() }}</div>@endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ method: @js(old('method', '')) }">
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0_#000] p-6">
                        <h2 class="font-black text-xl">Select Payment Method</h2>
                        <p class="mt-2 font-bold text-black/60">Choose a payment method to see its price.</p>
                        <label for="payment-method" class="sr-only">Payment method</label>
                        <select id="payment-method" x-model="method" class="mt-5 w-full border-2 border-black bg-white px-4 py-3 font-black focus:border-brand-500 focus:ring-0">
                            <option value="">Select payment method</option>
                            <option value="stripe">Stripe / Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="offline">Thai Bank Transfer</option>
                            <option value="myanmyanpay">MyanMyanPay MMQR</option>
                        </select>
                    </div>

                    <form x-show="method" x-cloak method="POST" action="{{ route('artist.catalog.pay', $album) }}" data-payment-form class="border-2 border-black bg-white p-6 shadow-[5px_5px_0_#000]">
                        @csrf
                        <input type="hidden" name="method" :value="method">
                        <p class="text-xs font-extrabold uppercase text-black/50">{{ $pricing['is_first'] ? 'First release price' : ucfirst($album->release_type).' release price' }}</p>
                        <h3 class="mt-2 text-xl font-black" x-text="{
                            stripe: 'Stripe / Card',
                            paypal: 'PayPal',
                            offline: 'Thai Bank Transfer',
                            myanmyanpay: 'MyanMyanPay MMQR'
                        }[method]"></h3>
                        <div class="mt-3 text-4xl font-black">
                            <span x-show="method === 'stripe' || method === 'paypal'">USD {{ number_format($pricing['usd'], 2) }}</span>
                            <span x-show="method === 'offline'">THB {{ number_format($pricing['thb'], 2) }}</span>
                            <span x-show="method === 'myanmyanpay'">MMK {{ number_format($pricing['mmk'], 0) }}</span>
                        </div>
                        <div x-show="method === 'offline'" class="mt-5 border-2 border-black bg-amber-100 p-4">
                            <h4 class="font-black">Thai Bank Instructions</h4>
                            <div class="mt-2 whitespace-pre-line text-sm font-bold text-black/70">{{ $pricing['offline_instructions'] }}</div>
                        </div>
                        <button type="submit" class="mt-6 w-full bg-brand-500 border-2 border-black px-4 py-3 font-black uppercase">Pay</button>
                    </form>
                </div>
                <aside class="space-y-5">
                    @if($qrImage)<button type="button" data-open-mmqr class="w-full bg-emerald-100 border-2 border-black shadow-[4px_4px_0_#000] p-5 text-left"><span class="block font-black text-lg">MMQR Payment Pending</span><span class="block mt-2 text-sm font-bold text-black/60">Open QR code to complete payment</span></button>@endif
                    @if($payments->isNotEmpty())<div class="bg-white border-2 border-black p-5"><h3 class="font-black">Payment History</h3>@foreach($payments as $payment)<div class="py-3 border-b border-black/10"><div class="flex justify-between font-bold"><span>{{ ucfirst($payment->provider) }}</span><span>{{ strtoupper($payment->status) }}</span></div><div class="text-xs font-semibold text-black/50">{{ $payment->currency }} {{ number_format($payment->amount, 2) }} · {{ $payment->reference }}</div></div>@endforeach</div>@endif
                </aside>
            </div>
            <a href="{{ route('artist.catalog.step4', $album) }}" class="inline-block mt-8 px-5 py-3 bg-white border-2 border-black font-black">← Back to Stores</a>
        </div>
    </div>

    @if($qrImage)
        <div id="mmqr-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-labelledby="mmqr-title">
            <div class="relative w-full max-w-md bg-white border-2 border-black shadow-[8px_8px_0_#FFE500] p-6 text-center">
                <button type="button" data-close-mmqr class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center border-2 border-black bg-white font-black" aria-label="Close MMQR popup">×</button>
                <img src="{{ asset('images/mmqr-logo.svg') }}" alt="MyanmarPay MMQR" class="mx-auto h-16 w-auto">
                <h2 id="mmqr-title" class="text-2xl font-black">Scan MMQR</h2>
                <p class="mt-2 text-sm font-bold text-black/60">Pay MMK {{ number_format($qrPayment->amount, 0) }} with a supported wallet</p>
                <div class="mt-3 inline-flex border-2 border-black bg-amber-100 px-4 py-2 font-black">Expires in&nbsp;<span data-mmqr-timer data-expires="{{ $qrExpiresAt->toIso8601String() }}">15:00</span></div>
                <img src="{{ $qrImage }}" alt="MyanMyanPay MMQR" class="mx-auto mt-4 w-full max-w-[320px]">
                <a href="{{ $qrImage }}" download="MMQR-{{ $qrPayment->reference }}.svg" class="mt-3 inline-flex border-2 border-black bg-brand-500 px-4 py-2 text-xs font-black uppercase">Download QR</a>
                <p class="mt-3 break-all text-xs font-bold">{{ $qrPayment->reference }}</p>
                <p class="mt-3 text-sm font-bold text-amber-700">Payment status: <span data-mmqr-status>{{ strtoupper($qrPayment->status) }}</span></p>
                <p class="mt-1 text-xs font-bold text-black/50">Payment confirmation is checked automatically.</p>
                <p class="mt-3 text-xs font-bold text-black/60">Payment powered by MyanMyanPay.</p>
                <button type="button" data-close-mmqr class="mt-5 w-full bg-white border-2 border-black px-4 py-3 font-black uppercase">Close</button>
                <form method="POST" action="{{ route('artist.release-payments.cancel', $qrPayment) }}" data-cancel-mmqr class="mt-3">
                    @csrf
                    <button type="submit" class="w-full bg-red-100 border-2 border-black px-4 py-3 font-black uppercase">Cancel Transaction</button>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('mmqr-modal');
                const shouldOpen = @js((bool) session('open_mmqr', false));
                const open = () => {
                    modal?.classList.remove('hidden');
                    modal?.classList.add('flex');
                };
                const close = () => {
                    modal?.classList.add('hidden');
                    modal?.classList.remove('flex');
                };

                document.querySelectorAll('[data-open-mmqr]').forEach((button) => button.addEventListener('click', open));
                document.querySelectorAll('[data-close-mmqr]').forEach((button) => button.addEventListener('click', close));
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) close();
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !modal?.classList.contains('hidden')) close();
                });
                if (shouldOpen) open();

                const timer = document.querySelector('[data-mmqr-timer]');
                if (timer) {
                    const expiresAt = new Date(timer.dataset.expires).getTime();
                    const tick = () => {
                        const seconds = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
                        timer.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
                        if (seconds <= 0) window.location.reload();
                    };
                    tick();
                    setInterval(tick, 1000);
                }

                const statusUrl = @js(route('artist.release-payments.status', $qrPayment));
                const statusLabel = document.querySelector('[data-mmqr-status]');
                let checking = false;
                const checkStatus = async () => {
                    if (checking) return;
                    checking = true;
                    try {
                        const response = await fetch(statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                        if (!response.ok) return;
                        const result = await response.json();
                        if (statusLabel) statusLabel.textContent = result.status.toUpperCase();
                        if (result.completed) {
                            if (timer) timer.textContent = 'PAID';
                            modal?.classList.add('hidden');
                            window.location.href = result.redirect_url;
                        } else if (['failed', 'cancelled', 'expired'].includes(result.status)) {
                            window.location.reload();
                        }
                    } finally {
                        checking = false;
                    }
                };
                checkStatus();
                setInterval(checkStatus, 3000);
            });
        </script>
    @endif
</x-app-layout>
