<x-app-layout>
    <x-slot name="header"><h2 class="font-extrabold text-2xl text-black tracking-tight">Royalty Analytics</h2></x-slot>
    <div class="py-6 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto space-y-6">
            <form method="GET" action="{{ route('admin.analytics') }}" class="bg-white border-2 border-black shadow-[4px_4px_0_#000] p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <div><x-input-label for="artist_id" value="Artist" /><select id="artist_id" name="artist_id" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Artists</option>@foreach($artists as $artist)<option value="{{ $artist->id }}" @selected(($filters['artist_id'] ?? '') == $artist->id)>{{ $artist->artist_name }}</option>@endforeach</select></div>
                    <div><x-input-label for="store_id" value="Store" /><select id="store_id" name="store_id" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Stores</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected(($filters['store_id'] ?? '') == $store->id)>{{ $store->name }}</option>@endforeach</select></div>
                    <div><x-input-label for="month" value="Month" /><select id="month" name="month" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Months</option>@for($month=1;$month<=12;$month++)<option value="{{ $month }}" @selected(($filters['month'] ?? '') == $month)>{{ date('F', mktime(0,0,0,$month,1)) }}</option>@endfor</select></div>
                    <div><x-input-label for="year" value="Year" /><select id="year" name="year" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Years</option>@foreach($years as $year)<option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>@endforeach</select></div>
                    <button class="px-4 py-2.5 bg-brand-500 border-2 border-black font-extrabold uppercase shadow-[3px_3px_0_#000]">Apply</button>
                    <a href="{{ route('admin.analytics') }}" class="px-4 py-2.5 bg-white border-2 border-black text-center font-extrabold uppercase">Clear</a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-brand-500 border-2 border-black shadow-[4px_4px_0_#000] p-5"><div class="text-xs font-extrabold uppercase text-black/60">Gross Revenue</div><div class="text-3xl font-black">${{ number_format($totals['revenue'], 2) }}</div></div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000] p-5"><div class="text-xs font-extrabold uppercase text-black/60">Streams</div><div class="text-3xl font-black">{{ number_format($totals['streams']) }}</div></div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000] p-5"><div class="text-xs font-extrabold uppercase text-black/60">Royalty Entries</div><div class="text-3xl font-black">{{ number_format($totals['entries']) }}</div></div>
            </div>

            <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000]">
                <div class="p-5 border-b-2 border-black"><h3 class="font-extrabold text-lg">Monthly Artist Earnings</h3><p class="text-sm font-bold text-black/50 mt-1">See exactly how much each artist earned in each month.</p></div>
                <div class="p-5 overflow-x-auto"><table class="w-full min-w-[850px] text-sm"><thead><tr class="border-b-2 border-black"><th class="text-left py-3">Period</th><th class="text-left">Artist</th><th class="text-right">Streams</th><th class="text-right">Gross Revenue</th><th class="text-right">Artist Earnings</th><th class="text-right">TeleMusic Fee</th><th class="text-right">Entries</th><th class="text-right">Actions</th></tr></thead><tbody>
                @forelse($artistMonthlyRows as $row)
                    <tr class="border-b border-black/10 hover:bg-brand-500/10"><td class="py-3 font-bold">{{ date('F', mktime(0,0,0,$row->month,1)) }} {{ $row->year }}</td><td class="font-extrabold">{{ $row->artist->artist_name ?? 'N/A' }}</td><td class="text-right font-bold">{{ number_format($row->total_streams) }}</td><td class="text-right font-bold">${{ number_format($row->gross_revenue,2) }}</td><td class="text-right font-black text-emerald-700">${{ number_format($row->artist_earnings,2) }}</td><td class="text-right font-bold text-black/60">${{ number_format($row->telemusic_fee,2) }}</td><td class="text-right font-bold">{{ $row->entries_count }}</td><td class="text-right"><a href="{{ route('admin.royalties', ['artist_id'=>$row->artist_id, 'year'=>$row->year, 'month'=>$row->month]) }}" class="font-extrabold underline decoration-brand-500 decoration-2">View / Edit Entries</a></td></tr>
                @empty<tr><td colspan="8" class="py-8 text-center font-bold text-black/40">No artist royalty data.</td></tr>@endforelse
                </tbody></table><div class="mt-4">{{ $artistMonthlyRows->links() }}</div></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000]">
                    <div class="p-5 border-b-2 border-black"><h3 class="font-extrabold text-lg">Monthly Performance</h3></div>
                    <div class="p-5 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b-2 border-black"><th class="text-left py-3">Period</th><th class="text-right">Streams</th><th class="text-right">Revenue</th></tr></thead><tbody>
                    @forelse($monthlyStats as $stat)<tr class="border-b border-black/10"><td class="py-3 font-bold">{{ date('F', mktime(0,0,0,$stat->month,1)) }} {{ $stat->year }}</td><td class="text-right font-bold">{{ number_format($stat->total_streams) }}</td><td class="text-right font-black">${{ number_format($stat->total_revenue,2) }}</td></tr>@empty<tr><td colspan="3" class="py-8 text-center font-bold text-black/40">No royalty data.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000]">
                    <div class="p-5 border-b-2 border-black"><h3 class="font-extrabold text-lg">Revenue by Store</h3></div>
                    <div class="p-5">@forelse($storeAnalytics as $row)<div class="flex items-center justify-between gap-4 py-3 border-b border-black/10"><span class="flex items-center gap-3 font-bold"><x-store-logo :store="$row->store" size="8" />{{ $row->store->name ?? 'N/A' }}</span><span class="text-right"><strong class="block">${{ number_format($row->total_revenue,2) }}</strong><small class="block font-bold text-black/50">{{ number_format($row->total_streams) }} streams · {{ $row->entries_count }} entries</small><a href="{{ route('admin.royalties', array_filter(['store_id'=>$row->store_id, 'artist_id'=>$filters['artist_id'] ?? null, 'year'=>$filters['year'] ?? null, 'month'=>$filters['month'] ?? null])) }}" class="text-xs font-extrabold underline decoration-brand-500 decoration-2">View / Edit</a></span></div>@empty<p class="py-8 text-center font-bold text-black/40">No royalty data.</p>@endforelse</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
