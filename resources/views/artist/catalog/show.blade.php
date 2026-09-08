<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Back Link -->
            <a href="{{ route('artist.catalog.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-black/60 hover:text-black mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                Back to Catalog
            </a>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Release Header -->
            <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3">
                    <!-- Cover Art -->
                    <div class="aspect-square bg-gray-100 border-r-2 border-black">
                        @if($album->cover_art)
                            <img src="{{ Storage::url($album->cover_art) }}" alt="{{ $album->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-brand-100">
                                <svg class="w-20 h-20 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="col-span-2 p-8">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-3xl font-black text-black">{{ $album->title }}</h1>
                                <p class="text-sm font-bold text-black/50 mt-1">
                                    {{ ucfirst($album->release_type) }} · {{ $album->genre }}
                                </p>
                            </div>
                            <span class="px-3 py-1 text-xs font-extrabold uppercase border-2 border-black
                                @if($album->status === 'approved') bg-green-200
                                @elseif($album->status === 'rejected') bg-red-200
                                @elseif($album->status === 'submitted') bg-yellow-200
                                @else bg-gray-200 @endif">
                                {{ $album->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Release Date</span>
                                <p class="font-extrabold">{{ $album->release_date?->format('M d, Y') ?? 'TBA' }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Physical Release</span>
                                <p class="font-extrabold">{{ $album->physical_release_date?->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Price</span>
                                <p class="font-extrabold">${{ number_format($album->price ?? 0, 2) }}</p>
                            <div class="col-span-2">
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Revenue Share</span>
                                <p class="font-extrabold">{{ $album->artist->artist_name }} (Primary): {{ $album->artist->revenue_share_percentage }}% Artist / {{ $album->artist->teleMusicFeePercentage }}% TeleMusic</p>
                                @if($album->relationLoaded('collaboratingArtists') && $album->collaboratingArtists->count() > 0)
                                    @foreach($album->collaboratingArtists as $collab)
                                        <p class="font-extrabold text-xs text-black/70 ml-4">
                                            {{ $collab->artist_name }}: {{ $collab->pivot->share_percentage }}% of net revenue
                                        </p>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        @if($album->status === 'draft')
                            <div class="mt-6 flex gap-3">
                                <a href="{{ route('artist.catalog.step2', $album) }}" class="px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs uppercase hover:bg-brand-300 transition-all">
                                    Continue Setup
                                </a>
                                <a href="{{ route('artist.catalog.edit', $album) }}" class="px-4 py-2 bg-gray-200 border-2 border-black font-extrabold text-xs uppercase hover:bg-gray-300 transition-all">
                                    Edit
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Track List -->
            <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden mb-8">
                <div class="p-6 border-b-2 border-black">
                    <h2 class="font-extrabold text-lg uppercase">Tracks ({{ $album->songs->count() }})</h2>
                </div>

                @if($album->songs->count() > 0)
                    <div class="divide-y-2 divide-black">
                        @foreach($album->songs->sortBy('track_number') as $song)
                            <div class="p-6 hover:bg-brand-50 transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-extrabold text-black/40 w-6">{{ $song->track_number }}.</span>
                                            <div>
                                                <h3 class="font-extrabold text-black">{{ $song->title }}
                                                    @if($song->version)
                                                        <span class="text-xs font-bold text-black/50">({{ $song->version }})</span>
                                                    @endif
                                                    @if($song->explicit)
                                                        <span class="ml-1 px-1.5 py-0.5 bg-red-200 border border-black text-[9px] font-extrabold uppercase">E</span>
                                                    @endif
                                                </h3>
                                                <p class="text-xs font-bold text-black/50">
                                                    {!! formatCredit($song->primary_artists) ?: e($album->artist->artist_name) !!}
                                                    @if($song->featuring) · feat. {!! formatCredit($song->featuring) !!} @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right text-xs font-bold text-black/60">
                                        @if($song->isrc_code)
                                            <div>ISRC: {{ $song->isrc_code }}</div>
                                        @elseif($song->request_new_isrc)
                                            <div class="text-yellow-600">ISRC requested</div>
                                        @else
                                            <div class="text-black/30">No ISRC</div>
                                        @endif
                                        @if($song->duration > 0)
                                            <div>{{ gmdate('i:s', $song->duration) }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Credits -->
                                <div class="mt-2 ml-9 flex flex-wrap gap-x-4 gap-y-1 text-[10px] font-bold text-black/40">
                                    @if($song->composers)<span>Composer: {!! formatCredit($song->composers) !!}</span>@endif
                                    @if($song->lyricist)<span>Lyricist: {!! formatCredit($song->lyricist) !!}</span>@endif
                                    @if($song->producers)<span>Producer: {!! formatCredit($song->producers) !!}</span>@endif
                                    @if($song->vocals)<span>Vocals: {!! formatCredit($song->vocals) !!}</span>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <p class="font-bold text-black/40">No tracks added yet.</p>
                    </div>
                @endif
            </div>

            <!-- Distribution / Store Status -->
            <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden mb-8">
                <div class="p-6 border-b-2 border-black">
                    <h2 class="font-extrabold text-lg uppercase">Distribution Status</h2>
                </div>

                @php
                    $groupedDistributions = $album->distributions->groupBy('store_id');
                @endphp

                @if($groupedDistributions->count() > 0)
                    <div class="divide-y-2 divide-black">
                        @foreach($groupedDistributions as $storeId => $dists)
                            @php $store = $dists->first()->store; @endphp
                            <div class="p-4 flex items-center gap-4">
                                <x-store-logo :store="$store" size="8" />
                                <div class="flex-1">
                                    <p class="font-extrabold text-sm">{{ $store->name ?? 'Unknown Store' }}</p>
                                    <p class="text-[10px] font-bold text-black/50">{{ $dists->count() }} track(s)</p>
                                </div>
                                <span class="px-2 py-1 text-[10px] font-extrabold uppercase border-2 border-black
                                    @if($dists->first()->status === 'live') bg-green-200
                                    @elseif($dists->first()->status === 'approved') bg-blue-200
                                    @else bg-yellow-200 @endif">
                                    {{ $dists->first()->status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <p class="font-bold text-black/40">Not distributed to any stores yet.</p>
                    </div>
                @endif
            </div>

            <!-- Admin Notes (for rejected releases) -->
            @if($album->rejection_reason)
                <div class="bg-red-100 border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <h2 class="font-extrabold text-sm uppercase text-red-700 mb-2">Rejection Reason</h2>
                    <p class="font-bold text-sm text-red-800">{{ $album->rejection_reason }}</p>
                </div>
            @endif

            @if($album->isApproved())
                <div class="bg-green-100 border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6 mt-8">
                    <h2 class="font-extrabold text-sm uppercase text-green-700 mb-2">✓ Approved</h2>
                    <p class="font-bold text-sm text-green-800">This release has been approved on {{ $album->approved_at->format('M d, Y g:i A') }}.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
