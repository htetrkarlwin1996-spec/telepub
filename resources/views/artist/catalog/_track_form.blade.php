<div class="flex items-center justify-between mb-4">
    <h3 class="track-heading font-extrabold text-lg text-black">Track {{ $index + 1 }}</h3>
    @if($index > 0)
        <button type="button" onclick="this.closest('.track-entry').remove()" class="text-xs font-extrabold uppercase text-red-600 hover:text-red-800">
            Remove
        </button>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Track Name -->
    <div>
        <label class="block font-extrabold text-xs uppercase mb-1">Track Name <span class="text-red-500">*</span></label>
        <input type="text" name="tracks[{{ $index }}][title]" value="{{ $song->title ?? old('tracks.' . $index . '.title') }}" required
            class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
            placeholder="Track name">
    </div>

    <!-- Version -->
    <div>
        <label class="block font-extrabold text-xs uppercase mb-1">Version</label>
        <input type="text" name="tracks[{{ $index }}][version]" value="{{ $song->version ?? old('tracks.' . $index . '.version') }}"
            class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
            placeholder="e.g. Album Version, Radio Edit, Acoustic">
    </div>
</div>

<!-- ===== CREDIT SECTIONS ===== -->
@php
    // Helper to extract person entries from song data (handle both old string[] and new object[] format)
    $getCreditEntries = function($field) use ($song) {
        $data = $song->$field ?? null;
        if (empty($data)) return [];
        // If it's an array of strings (old format), convert to objects
        if (is_array($data) && isset($data[0]) && is_string($data[0])) {
            return array_map(fn($name) => ['name' => $name, 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], $data);
        }
        // If it's already an array of objects
        if (is_array($data)) return $data;
        return [];
    };

    $primaryArtists = $getCreditEntries('primary_artists');
    $featuring = $getCreditEntries('featuring');
    $composers = $getCreditEntries('composers');
    $lyricist = $getCreditEntries('lyricist');
    $producers = $getCreditEntries('producers');
    $vocals = $getCreditEntries('vocals');
@endphp

