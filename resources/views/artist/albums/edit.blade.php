<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Edit Album') }}: {{ $album->title }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('artist.albums.update', $album) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label for="title" value="Album Title" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" value="{{ $album->title }}" required />
                        </div>
                        <div>
                            <x-input-label for="genre" value="Genre" />
                            <x-text-input id="genre" class="block mt-1 w-full" type="text" name="genre" value="{{ $album->genre }}" />
                        </div>
                        <div>
                            <x-input-label for="label" value="Label" />
                            <x-text-input id="label" class="block mt-1 w-full" type="text" name="label" value="{{ $album->label }}" />
                        </div>
                        <div>
                            <x-input-label for="release_date" value="Release Date" />
                            <x-text-input id="release_date" class="block mt-1 w-full" type="date" name="release_date" value="{{ $album->release_date ? $album->release_date->format('Y-m-d') : '' }}" />
                        </div>
                        <div>
                            <x-input-label for="upc_code" value="UPC Code" />
                            <x-text-input id="upc_code" class="block mt-1 w-full" type="text" name="upc_code" value="{{ $album->upc_code }}" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="cover_art" value="Cover Art" />
                            <input id="cover_art" class="block mt-1 w-full text-sm font-semibold text-black file:mr-4 file:py-2 file:px-4 file:border-2 file:border-black file:rounded-none file:text-xs file:font-extrabold file:bg-brand-500 file:text-black hover:file:bg-brand-400 file:uppercase file:tracking-wider file:cursor-pointer" type="file" name="cover_art" accept="image/*" />
                            @if($album->cover_art)
                            <p class="text-xs font-semibold text-black/50 mt-1">Current: {{ $album->cover_art }}</p>
                            @endif
                        </div>
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                <option value="draft" {{ $album->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ $album->status == 'submitted' ? 'selected' : '' }}>Submit for Review</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="notes" value="Notes" />
                            <textarea id="notes" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="notes" rows="2">{{ $album->notes }}</textarea>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-primary-button>{{ __('Update Album') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
