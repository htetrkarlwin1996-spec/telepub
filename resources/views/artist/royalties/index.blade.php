<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('My Royalties') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            @php
                $artist = auth()->user()->artist;
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                @foreach(\App\Models\Royalty::TYPES as $type => $label)
                <div class="bg-white border-2 border-black p-4 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <div class="text-xs font-extrabold uppercase text-black/50">{{ $label }}</div>
                    <div class="text-2xl font-black mt-1">${{ number_format($balanceBreakdown[$type], 2) }}</div>
                </div>
                @endforeach
                <div class="bg-brand-500 border-2 border-black p-4 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <div class="text-xs font-extrabold uppercase text-black/60">Total Balance</div>
                    <div class="text-2xl font-black mt-1">${{ number_format($balanceBreakdown->sum(), 2) }}</div>
                </div>
            </div>
            <div class="bg-brand-500 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6 mb-8">
                <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Total Earnings</div>
                <div class="text-4xl font-black text-black mt-1">${{ number_format($totalRoyalties, 2) }}</div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Monthly Breakdown -->
                <div class="lg:col-span-2 bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Monthly Breakdown</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-sm">
                            <thead><tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Period</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Amount</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Streams</th>
                            </tr></thead>
                            <tbody>
                                @forelse($monthlyRoyalties as $mr)
                                <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                    <td class="py-3 px-2 font-bold text-black">{{ date('F', mktime(0,0,0,$mr->month,1)) }} {{ $mr->year }}</td>
                                    <td class="py-3 px-2 text-right font-black text-black">+${{ number_format($mr->total, 2) }}</td>
                                    <td class="py-3 px-2 text-right font-bold text-black/70">{{ number_format($mr->total_streams) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="py-8 text-center font-bold text-black/40">No royalty data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Store Breakdown -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">By Store</h3>
                    </div>
                    <div class="p-6">
                        @forelse($storeBreakdown as $sb)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0">
                            <span class="flex items-center gap-2 font-bold text-black">
                                <x-store-logo :store="$sb->store" size="5" />
                                {{ $sb->store->name ?? 'N/A' }}
                            </span>
                            <span class="font-black text-black">${{ number_format($sb->total, 2) }}</span>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No data</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Royalty History -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6 border-b-2 border-black">
                    <h3 class="font-extrabold text-lg text-black tracking-tight">Transaction History</h3>
                </div>
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b-2 border-black">
                            <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Store</th>
                            <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Type</th>
                            <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Period</th>
                            <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Earnings</th>
                            <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Streams</th>
                            <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Notes</th>
                        </tr></thead>
                        <tbody>
                            @forelse($royalties as $royalty)
                            @php
                                $itemArtistShare = $artist->getArtistShareAttribute($royalty->amount);
                                $hasCollaborators = $royalty->relationLoaded('album') && $royalty->album && $royalty->album->relationLoaded('collaboratingArtists') && $royalty->album->collaboratingArtists->count() > 0;
                            @endphp
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <x-store-logo :store="$royalty->store" size="5" />
                                        <span class="font-bold text-black">{{ $royalty->store->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2 font-bold text-black/70">{{ $royalty->royalty_type_label }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70">{{ $royalty->month }}/{{ $royalty->year }}</td>
                                <td class="py-3 px-2 text-right font-black text-emerald-600">+${{ number_format($itemArtistShare, 2) }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black/70">{{ $royalty->streams ? number_format($royalty->streams) : '-' }}</td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50">{{ $royalty->notes ?? '-' }}</td>
                            </tr>
                            @if($hasCollaborators)
                                <tr class="bg-purple-50 border-b border-black/10">
                                    <td colspan="6" class="py-2 px-6 text-xs font-bold text-black/70">
                                        <span class="font-extrabold uppercase text-[10px]">Collaborator Revenue Split:</span>
                                        @foreach($royalty->album->collaboratingArtists as $collab)
                                            <span class="ml-3 inline-flex items-center gap-1">
                                                {{ $collab->artist_name }} ({{ $collab->pivot->share_percentage }}%)
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            @empty
                            <tr><td colspan="6" class="py-8 text-center font-bold text-black/40">No royalties recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $royalties->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
