<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Top Actions -->
            <div class="flex items-center justify-between mb-6">
                <a href="{{ route('admin.releases') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-black/60 hover:text-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Back to Releases
                </a>
                <a href="{{ route('admin.releases.edit-step1', $album) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-200 border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Release
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Release Header -->
            <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3">
                    <div class="aspect-square bg-gray-100 border-r-2 border-black">
                        @if($album->cover_art)
                            <img src="{{ Storage::url($album->cover_art) }}" alt="{{ $album->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-brand-100">
                                <svg class="w-20 h-20 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="col-span-2 p-8">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-3xl font-black text-black">{{ $album->title }}</h1>
                                <p class="text-sm font-bold text-black/50 mt-1">
                                    By {{ $album->artist->artist_name ?? 'Unknown' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 text-xs font-extrabold uppercase border-2 border-black
                                @if($album->status === 'approved') bg-green-200
                                @elseif($album->status === 'rejected') bg-red-200
                                @else bg-yellow-200 @endif">
                                {{ $album->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Type</span>
                                <p class="font-extrabold">{{ ucfirst($album->release_type) }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Genre</span>
                                <p class="font-extrabold">{{ $album->genre }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Release Date</span>
                                <p class="font-extrabold">{{ $album->release_date?->format('M d, Y') ?? 'TBA' }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Price</span>
                                <p class="font-extrabold">${{ number_format($album->price ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Copyright</span>
                                <p class="font-extrabold">{{ $album->copyright_holder ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Phonogram</span>
                                <p class="font-extrabold">{{ $album->phonogram_right_holder ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Artist Email</span>
                                <p class="font-extrabold">{{ $album->artist->user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-black/50">Submitted</span>
                                <p class="font-extrabold">{{ $album->created_at->format('M d, Y g:i A') }}</p>
                            </div>
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
                    </div>
                </div>
            </div>

            <!-- Track List + ISRC Management -->
            <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden mb-8">
                <div class="p-6 border-b-2 border-black">
                    <h2 class="font-extrabold text-lg uppercase">Tracks ({{ $album->songs->count() }})</h2>
                </div>

                @if($album->songs->count() > 0)
                    @if($album->status === 'submitted')
                        <!-- ISRC Entry Form (approval) -->
                        <form method="POST" action="{{ route('admin.releases.approve', $album) }}">
                            @csrf
                            <div class="divide-y-2 divide-black">
                                @foreach($album->songs->sortBy('track_number') as $song)
                                    <div class="p-6">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-sm font-extrabold text-black/40 w-6">{{ $song->track_number }}.</span>
                                                    <div>
                                                        <h3 class="font-extrabold text-black">{{ $song->title }}
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
                                        </div>

                                        <div class="mt-3 ml-9">
                                            <label class="font-extrabold text-xs uppercase text-black/60">ISRC Code</label>
                                            <div class="flex items-center gap-3 mt-1">
                                                <input type="text" name="songs[{{ $song->id }}][isrc_code]"
                                                    value="{{ $song->isrc_code }}"
                                                    class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                                                    placeholder="{{ $song->request_new_isrc ? 'Requested — Enter ISRC' : 'Enter ISRC code' }}">
                                                @if($song->request_new_isrc)
                                                    <span class="px-2 py-1 text-[9px] font-extrabold uppercase bg-yellow-200 border-2 border-black">New Requested</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-2 ml-9 flex flex-wrap gap-x-4 gap-y-1 text-[10px] font-bold text-black/40">
                                            @if($song->composers)<span>Composer: {!! formatCredit($song->composers) !!}</span>@endif
                                            @if($song->producers)<span>Producer: {!! formatCredit($song->producers) !!}</span>@endif
                                            @if($song->lyricist)<span>Lyricist: {!! formatCredit($song->lyricist) !!}</span>@endif
                                            @if($song->vocals)<span>Vocals: {!! formatCredit($song->vocals) !!}</span>@endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-6 flex items-center gap-3 border-t-2 border-black bg-gray-50">
                                <button type="submit" class="px-6 py-3 bg-green-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                                    ✓ Approve Release
                                </button>
                                <button type="button" onclick="document.getElementById('reject-form').classList.toggle('hidden')" class="px-6 py-3 bg-red-200 border-2 border-black font-extrabold text-sm uppercase hover:bg-red-300 transition-all">
                                    ✕ Reject
                                </button>
                            </div>
                        </form>

                        <!-- Reject Form -->
                        <div id="reject-form" class="hidden p-6 border-t-2 border-black bg-red-50">
                            <form method="POST" action="{{ route('admin.releases.reject', $album) }}">
                                @csrf
                                <label class="block font-extrabold text-sm uppercase mb-2 text-red-700">Rejection Reason</label>
                                <textarea name="rejection_reason" rows="3" required
                                    class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0"
                                    placeholder="Explain why the release was rejected..."></textarea>
                                <button type="submit" class="mt-3 px-6 py-3 bg-red-500 border-2 border-black font-extrabold text-sm uppercase text-white hover:bg-red-600 transition-all">
                                    Confirm Rejection
                                </button>
                            </form>
                        </div>
                    @elseif($album->status === 'approved')
                        <!-- View approved tracks + ISRC update -->
                        <div class="divide-y-2 divide-black">
                            @foreach($album->songs->sortBy('track_number') as $song)
                                <div class="p-6">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-extrabold text-black/40 w-6">{{ $song->track_number }}.</span>
                                                <div>
                                                    <h3 class="font-extrabold text-black">{{ $song->title }}
                                                        @if($song->explicit)
                                                            <span class="ml-1 px-1.5 py-0.5 bg-red-200 border border-black text-[9px] font-extrabold uppercase">E</span>
                                                        @endif
                                                    </h3>
                                                    <p class="text-xs font-bold text-black/50">
                                                        {!! formatCredit($song->primary_artists) ?: e($album->artist->artist_name) !!}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-extrabold text-green-700">
                                                ISRC: {{ $song->isrc_code ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- ISRC Update Form -->
                        <div class="p-6 border-t-2 border-black bg-gray-50">
                            <button type="button" onclick="document.getElementById('isrc-update-form').classList.toggle('hidden')" class="px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs uppercase hover:bg-brand-300 transition-all">
                                Update ISRC Codes
                            </button>

                            <div id="isrc-update-form" class="hidden mt-4">
                                <form method="POST" action="{{ route('admin.releases.update-isrc', $album) }}">
                                    @csrf
                                    @foreach($album->songs->sortBy('track_number') as $song)
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-bold text-xs w-32 truncate">{{ $song->title }}:</span>
                                            <input type="text" name="songs[{{ $song->id }}][isrc_code]"
                                                value="{{ $song->isrc_code }}"
                                                class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                                        </div>
                                    @endforeach
                                    <button type="submit" class="mt-3 px-4 py-2 bg-black text-white border-2 border-black font-extrabold text-xs uppercase hover:bg-brand-500 hover:text-black transition-all">
                                        Save ISRC Updates
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Rejected - show details -->
                        <div class="p-6">
                            @foreach($album->songs->sortBy('track_number') as $song)
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-sm font-extrabold text-black/40 w-6">{{ $song->track_number }}.</span>
                                    <div>
                                        <h3 class="font-extrabold text-black">{{ $song->title }}</h3>
                                        <p class="text-xs font-bold text-black/50">{!! formatCredit($song->primary_artists) ?: e($album->artist->artist_name) !!}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="p-8 text-center">
                        <p class="font-bold text-black/40">No tracks found.</p>
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
                        <p class="font-bold text-black/40">No distributions created yet.</p>
                    </div>
                @endif
            </div>

            <!-- Rejection Info -->
            @if($album->rejection_reason)
                <div class="bg-red-100 border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <h2 class="font-extrabold text-sm uppercase text-red-700 mb-2">Rejection Reason</h2>
                    <p class="font-bold text-sm text-red-800">{{ $album->rejection_reason }}</p>
                </div>
            @endif

            @if($album->approved_at)
                <div class="bg-green-100 border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6 mt-6">
                    <h2 class="font-extrabold text-sm uppercase text-green-700 mb-2">Approved</h2>
                    <p class="font-bold text-sm text-green-800">Approved on {{ $album->approved_at->format('M d, Y g:i A') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
