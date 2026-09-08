<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">{{ __('Platform Analytics') }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly Stats -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] lg:col-span-2">
                    <div class="p-5 border-b-2 border-black">
                        <h3 class="text-lg font-extrabold text-black tracking-tight">Monthly Platform Stats (Last 12 Months)</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-black">
                                    <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Period</th>
                                    <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Total Streams</th>
                                    <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Total Downloads</th>
                                    <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyStats as $stat)
                                <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                    <td class="py-3 px-2 font-bold text-black">{{ date('F', mktime(0,0,0,$stat->month,1)) }} {{ $stat->year }}</td>
                                    <td class="py-3 px-2 text-right font-bold text-black/70">{{ number_format($stat->total_streams) }}</td>
                                    <td class="py-3 px-2 text-right font-bold text-black/70">{{ number_format($stat->total_downloads) }}</td>
                                    <td class="py-3 px-2 text-right font-black text-black">${{ number_format($stat->total_revenue, 2) }}</td>
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
                    <div class="p-5 border-b-2 border-black">
                        <h3 class="text-lg font-extrabold text-black tracking-tight">Revenue by Store</h3>
                    </div>
                    <div class="p-6">
                        @forelse($storeAnalytics as $sa)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0 hover:bg-brand-500/10 px-2 -mx-2 transition-colors">
                            <div class="flex items-center gap-2">
                                <x-store-logo :store="$sa->store" size="6" />
                                <div class="font-bold text-black">{{ $sa->store->name ?? 'N/A' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-black">${{ number_format($sa->total_revenue, 2) }}</div>
                                <div class="text-xs font-bold text-black/50">{{ number_format($sa->total_streams) }} streams</div>
                            </div>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No data.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