<div class="mt-4 space-y-4">
    <!-- Primary Artists -->
    <div class="border-2 border-black p-4 credit-section" data-field="primary_artists" data-track="{{ $index }}">
        <label class="block font-extrabold text-xs uppercase mb-2">Primary Artists <span class="text-red-500">*</span></label>
        <div class="person-list space-y-2">
            @if(count($primaryArtists) > 0)
                @foreach($primaryArtists as $paIdx => $pa)
                    <div class="person-entry flex items-center gap-2" data-person-idx="{{ $paIdx }}">
                        <input type="text"
                            name="tracks[{{ $index }}][primary_artists][{{ $paIdx }}][name]"
                            value="{{ $pa['name'] ?? '' }}"
                            required
                            class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="Artist name">
                        <input type="hidden" name="tracks[{{ $index }}][primary_artists][{{ $paIdx }}][spotify_url]" value="{{ $pa['spotify_url'] ?? '' }}" class="link-spotify">
                        <input type="hidden" name="tracks[{{ $index }}][primary_artists][{{ $paIdx }}][apple_music_url]" value="{{ $pa['apple_music_url'] ?? '' }}" class="link-apple_music">
                        <input type="hidden" name="tracks[{{ $index }}][primary_artists][{{ $paIdx }}][youtube_url]" value="{{ $pa['youtube_url'] ?? '' }}" class="link-youtube">
                        <input type="hidden" name="tracks[{{ $index }}][primary_artists][{{ $paIdx }}][tidal_url]" value="{{ $pa['tidal_url'] ?? '' }}" class="link-tidal">
                        <button type="button" onclick="openLinksModal(this)"
                            class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                            Links
                        </button>
                        <button type="button" onclick="this.closest('.person-entry').remove()"
                            class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                            ✕
                        </button>
                    </div>
                @endforeach
            @else
                <!-- Default: pre-fill with logged-in artist from profile -->
                <div class="person-entry flex items-center gap-2" data-person-idx="0">
                    <input type="text"
                        name="tracks[{{ $index }}][primary_artists][0][name]"
                        value="{{ $artistName ?? auth()->user()->artist->artist_name ?? '' }}"
                        required
                        class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                        placeholder="Artist name">
                    <input type="hidden" name="tracks[{{ $index }}][primary_artists][0][spotify_url]" value="{{ auth()->user()->artist->spotify_profile_url ?? '' }}" class="link-spotify">
                    <input type="hidden" name="tracks[{{ $index }}][primary_artists][0][apple_music_url]" value="{{ auth()->user()->artist->apple_music_profile_url ?? '' }}" class="link-apple_music">
                    <input type="hidden" name="tracks[{{ $index }}][primary_artists][0][youtube_url]" value="{{ auth()->user()->artist->youtube_profile_url ?? '' }}" class="link-youtube">
                    <input type="hidden" name="tracks[{{ $index }}][primary_artists][0][tidal_url]" value="{{ auth()->user()->artist->tidal_profile_url ?? '' }}" class="link-tidal">
                    <button type="button" onclick="openLinksModal(this)"
                        class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                        Links
                    </button>
                    <button type="button" onclick="this.closest('.person-entry').remove()"
                        class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                        ✕
                    </button>
                </div>
            @endif
        </div>
        <button type="button" onclick="addPersonEntry(this, 'primary_artists', {{ $index }})"
            class="mt-2 px-4 py-1.5 bg-gray-50 border-2 border-dashed border-black font-extrabold text-[10px] uppercase hover:bg-brand-50 transition-all">
            + Add Primary Artist
        </button>
    </div>

    <!-- Featuring -->
    <div class="border-2 border-black p-4 credit-section" data-field="featuring" data-track="{{ $index }}">
        <label class="block font-extrabold text-xs uppercase mb-2">Featuring</label>
        <div class="person-list space-y-2">
            @if(count($featuring) > 0)
                @foreach($featuring as $fIdx => $f)
                    <div class="person-entry flex items-center gap-2" data-person-idx="{{ $fIdx }}">
                        <input type="text"
                            name="tracks[{{ $index }}][featuring][{{ $fIdx }}][name]"
                            value="{{ $f['name'] ?? '' }}"
                            class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="Artist name">
                        <input type="hidden" name="tracks[{{ $index }}][featuring][{{ $fIdx }}][spotify_url]" value="{{ $f['spotify_url'] ?? '' }}" class="link-spotify">
                        <input type="hidden" name="tracks[{{ $index }}][featuring][{{ $fIdx }}][apple_music_url]" value="{{ $f['apple_music_url'] ?? '' }}" class="link-apple_music">
                        <input type="hidden" name="tracks[{{ $index }}][featuring][{{ $fIdx }}][youtube_url]" value="{{ $f['youtube_url'] ?? '' }}" class="link-youtube">
                        <input type="hidden" name="tracks[{{ $index }}][featuring][{{ $fIdx }}][tidal_url]" value="{{ $f['tidal_url'] ?? '' }}" class="link-tidal">
                        <button type="button" onclick="openLinksModal(this)"
                            class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                            Links
                        </button>
                        <button type="button" onclick="this.closest('.person-entry').remove()"
                            class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                            ✕
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" onclick="addPersonEntry(this, 'featuring', {{ $index }})"
            class="mt-2 px-4 py-1.5 bg-gray-50 border-2 border-dashed border-black font-extrabold text-[10px] uppercase hover:bg-brand-50 transition-all">
            + Add Featuring Artist
        </button>
    </div>

    <!-- Composer(s) -->
    <div class="border-2 border-black p-4 credit-section" data-field="composers" data-track="{{ $index }}">
        <label class="block font-extrabold text-xs uppercase mb-2">Composer(s) <span class="text-red-500">*</span></label>
        <div class="person-list space-y-2">
            @if(count($composers) > 0)
                @foreach($composers as $cIdx => $c)
                    <div class="person-entry flex items-center gap-2" data-person-idx="{{ $cIdx }}">
                        <input type="text"
                            name="tracks[{{ $index }}][composers][{{ $cIdx }}][name]"
                            value="{{ $c['name'] ?? '' }}"
                            required
                            class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="Composer name">
                        <input type="hidden" name="tracks[{{ $index }}][composers][{{ $cIdx }}][spotify_url]" value="{{ $c['spotify_url'] ?? '' }}" class="link-spotify">
                        <input type="hidden" name="tracks[{{ $index }}][composers][{{ $cIdx }}][apple_music_url]" value="{{ $c['apple_music_url'] ?? '' }}" class="link-apple_music">
                        <input type="hidden" name="tracks[{{ $index }}][composers][{{ $cIdx }}][youtube_url]" value="{{ $c['youtube_url'] ?? '' }}" class="link-youtube">
                        <input type="hidden" name="tracks[{{ $index }}][composers][{{ $cIdx }}][tidal_url]" value="{{ $c['tidal_url'] ?? '' }}" class="link-tidal">
                        <button type="button" onclick="openLinksModal(this)"
                            class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                            Links
                        </button>
                        <button type="button" onclick="this.closest('.person-entry').remove()"
                            class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                            ✕
                        </button>
                    </div>
                @endforeach
            @else
                <div class="person-entry flex items-center gap-2" data-person-idx="0">
                    <input type="text"
                        name="tracks[{{ $index }}][composers][0][name]"
                        value=""
                        required
                        class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                        placeholder="Composer name">
                    <input type="hidden" name="tracks[{{ $index }}][composers][0][spotify_url]" value="" class="link-spotify">
                    <input type="hidden" name="tracks[{{ $index }}][composers][0][apple_music_url]" value="" class="link-apple_music">
                    <input type="hidden" name="tracks[{{ $index }}][composers][0][youtube_url]" value="" class="link-youtube">
                    <input type="hidden" name="tracks[{{ $index }}][composers][0][tidal_url]" value="" class="link-tidal">
                    <button type="button" onclick="openLinksModal(this)"
                        class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                        Links
                    </button>
                    <button type="button" onclick="this.closest('.person-entry').remove()"
                        class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                        ✕
                    </button>
                </div>
            @endif
        </div>
        <button type="button" onclick="addPersonEntry(this, 'composers', {{ $index }})"
            class="mt-2 px-4 py-1.5 bg-gray-50 border-2 border-dashed border-black font-extrabold text-[10px] uppercase hover:bg-brand-50 transition-all">
            + Add Composer
        </button>
    </div>

    <!-- Lyricist -->
    <div class="border-2 border-black p-4 credit-section" data-field="lyricist" data-track="{{ $index }}">
        <label class="block font-extrabold text-xs uppercase mb-2">Lyricist</label>
        <div class="person-list space-y-2">
            @if(count($lyricist) > 0)
                @foreach($lyricist as $lIdx => $l)
                    <div class="person-entry flex items-center gap-2" data-person-idx="{{ $lIdx }}">
                        <input type="text"
                            name="tracks[{{ $index }}][lyricist][{{ $lIdx }}][name]"
                            value="{{ $l['name'] ?? '' }}"
                            class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="Lyricist name">
                        <input type="hidden" name="tracks[{{ $index }}][lyricist][{{ $lIdx }}][spotify_url]" value="{{ $l['spotify_url'] ?? '' }}" class="link-spotify">
                        <input type="hidden" name="tracks[{{ $index }}][lyricist][{{ $lIdx }}][apple_music_url]" value="{{ $l['apple_music_url'] ?? '' }}" class="link-apple_music">
                        <input type="hidden" name="tracks[{{ $index }}][lyricist][{{ $lIdx }}][youtube_url]" value="{{ $l['youtube_url'] ?? '' }}" class="link-youtube">
                        <input type="hidden" name="tracks[{{ $index }}][lyricist][{{ $lIdx }}][tidal_url]" value="{{ $l['tidal_url'] ?? '' }}" class="link-tidal">
                        <button type="button" onclick="openLinksModal(this)"
                            class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                            Links
                        </button>
                        <button type="button" onclick="this.closest('.person-entry').remove()"
                            class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                            ✕
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" onclick="addPersonEntry(this, 'lyricist', {{ $index }})"
            class="mt-2 px-4 py-1.5 bg-gray-50 border-2 border-dashed border-black font-extrabold text-[10px] uppercase hover:bg-brand-50 transition-all">
            + Add Lyricist
        </button>
    </div>

    <!-- Producer(s) -->
    <div class="border-2 border-black p-4 credit-section" data-field="producers" data-track="{{ $index }}">
        <label class="block font-extrabold text-xs uppercase mb-2">Producer(s)</label>
        <div class="person-list space-y-2">
            @if(count($producers) > 0)
                @foreach($producers as $pIdx => $p)
                    <div class="person-entry flex items-center gap-2" data-person-idx="{{ $pIdx }}">
                        <input type="text"
                            name="tracks[{{ $index }}][producers][{{ $pIdx }}][name]"
                            value="{{ $p['name'] ?? '' }}"
                            class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="Producer name">
                        <input type="hidden" name="tracks[{{ $index }}][producers][{{ $pIdx }}][spotify_url]" value="{{ $p['spotify_url'] ?? '' }}" class="link-spotify">
                        <input type="hidden" name="tracks[{{ $index }}][producers][{{ $pIdx }}][apple_music_url]" value="{{ $p['apple_music_url'] ?? '' }}" class="link-apple_music">
                        <input type="hidden" name="tracks[{{ $index }}][producers][{{ $pIdx }}][youtube_url]" value="{{ $p['youtube_url'] ?? '' }}" class="link-youtube">
                        <input type="hidden" name="tracks[{{ $index }}][producers][{{ $pIdx }}][tidal_url]" value="{{ $p['tidal_url'] ?? '' }}" class="link-tidal">
                        <button type="button" onclick="openLinksModal(this)"
                            class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                            Links
                        </button>
                        <button type="button" onclick="this.closest('.person-entry').remove()"
                            class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                            ✕
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" onclick="addPersonEntry(this, 'producers', {{ $index }})"
            class="mt-2 px-4 py-1.5 bg-gray-50 border-2 border-dashed border-black font-extrabold text-[10px] uppercase hover:bg-brand-50 transition-all">
            + Add Producer
        </button>
    </div>

    <!-- Vocals -->
    <div class="border-2 border-black p-4 credit-section" data-field="vocals" data-track="{{ $index }}">
        <label class="block font-extrabold text-xs uppercase mb-2">Vocals</label>
        <div class="person-list space-y-2">
            @if(count($vocals) > 0)
                @foreach($vocals as $vIdx => $v)
                    <div class="person-entry flex items-center gap-2" data-person-idx="{{ $vIdx }}">
                        <input type="text"
                            name="tracks[{{ $index }}][vocals][{{ $vIdx }}][name]"
                            value="{{ $v['name'] ?? '' }}"
                            class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="Vocalist name">
                        <input type="hidden" name="tracks[{{ $index }}][vocals][{{ $vIdx }}][spotify_url]" value="{{ $v['spotify_url'] ?? '' }}" class="link-spotify">
                        <input type="hidden" name="tracks[{{ $index }}][vocals][{{ $vIdx }}][apple_music_url]" value="{{ $v['apple_music_url'] ?? '' }}" class="link-apple_music">
                        <input type="hidden" name="tracks[{{ $index }}][vocals][{{ $vIdx }}][youtube_url]" value="{{ $v['youtube_url'] ?? '' }}" class="link-youtube">
                        <input type="hidden" name="tracks[{{ $index }}][vocals][{{ $vIdx }}][tidal_url]" value="{{ $v['tidal_url'] ?? '' }}" class="link-tidal">
                        <button type="button" onclick="openLinksModal(this)"
                            class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                            Links
                        </button>
                        <button type="button" onclick="this.closest('.person-entry').remove()"
                            class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                            ✕
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" onclick="addPersonEntry(this, 'vocals', {{ $index }})"
            class="mt-2 px-4 py-1.5 bg-gray-50 border-2 border-dashed border-black font-extrabold text-[10px] uppercase hover:bg-brand-50 transition-all">
            + Add Vocalist
        </button>
    </div>
