<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <a href="{{ route('admin.releases') }}" class="text-sm font-extrabold uppercase hover:underline">← Releases</a>
                <h1 class="text-3xl font-black uppercase mt-3">Bulk Create from Spotify</h1>
                <p class="mt-2 font-bold text-black/60">Paste up to 20 Spotify album or single URLs. Metadata comes from Spotify through RapidAPI.</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-2 border-black shadow-[3px_3px_0_#000]">
                    <ul class="list-disc ml-5 font-bold text-sm">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.releases.bulk-fetch') }}" class="bg-white border-2 border-black p-6 shadow-[5px_5px_0_#000]">
                @csrf
                <label for="spotify_references" class="block font-black uppercase mb-2">Spotify Album / Single URLs or IDs</label>
                <textarea id="spotify_references" name="spotify_references" rows="6" required placeholder="One URL per line&#10;https://open.spotify.com/album/..." class="w-full border-2 border-black font-bold focus:ring-brand-500">{{ old('spotify_references', $spotifyReferences) }}</textarea>
                <button class="mt-4 px-6 py-3 bg-brand-500 border-2 border-black font-black uppercase shadow-[4px_4px_0_#000] hover:translate-x-0.5 hover:translate-y-0.5">Fetch Metadata</button>
            </form>

            @if(count($releases))
                <form method="POST" action="{{ route('admin.releases.bulk-store') }}" class="mt-8 space-y-6">
                    @csrf
                    @foreach($releases as $releaseIndex => $release)
                        <section class="bg-white border-2 border-black shadow-[5px_5px_0_#000] overflow-hidden">
                            <div class="p-5 bg-yellow-100 border-b-2 border-black flex gap-5 items-start">
                                @if($release['cover_url'])<img src="{{ $release['cover_url'] }}" class="w-24 h-24 object-cover border-2 border-black" alt="">@endif
                                <div class="flex-1">
                                    <h2 class="text-xl font-black">{{ $release['title'] }}</h2>
                                    <p class="font-bold text-black/60">{{ implode(', ', $release['artist_names']) }} · {{ strtoupper($release['release_type']) }} · {{ $release['release_date'] }}</p>
                                    <a href="{{ $release['spotify_url'] }}" target="_blank" rel="noopener" class="inline-block mt-2 text-xs font-black uppercase underline">View on Spotify</a>
                                    <label class="block mt-4 text-xs font-black uppercase">TeleMusic Artist</label>
                                    <select name="releases[{{ $releaseIndex }}][artist_id]" required class="mt-1 w-full max-w-md border-2 border-black font-bold">
                                        <option value="">Select artist</option>
                                        @foreach($artists as $artist)<option value="{{ $artist->id }}" @selected(old("releases.$releaseIndex.artist_id") == $artist->id)>{{ $artist->artist_name }}</option>@endforeach
                                    </select>
                                </div>
                            </div>

                            @foreach(['spotify_id','title','release_type','release_date','cover_url','label','upc_code','copyright'] as $field)
                                <input type="hidden" name="releases[{{ $releaseIndex }}][{{ $field }}]" value="{{ $release[$field] ?? '' }}">
                            @endforeach
                            @foreach($release['artist_names'] as $artistName)<input type="hidden" name="releases[{{ $releaseIndex }}][artist_names][]" value="{{ $artistName }}">@endforeach

                            <div class="p-5 overflow-x-auto">
                                <table class="w-full min-w-[700px]">
                                    <thead><tr class="border-b-2 border-black text-left text-xs uppercase"><th class="py-3">#</th><th>Track</th><th>Duration</th><th class="w-64">ISRC (required)</th></tr></thead>
                                    <tbody class="divide-y-2 divide-black/20">
                                    @foreach($release['tracks'] as $trackIndex => $track)
                                        <tr>
                                            <td class="py-4 font-black">{{ $track['track_number'] }}</td>
                                            <td class="font-bold">{{ $track['title'] }} @if($track['explicit'])<span class="text-xs bg-black text-white px-1">E</span>@endif</td>
                                            <td class="font-bold text-black/60">{{ $track['duration'] }}</td>
                                            <td><input name="releases[{{ $releaseIndex }}][tracks][{{ $trackIndex }}][isrc_code]" value="{{ old("releases.$releaseIndex.tracks.$trackIndex.isrc_code") }}" required placeholder="USRC17607839" class="w-full border-2 border-black font-mono font-bold uppercase"></td>
                                        </tr>
                                        @foreach(['title','track_number','duration'] as $field)<input type="hidden" name="releases[{{ $releaseIndex }}][tracks][{{ $trackIndex }}][{{ $field }}]" value="{{ $track[$field] }}">@endforeach
                                        <input type="hidden" name="releases[{{ $releaseIndex }}][tracks][{{ $trackIndex }}][explicit]" value="{{ $track['explicit'] ? 1 : 0 }}">
                                        @foreach($track['artist_names'] as $artistName)<input type="hidden" name="releases[{{ $releaseIndex }}][tracks][{{ $trackIndex }}][artist_names][]" value="{{ $artistName }}">@endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endforeach

                    <div class="flex justify-end">
                        <button class="px-7 py-4 bg-green-300 border-2 border-black font-black uppercase shadow-[5px_5px_0_#000]">Create & Mark Distributed</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
