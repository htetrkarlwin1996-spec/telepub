<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Create Album') }}</h2>
            <a href="{{ route('admin.albums') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                Back to Albums
            </a>
        </div>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <form method="POST" action="{{ route('admin.albums.store') }}" class="space-y-6">
                @csrf

                <!-- Artist Selection -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <h3 class="text-lg font-extrabold text-black mb-4">Artist</h3>
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Select Artist</label>
                        <select name="artist_id" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('artist_id') border-red-500 @enderror" required>
                            <option value="">— Select Artist —</option>
                            @foreach($artists as $artist)
                                <option value="{{ $artist->id }}" {{ old('artist_id') == $artist->id ? 'selected' : '' }}>
                                    {{ $artist->artist_name }} ({{ $artist->user->email ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('artist_id') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Album Details -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <h3 class="text-lg font-extrabold text-black mb-4">Album Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('title') border-red-500 @enderror" required>
                            @error('title') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Release Type</label>
                            <select name="release_type" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('release_type') border-red-500 @enderror" required>
                                <option value="single" {{ old('release_type') == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="ep" {{ old('release_type') == 'ep' ? 'selected' : '' }}>EP</option>
                                <option value="album" {{ old('release_type') == 'album' ? 'selected' : '' }}>Album</option>
                            </select>
                            @error('release_type') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Genre</label>
                            <select name="genre" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('genre') border-red-500 @enderror" required>
                                <option value="">— Select Genre —</option>
                                @foreach($genres as $genre)
                                    <option value="{{ $genre }}" {{ old('genre') == $genre ? 'selected' : '' }}>{{ $genre }}</option>
                                @endforeach
                            </select>
                            @error('genre') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Label</label>
                            <input type="text" name="label" value="{{ old('label') }}" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('label') border-red-500 @enderror">
                            @error('label') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Release Date</label>
                            <input type="date" name="release_date" value="{{ old('release_date') }}" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('release_date') border-red-500 @enderror" required>
                            @error('release_date') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">UPC Code</label>
                            <input type="text" name="upc_code" value="{{ old('upc_code') }}" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('upc_code') border-red-500 @enderror">
                            @error('upc_code') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Copyright Holder</label>
                            <input type="text" name="copyright_holder" value="{{ old('copyright_holder') }}" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('copyright_holder') border-red-500 @enderror">
                            @error('copyright_holder') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-black/60 mb-1">Phonogram Right Holder</label>
                            <input type="text" name="phonogram_right_holder" value="{{ old('phonogram_right_holder') }}" class="w-full border-2 border-black px-3 py-2 text-sm font-bold focus:outline-none focus:ring-0 focus:border-brand-500 @error('phonogram_right_holder') border-red-500 @enderror">
                            @error('phonogram_right_holder') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.albums') }}" class="px-6 py-3 bg-white border-2 border-black font-extrabold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        Create Album
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