</div>

<!-- Language & Duration -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="block font-extrabold text-xs uppercase mb-1">Language</label>
        <input type="text" name="tracks[{{ $index }}][language]" value="{{ $song->language ?? old('tracks.' . $index . '.language', 'English') }}"
            class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
            placeholder="e.g. English">
    </div>

    <div>
        <label class="block font-extrabold text-xs uppercase mb-1">Duration (seconds)</label>
        <input type="number" name="tracks[{{ $index }}][duration]" value="{{ $song->duration ?? old('tracks.' . $index . '.duration') }}" min="0"
            class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
            placeholder="e.g. 240">
    </div>
</div>

<!-- Audio File Upload -->
<div class="mt-4">
    <label class="block font-extrabold text-xs uppercase mb-1">Audio File</label>
    <p class="text-[10px] font-bold text-black/50 mb-2">MP3, WAV, AAC, FLAC, OGG · Max 50MB</p>
    <div class="border-2 border-dashed border-black p-4 text-center audio-upload-zone" data-track="{{ $index }}">
        <!-- Hidden input to store the uploaded file path (from AJAX upload) -->
        <input type="hidden" name="tracks[{{ $index }}][audio_file_path]" value="{{ $song->audio_file ?? '' }}" class="audio-path-input">

        <!-- File input for selecting (no name — uploaded via AJAX) -->
        <input type="file" accept=".mp3,.wav,.aac,.flac,.ogg"
            class="w-full text-sm audio-file-input">

        <!-- Status text -->
        <p class="file-label text-xs font-bold mt-1">
            @if($song && $song->audio_file)
                <span class="text-green-600">✓ Uploaded: {{ basename($song->audio_file) }}</span>
            @else
                <span class="text-black/40">No file selected</span>
            @endif
        </p>

        <!-- Progress bar (hidden by default) -->
        <div class="audio-progress mt-2 hidden">
            <div class="w-full bg-gray-200 border-2 border-black h-5">
                <div class="audio-progress-bar bg-brand-500 h-full text-[9px] font-extrabold text-white flex items-center justify-center transition-all duration-200" style="width: 0%">0%</div>
            </div>
            <p class="audio-status text-[10px] font-bold mt-1 text-black/60">Uploading...</p>
        </div>
    </div>
