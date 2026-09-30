<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ $title }}</h2>
            <a href="{{ route('artist.royalties', $filters) }}" class="px-4 py-2 bg-white border-2 border-black font-extrabold text-sm shadow-[3px_3px_0_#000]">← Back to Royalties</a>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[6px_6px_0_#000]">
                <div class="p-6 border-b-2 border-black flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-black text-xl text-black">{{ $title }}</h3>
                        <p class="text-sm font-bold text-black/50 mt-1">Complete results for the selected royalty period.</p>
                    </div>
                    <div class="text-sm font-extrabold bg-brand-500 border-2 border-black px-3 py-2">{{ number_format($items->total()) }} results</div>
                </div>

                <div class="p-4 sm:p-6 overflow-x-auto">
                    @if($section === 'months')
                        <table class="w-full min-w-[700px] text-sm">
                            <thead><tr class="border-b-2 border-black"><th class="text-left py-3 px-2 uppercase text-xs">Period</th><th class="text-right py-3 px-2 uppercase text-xs">Gross Revenue</th><th class="text-right py-3 px-2 uppercase text-xs">Artist Earnings</th><th class="text-right py-3 px-2 uppercase text-xs">Streams</th></tr></thead>
                            <tbody>@forelse($items as $item)<tr class="border-b border-black/10 hover:bg-brand-500/10"><td class="py-4 px-2 font-bold">{{ date('F', mktime(0,0,0,$item->month,1)) }} {{ $item->year }}</td><td class="py-4 px-2 text-right font-bold text-black/60">{{ money($item->gross_total) }}</td><td class="py-4 px-2 text-right font-black">+{{ money($item->total) }}</td><td class="py-4 px-2 text-right font-bold">{{ number_format($item->total_streams) }}</td></tr>@empty<tr><td colspan="4" class="py-10 text-center font-bold text-black/40">No royalty data.</td></tr>@endforelse</tbody>
                        </table>
                    @elseif($section === 'stores')
                        <table class="w-full min-w-[600px] text-sm">
                            <thead><tr class="border-b-2 border-black"><th class="text-left py-3 px-2 uppercase text-xs">Store</th><th class="text-right py-3 px-2 uppercase text-xs">Artist Earnings</th><th class="text-right py-3 px-2 uppercase text-xs">Streams</th></tr></thead>
                            <tbody>@forelse($items as $item)<tr class="border-b border-black/10 hover:bg-brand-500/10"><td class="py-4 px-2"><span class="flex items-center gap-3 font-bold"><x-store-logo :store="$item->store" size="6" />{{ $item->store->name ?? 'N/A' }}</span></td><td class="py-4 px-2 text-right font-black">{{ money($item->total) }}</td><td class="py-4 px-2 text-right font-bold">{{ number_format($item->total_streams) }}</td></tr>@empty<tr><td colspan="3" class="py-10 text-center font-bold text-black/40">No store data.</td></tr>@endforelse</tbody>
                        </table>
                    @elseif($section === 'albums' || $section === 'tracks')
                        <table class="w-full min-w-[700px] text-sm">
                            <thead><tr class="border-b-2 border-black"><th class="text-left py-3 px-2 uppercase text-xs">{{ $section === 'albums' ? 'Album' : 'Track' }}</th>@if($section === 'tracks')<th class="text-left py-3 px-2 uppercase text-xs">Album</th>@endif<th class="text-right py-3 px-2 uppercase text-xs">Artist Earnings</th><th class="text-right py-3 px-2 uppercase text-xs">Streams</th></tr></thead>
                            <tbody>@forelse($items as $item)<tr class="border-b border-black/10 hover:bg-brand-500/10"><td class="py-4 px-2 font-bold">{{ $section === 'albums' ? ($item->album->title ?? 'Deleted Album') : ($item->song->title ?? 'Deleted Track') }}</td>@if($section === 'tracks')<td class="py-4 px-2 font-semibold text-black/60">{{ $item->album->title ?? 'No album' }}</td>@endif<td class="py-4 px-2 text-right font-black">{{ money($item->total) }}</td><td class="py-4 px-2 text-right font-bold">{{ number_format($item->total_streams) }}</td></tr>@empty<tr><td colspan="{{ $section === 'tracks' ? 4 : 3 }}" class="py-10 text-center font-bold text-black/40">No linked royalty data.</td></tr>@endforelse</tbody>
                        </table>
                    @else
                        <table class="w-full min-w-[1100px] text-sm">
                            <thead><tr class="border-b-2 border-black"><th class="text-left py-3 px-2 uppercase text-xs">Store</th><th class="text-left py-3 px-2 uppercase text-xs">Album / Track</th><th class="text-left py-3 px-2 uppercase text-xs">Period</th><th class="text-right py-3 px-2 uppercase text-xs">Gross Revenue</th><th class="text-right py-3 px-2 uppercase text-xs">Artist Earnings</th><th class="text-right py-3 px-2 uppercase text-xs">Streams</th><th class="text-left py-3 px-2 uppercase text-xs">Notes</th></tr></thead>
                            <tbody>@forelse($items as $item)<tr class="border-b border-black/10 hover:bg-brand-500/10"><td class="py-4 px-2"><span class="flex items-center gap-2 font-bold"><x-store-logo :store="$item->store" size="5" />{{ $item->store->name ?? 'N/A' }}</span></td><td class="py-4 px-2"><div class="font-bold">{{ $item->song->title ?? 'Unassigned' }}</div><div class="text-xs font-semibold text-black/50">{{ $item->album->title ?? 'No album linked' }}</div></td><td class="py-4 px-2 font-semibold">{{ date('F', mktime(0,0,0,$item->month,1)) }} {{ $item->year }}</td><td class="py-4 px-2 text-right font-bold text-black/60">{{ money($item->amount) }}</td><td class="py-4 px-2 text-right font-black text-emerald-600">+{{ money($artist->getArtistShareAttribute($item->amount)) }}</td><td class="py-4 px-2 text-right font-bold">{{ $item->streams ? number_format($item->streams) : '-' }}</td><td class="py-4 px-2 text-xs font-semibold text-black/50">{{ $item->notes ?? '-' }}</td></tr>@empty<tr><td colspan="7" class="py-10 text-center font-bold text-black/40">No transactions.</td></tr>@endforelse</tbody>
                        </table>
                    @endif

                    <div class="mt-6">{{ $items->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
