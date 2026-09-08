<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Upload Song') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('artist.songs.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label for="title" value="Song Title *" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" required placeholder="Enter song title" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="album_id" value="Album *" />
                            <select id="album_id" name="album_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Album</option>
                                @foreach($albums as $album)
                                <option value="{{ $album->id }}" {{ $selectedAlbum && $selectedAlbum->id == $album->id ? 'selected' : '' }}>{{ $album->title }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('album_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="track_number" value="Track Number" />
                            <x-text-input id="track_number" class="block mt-1 w-full" type="number" min="1" name="track_number" />
                        </div>
                        <div>
                            <x-input-label for="genre" value="Genre" />
                            <x-text-input id="genre" class="block mt-1 w-full" type="text" name="genre" placeholder="Pop, Rock..." />
                        </div>
                        <div>
                            <x-input-label for="language" value="Language" />
                            <x-text-input id="language" class="block mt-1 w-full" type="text" name="language" placeholder="English, Spanish..." />
                        </div>
                        <div>
                            <x-input-label for="duration" value="Duration (mm:ss)" />
                            <x-text-input id="duration" class="block mt-1 w-full" type="text" name="duration" placeholder="3:30" />
                        </div>
                        <div>
                            <x-input-label for="isrc_code" value="ISRC Code" />
                            <x-text-input id="isrc_code" class="block mt-1 w-full" type="text" name="isrc_code" placeholder="US-S1Z-99-00001" />
                        </div>
                        <div>
                            <x-input-label for="composers" value="Composers" />
                            <x-text-input id="composers" class="block mt-1 w-full" type="text" name="composers" placeholder="Composer names" />
                        </div>
                        <div>
                            <x-input-label for="producers" value="Producers" />
                            <x-text-input id="producers" class="block mt-1 w-full" type="text" name="producers" placeholder="Producer names" />
                        </div>
                        <div>
                            <x-input-label for="featuring" value="Featuring" />
                            <x-text-input id="featuring" class="block mt-1 w-full" type="text" name="featuring" placeholder="Featured artists" />
                        </div>
                        <div>
                            <label class="inline-flex items-center mt-6">
                                <input type="checkbox" name="explicit" value="1" class="w-4 h-4 border-2 border-black rounded-none text-brand-500 focus:ring-0">
                                <span class="ml-2 font-extrabold text-sm text-black">Explicit Content</span>
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="audio_file" value="Audio File (MP3, WAV, FLAC)" />
                            <input id="audio_file" class="block mt-1 w-full text-sm font-semibold text-black file:mr-4 file:py-2 file:px-4 file:border-2 file:border-black file:rounded-none file:text-xs file:font-extrabold file:bg-brand-500 file:text-black hover:file:bg-brand-400 file:uppercase file:tracking-wider file:cursor-pointer" type="file" name="audio_file" accept="audio/*" />
                            <p class="text-xs font-semibold text-black/50 mt-1">DistroKid format: WAV or FLAC, 16-bit, 44.1kHz recommended</p>
                            <x-input-error :messages="$errors->get('audio_file')" class="mt-2" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="lyrics" value="Lyrics" />
                            <textarea id="lyrics" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="lyrics" rows="3" placeholder="Song lyrics..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4 gap-3">
                        <a href="{{ route('artist.songs') }}" class="inline-flex items-center px-4 py-2 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest hover:bg-black/5 transition-all rounded-none">Cancel</a>
                        <x-primary-button>{{ __('Upload Song') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
