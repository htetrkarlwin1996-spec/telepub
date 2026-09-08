<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Edit Song') }}: {{ $song->title }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('artist.songs.update', $song) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label for="title" value="Song Title" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" value="{{ $song->title }}" required />
                        </div>
                        <div>
                            <x-input-label for="album_id" value="Album" />
                            <select id="album_id" name="album_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @foreach($albums as $album)
                                <option value="{{ $album->id }}" {{ $song->album_id == $album->id ? 'selected' : '' }}>{{ $album->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="track_number" value="Track Number" />
                            <x-text-input id="track_number" class="block mt-1 w-full" type="number" name="track_number" value="{{ $song->track_number }}" />
                        </div>
                        <div>
                            <x-input-label for="duration" value="Duration" />
                            <x-text-input id="duration" class="block mt-1 w-full" type="text" name="duration" value="{{ $song->duration }}" />
                        </div>
                        <div>
                            <x-input-label for="isrc_code" value="ISRC Code" />
                            <x-text-input id="isrc_code" class="block mt-1 w-full" type="text" name="isrc_code" value="{{ $song->isrc_code }}" />
                        </div>
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                <option value="draft" {{ $song->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ $song->status == 'submitted' ? 'selected' : '' }}>Submit for Review</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="composers" value="Composers" />
                            <x-text-input id="composers" class="block mt-1 w-full" type="text" name="composers" value="{{ $song->composers }}" />
                        </div>
                        <div>
                            <x-input-label for="producers" value="Producers" />
                            <x-text-input id="producers" class="block mt-1 w-full" type="text" name="producers" value="{{ $song->producers }}" />
                        </div>
                        <div>
                            <x-input-label for="featuring" value="Featuring" />
                            <x-text-input id="featuring" class="block mt-1 w-full" type="text" name="featuring" value="{{ $song->featuring }}" />
                        </div>
                        <div>
                            <label class="inline-flex items-center mt-6">
                                <input type="checkbox" name="explicit" value="1" {{ $song->explicit ? 'checked' : '' }} class="w-4 h-4 border-2 border-black rounded-none text-brand-500 focus:ring-0">
                                <span class="ml-2 font-extrabold text-sm text-black">Explicit Content</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-primary-button>{{ __('Update Song') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
