<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7] py-8 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="mb-8 text-center"><div class="inline-flex w-10 h-10 items-center justify-center bg-brand-500 border-2 border-black font-black">5</div><h1 class="mt-3 text-3xl font-black">Checkout & Submit</h1><p class="font-bold text-black/60">{{ $album->title }} · {{ ucfirst($album->release_type) }}</p></div>
            @if(session('success'))<div class="mb-6 p-4 bg-emerald-300 border-2 border-black font-bold">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-6 p-4 bg-red-100 border-2 border-black font-bold">{{ $errors->first() }}</div>@endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0_#000] p-6"><h2 class="font-black text-xl">Release Fee</h2><p class="mt-2 font-bold text-black/60">{{ $pricing['is_first'] ? 'First release price' : ucfirst($album->release_type).' release price' }}</p><div class="mt-3 text-4xl font-black">USD {{ number_format($pricing['usd'], 2) }}</div></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach([
                            'stripe'=>['Stripe / Card','USD '.number_format($pricing['usd'],2),'bg-blue-100'],
                            'paypal'=>['PayPal','USD '.number_format($pricing['usd'],2),'bg-sky-100'],
                            'offline'=>['Thai Bank Transfer','THB '.number_format($pricing['thb'],2),'bg-amber-100'],
                            'myanmyanpay'=>['MyanMyanPay MMQR','MMK '.number_format($pricing['mmk'],0),'bg-emerald-100'],
                        ] as $method=>$info)
                        <form method="POST" action="{{ route('artist.catalog.pay', $album) }}" class="{{ $info[2] }} border-2 border-black p-5 shadow-[4px_4px_0_#000]">@csrf<input type="hidden" name="method" value="{{ $method }}"><h3 class="font-black text-lg">{{ $info[0] }}</h3><div class="text-2xl font-black mt-2">{{ $info[1] }}</div><button class="mt-5 w-full bg-brand-500 border-2 border-black px-4 py-3 font-black uppercase">Choose & Pay</button></form>
                        @endforeach
                    </div>
                </div>
                <aside class="space-y-5">
                    <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000] p-5"><h3 class="font-black">Thai Bank Instructions</h3><div class="mt-3 whitespace-pre-line text-sm font-bold text-black/70">{{ $pricing['offline_instructions'] }}</div></div>
                    @if($qrImage)<div class="bg-white border-2 border-black shadow-[4px_4px_0_#000] p-5 text-center"><h3 class="font-black">Scan MMQR</h3><img src="{{ $qrImage }}" alt="MyanMyanPay MMQR" class="mx-auto mt-3 w-full max-w-[320px]"><p class="mt-3 text-xs font-bold">{{ $qrPayment->reference }}</p></div>@endif
                    @if($payments->isNotEmpty())<div class="bg-white border-2 border-black p-5"><h3 class="font-black">Payment History</h3>@foreach($payments as $payment)<div class="py-3 border-b border-black/10"><div class="flex justify-between font-bold"><span>{{ ucfirst($payment->provider) }}</span><span>{{ strtoupper($payment->status) }}</span></div><div class="text-xs font-semibold text-black/50">{{ $payment->currency }} {{ number_format($payment->amount, 2) }} · {{ $payment->reference }}</div></div>@endforeach</div>@endif
                </aside>
            </div>
            <a href="{{ route('artist.catalog.step4', $album) }}" class="inline-block mt-8 px-5 py-3 bg-white border-2 border-black font-black">← Back to Stores</a>
        </div>
    </div>
</x-app-layout>
