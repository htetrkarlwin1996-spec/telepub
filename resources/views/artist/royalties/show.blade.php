<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Royalty Detail') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            @php
                $artist = auth()->user()->artist;
                $artistShare = $royalty->amountForArtist($artist);
            @endphp
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <dl class="space-y-4">
                    <div class="flex justify-between py-3 border-b border-black/10"><dt class="font-bold text-black/60">Store:</dt><dd class="font-bold text-black"><div class="flex items-center gap-2"><x-store-logo :store="$royalty->store" size="5" /><span>{{ $royalty->store->name ?? 'N/A' }}</span></div></dd></div>
                    <div class="flex justify-between py-3 border-b border-black/10"><dt class="font-bold text-black/60">Period:</dt><dd class="font-bold text-black/70">{{ date('F', mktime(0,0,0,$royalty->month,1)) }} {{ $royalty->year }}</dd></div>
                    <div class="flex justify-between py-3 border-b border-black/10"><dt class="font-bold text-black/60">Album:</dt><dd class="font-bold text-black/70">{{ $royalty->album->title ?? 'Unassigned' }}</dd></div>
                    <div class="flex justify-between py-3 border-b border-black/10"><dt class="font-bold text-black/60">Track:</dt><dd class="font-bold text-black/70">{{ $royalty->song->title ?? 'Unassigned' }}</dd></div>
                    <div class="flex justify-between py-3 border-b border-black/10"><dt class="font-bold text-black/60">Gross Revenue:</dt><dd class="font-black text-black">{{ money($royalty->amount) }}</dd></div>
                    <div class="flex justify-between py-3 border-b border-black/10 bg-brand-500/20 -mx-6 px-6"><dt class="font-bold text-black/80">Artist Earnings ({{ number_format($artist->revenue_share_percentage, 0) }}%):</dt><dd class="font-black text-emerald-700 text-lg">{{ money($artistShare) }}</dd></div>
                    <div class="flex justify-between py-3 border-b border-black/10"><dt class="font-bold text-black/60">Streams:</dt><dd class="font-bold text-black/70">{{ $royalty->streams ? number_format($royalty->streams) : '-' }}</dd></div>
                    <div class="flex justify-between py-3"><dt class="font-bold text-black/60">Notes:</dt><dd class="font-semibold text-black/70">{{ $royalty->notes ?? '-' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
