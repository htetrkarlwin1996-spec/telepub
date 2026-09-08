<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ $album->title }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('artist.albums.edit', $album) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-sm">Edit Album</a>
                <a href="{{ route('artist.songs.create', ['album_id' => $album->id]) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ Add Song</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Album Info -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    @if($album->cover_art)
                    <img src="{{ asset('storage/'.$album->cover_art) }}" class="w-full border-2 border-black mb-4">
                    @else
                    <div class="w-full aspect-square bg-gray-100 border-2 border-black flex items-center justify-center font-bold text-black/40 mb-4">No Cover Art</div>
                    @endif
                    <h3 class="font-extrabold text-lg text-black tracking-tight">{{ $album->title }}</h3>
                    <p class="text-sm font-bold text-black/60">{{ $album->genre ?? 'No genre' }} • {{ $album->songs->count() }} tracks</p>
                    <p class="text-sm font-semibold text-black/70">Status: <span class="font-extrabold text-black">{{ ucfirst($album->status) }}</span></p>
                    @if($album->release_date)<p class="text-sm font-semibold text-black/60">Release: {{ $album->release_date->format('M d, Y') }}</p>@endif
                    @if($album->upc_code)<p class="text-sm font-semibold text-black/60">UPC: {{ $album->upc_code }}</p>@endif
                    @if($album->label)<p class="text-sm font-semibold text-black/60">Label: {{ $album->label }}</p>@endif
                </div>

                <!-- Tracklist -->
                <div class="lg:col-span-2 bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-6 border-b-2 border-black">
                        <h3 class="font-extrabold text-lg text-black tracking-tight">Tracklist</h3>
                    </div>
                    <div class="p-6">
                        @forelse($songs as $song)
                        <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0 hover:bg-brand-500/10 transition-colors px-2">
                            <div class="flex items-center">
                                <span class="font-bold text-black/40 w-8 text-sm">{{ $song->track_number ?? '-' }}</span>
                                <div>
                                    <a href="{{ route('artist.songs.show', $song) }}" class="font-bold text-black hover:underline hover:decoration-brand-500 hover:decoration-2">{{ $song->title }}</a>
                                    @if($song->featuring)<span class="text-xs font-semibold text-black/50 ml-1">ft. {{ $song->featuring }}</span>@endif
                                    <div class="text-xs font-semibold text-black/50">
                                        @if($song->duration)<span>{{ $song->duration }} • </span>@endif
                                        <span class="text-xs font-bold border border-black px-1.5 py-0.5
                                            @if($song->status == 'draft') bg-gray-100 text-black
                                            @elseif($song->status == 'submitted') bg-amber-200 text-black
                                            @elseif($song->status == 'approved') bg-emerald-400 text-black
                                            @else bg-red-200 text-black
                                            @endif">{{ ucfirst($song->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="font-bold text-black/40 text-center py-6">No songs in this album yet. <a href="{{ route('artist.songs.create', ['album_id' => $album->id]) }}" class="text-black underline decoration-brand-500 decoration-2">Add your first song</a></p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
