<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">
            {{ __('Artist Dashboard') }} — <span class="text-brand-500">{{ $artist->artist_name }}</span>
        </h2>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
            <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
            @endif

            <!-- Artist Balance Hero -->
            <div class="bg-brand-500 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Royalties</div>
                        <div class="text-3xl font-black text-black mt-1">${{ number_format($balanceBreakdown['royalties'], 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Publishing Rights</div>
                        <div class="text-3xl font-black text-black mt-1">${{ number_format($balanceBreakdown['publishing_rights'], 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Composer Rights</div>
                        <div class="text-3xl font-black text-black mt-1">${{ number_format($balanceBreakdown['composer_rights'], 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Mechanical Royalties</div>
                        <div class="text-3xl font-black text-black mt-1">${{ number_format($balanceBreakdown['mechanical_royalties'], 2) }}</div>
                    </div>
                    <div class="bg-white/30 border-2 border-black p-4">
                        <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Total Balance</div>
                        <div class="text-3xl font-black text-black mt-1">${{ number_format($balanceBreakdown->sum(), 2) }}</div>
                    </div>
                </div>
                @if($artist->available_balance > 0)
                <div class="mt-4">
                    <a href="{{ route('artist.withdrawals.create') }}" class="inline-flex items-center px-4 py-2 bg-white border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">
                        Withdraw Funds
                    </a>
                </div>
                @endif
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-wider text-black/50">Total Songs</div>
                            <div class="mt-1 text-3xl font-black text-black">{{ $totalSongs }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-wider text-black/50">Total Albums</div>
                            <div class="mt-1 text-3xl font-black text-black">{{ $totalAlbums }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-wider text-black/50">Total Earnings</div>
                            <div class="mt-1 text-3xl font-black text-black">${{ number_format($totalRoyalties, 2) }}</div>
                        </div>
                        <div class="w-12 h-12 bg-emerald-400 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-wider text-black/50">Total Streams</div>
                            <div class="mt-1 text-3xl font-black text-black">{{ number_format($totalStreams) }}</div>
                        </div>
                        <div class="w-12 h-12 bg-blue-200 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Albums -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black flex justify-between items-center">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Recent Albums</h3>
                        <a href="{{ route('artist.albums.create') }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-sm">+ New Album</a>
                    </div>
                    <div class="p-6">
                        @forelse($albums as $album)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0">
                            <div>
                                <a href="{{ route('artist.albums.show', $album) }}" class="font-bold text-black hover:underline hover:decoration-brand-500 hover:decoration-2">{{ $album->title }}</a>
                                <div class="text-xs font-semibold text-black/50">{{ $album->songs_count }} tracks</div>
                            </div>
                            <span class="text-xs font-bold border border-black px-2 py-1
                                @if($album->status == 'draft') bg-gray-100 text-black
                                @elseif($album->status == 'submitted') bg-amber-200 text-black
                                @elseif($album->status == 'approved') bg-emerald-400 text-black
                                @else bg-red-200 text-black
                                @endif">
                                {{ ucfirst($album->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No albums yet. <a href="{{ route('artist.albums.create') }}" class="text-black underline decoration-brand-500 decoration-2">Create one</a></p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Royalties -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Recent Royalties</h3>
                    </div>
                    <div class="p-6">
                        @forelse($recentRoyalties as $royalty)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0">
                            <div>
                                <div class="font-bold text-black">{{ $royalty->store->name ?? 'N/A' }}</div>
                                <div class="text-xs font-semibold text-black/50">{{ $royalty->month }}/{{ $royalty->year }}</div>
                            </div>
                            <span class="font-black text-black">+${{ number_format($artist->getArtistShareAttribute($royalty->amount), 2) }}</span>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No royalties recorded yet.</p>
                        @endforelse
                        @if($recentRoyalties->isNotEmpty())
                        <div class="mt-4">
                            <a href="{{ route('artist.royalties') }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-sm">View all royalties →</a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Distributions -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] lg:col-span-2">
                    <div class="p-6 border-b-2 border-black flex justify-between items-center">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Distribution Status</h3>
                        <a href="{{ route('artist.distributions.create') }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-sm">+ New Distribution</a>
                    </div>
                    <div class="p-6">
                        @forelse($distributions as $dist)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0">
                            <div>
                                <div class="font-bold text-black">{{ $dist->song->title ?? 'N/A' }}</div>
                                <div class="text-xs font-semibold text-black/50">{{ $dist->store->name ?? 'N/A' }}</div>
                            </div>
                            <span class="text-xs font-bold border border-black px-2 py-1
                                @if($dist->status == 'pending') bg-gray-100 text-black
                                @elseif($dist->status == 'submitted') bg-amber-200 text-black
                                @elseif($dist->status == 'approved') bg-blue-200 text-black
                                @elseif($dist->status == 'live') bg-emerald-400 text-black
                                @else bg-red-200 text-black
                                @endif">
                                {{ ucfirst($dist->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-4">No distributions yet. <a href="{{ route('artist.distributions.create') }}" class="text-black underline decoration-brand-500 decoration-2">Distribute your music</a></p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
