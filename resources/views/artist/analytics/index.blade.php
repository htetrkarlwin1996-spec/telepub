<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Analytics') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <form method="GET" action="{{ route('artist.analytics') }}" class="bg-white border-2 border-black shadow-[4px_4px_0_#000] p-5 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div><x-input-label for="store_id" value="Store" /><select id="store_id" name="store_id" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Stores</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected(($filters['store_id'] ?? '') == $store->id)>{{ $store->name }}</option>@endforeach</select></div>
                    <div><x-input-label for="month" value="Month" /><select id="month" name="month" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Months</option>@for($month=1;$month<=12;$month++)<option value="{{ $month }}" @selected(($filters['month'] ?? '') == $month)>{{ date('F', mktime(0,0,0,$month,1)) }}</option>@endfor</select></div>
                    <div><x-input-label for="year" value="Year" /><select id="year" name="year" class="mt-1 w-full border-2 border-black font-bold"><option value="">All Years</option>@foreach($years as $year)<option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>@endforeach</select></div>
                    <button class="px-4 py-2.5 bg-brand-500 border-2 border-black font-extrabold uppercase shadow-[3px_3px_0_#000]">Apply</button>
                    <a href="{{ route('artist.analytics') }}" class="px-4 py-2.5 bg-white border-2 border-black text-center font-extrabold uppercase">All Time</a>
                </div>
            </form>

            <!-- Totals -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 bg-brand-500 border-2 border-black">
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-black/50">Total Streams</span>
                    </div>
                    <div class="text-3xl font-black text-black">{{ number_format($totalStreams) }}</div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 bg-emerald-400 border-2 border-black">
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-black/50">Total Earnings</span>
                    </div>
                    <div class="text-3xl font-black text-black">${{ number_format($totalRevenue, 2) }}</div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 bg-brand-500 border-2 border-black">
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-black/50">Royalty Entries</span>
                    </div>
                    <div class="text-3xl font-black text-black">{{ number_format($totalEntries) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly Stats -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] lg:col-span-2">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Monthly Performance</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-black">
                                    <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Period</th>
                                    <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Streams</th>
                                    <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Downloads</th>
                                    <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyData as $data)
                                <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                    <td class="py-3 px-2 font-bold text-black">{{ date('F', mktime(0,0,0,$data->month,1)) }} {{ $data->year }}</td>
                                    <td class="py-3 px-2 text-right font-bold text-black/70">{{ number_format($data->total_streams) }}</td>
                                    <td class="py-3 px-2 text-right font-bold text-black/70">{{ number_format($data->total_downloads) }}</td>
                                    <td class="py-3 px-2 text-right font-black text-black">+${{ number_format($data->total_revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="py-8 text-center font-bold text-black/40">No analytics data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Store Breakdown -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Revenue by Store</h3>
                    </div>
                    <div class="p-6">
                        @forelse($storeData as $sd)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0">
                            <div class="flex items-center gap-2">
                                <x-store-logo :store="$sd->store" size="6" />
                                <div>
                                    <div class="font-bold text-black">{{ $sd->store->name ?? 'N/A' }}</div>
                                    <div class="text-xs font-bold text-black/50">{{ number_format($sd->total_streams) }} streams</div>
                                </div>
                            </div>
                            <span class="font-black text-black">${{ number_format($sd->total_revenue, 2) }}</span>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No data</p>
                        @endforelse
                    </div>
                </div>

                <!-- Top Songs -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Top Songs</h3>
                    </div>
                    <div class="p-6">
                        @forelse($songPerformance as $sp)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0">
                            <div>
                                <div class="font-bold text-black">{{ $sp->song->title ?? 'N/A' }}</div>
                                <div class="text-xs font-bold text-black/50">{{ number_format($sp->total_streams) }} streams</div>
                            </div>
                            <span class="font-black text-black">${{ number_format($sp->total_revenue, 2) }}</span>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No data</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
