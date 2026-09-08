<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Distribution Details') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <dl class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Song:</dt><dd class="font-bold text-black">{{ $distribution->song->title ?? 'N/A' }}</dd></div>
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Album:</dt><dd class="font-semibold text-black/70">{{ $distribution->album->title ?? 'N/A' }}</dd></div>
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Store:</dt><dd class="font-semibold text-black/70"><div class="flex items-center gap-2"><x-store-logo :store="$distribution->store" size="5" /><span>{{ $distribution->store->name ?? 'N/A' }}</span></div></dd></div>
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Status:</dt><dd><span class="text-xs font-bold border border-black px-2 py-1
                        @if($distribution->status == 'pending') bg-gray-100 text-black
                        @elseif($distribution->status == 'submitted') bg-amber-200 text-black
                        @elseif($distribution->status == 'approved') bg-blue-200 text-black
                        @elseif($distribution->status == 'live') bg-emerald-400 text-black
                        @else bg-red-200 text-black
                        @endif">{{ ucfirst($distribution->status) }}</span></dd></div>
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Fee:</dt><dd class="font-bold text-black">${{ number_format($distribution->distribution_fee, 2) }}</dd></div>
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Submitted:</dt><dd class="font-semibold text-black/70">{{ $distribution->submitted_at ? $distribution->submitted_at->format('Y-m-d H:i') : '-' }}</dd></div>
                    <div class="flex justify-between py-2 border-b border-black/10"><dt class="font-bold text-black/60">Store URL:</dt><dd>{!! $distribution->store_url ? '<a href="'.$distribution->store_url.'" target="_blank" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black">View on Store →</a>' : '<span class="font-semibold text-black/40">-</span>' !!}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