</div>

<!-- ISRC & Checkboxes -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <!-- ISRC Code -->
    <div>
        <label class="block font-extrabold text-xs uppercase mb-1">ISRC Code</label>
        <input type="text" name="tracks[{{ $index }}][isrc_code]" value="{{ $song->isrc_code ?? old('tracks.' . $index . '.isrc_code') }}"
            class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
            placeholder="e.g. USABC0100001">
    </div>

    <!-- Request New ISRC -->
    <div class="flex items-center">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="tracks[{{ $index }}][request_new_isrc]" value="1"
                {{ ($song && $song->request_new_isrc) ? 'checked' : '' }}
                class="w-5 h-5 border-2 border-black rounded-none focus:ring-0 focus:ring-offset-0">
            <span class="font-extrabold text-xs uppercase">Request New ISRC</span>
        </label>
    </div>

    <!-- Explicit -->
    <div class="flex items-center">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="tracks[{{ $index }}][explicit]" value="1"
                {{ ($song && $song->explicit) ? 'checked' : '' }}
                class="w-5 h-5 border-2 border-black rounded-none focus:ring-0 focus:ring-offset-0">
            <span class="font-extrabold text-xs uppercase">Explicit Content</span>
        </label>
    </div>
</div>

<!-- Lyrics -->
<div class="mt-4">
    <label class="block font-extrabold text-xs uppercase mb-1">Lyrics</label>
    <textarea name="tracks[{{ $index }}][lyrics]" rows="3"
        class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
        placeholder="Optional: paste lyrics here">{{ $song->lyrics ?? old('tracks.' . $index . '.lyrics') }}</textarea>
</div>
