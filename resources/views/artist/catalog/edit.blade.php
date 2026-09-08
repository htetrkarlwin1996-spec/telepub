<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-black text-black">Edit Release</h1>
                <p class="text-sm font-bold text-black/60 mt-1">{{ $album->title }}</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('artist.catalog.update', $album) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')

                <!-- Collaborating Artists (Revenue Share) -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Collaborating Artists (Optional)</label>
                    <p class="text-xs font-bold text-black/50 mb-3">Add other artists who will share revenue on this release. Set each collaborator's share percentage of the net artist revenue.</p>
                    
                    <div id="collaborators-container">
                        @if($album->relationLoaded('collaboratingArtists'))
                            @foreach($album->collaboratingArtists as $index => $collab)
                                <div class="collaborator-row flex items-center gap-3 mb-3 p-3 bg-gray-50 border-2 border-black">
                                    <div class="flex-1">
                                        <select name="collaborating_artists[]" class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                                            <option value="">Select artist...</option>
                                            @foreach($artists as $artist)
                                                <option value="{{ $artist->id }}" {{ $collab->id == $artist->id ? 'selected' : '' }}>
                                                    {{ $artist->artist_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-32">
                                        <div class="flex items-center gap-1">
                                            <input type="number" name="collaborating_shares[]" value="{{ $collab->pivot->share_percentage }}"
                                                min="0" max="100" step="0.01"
                                                class="w-20 px-3 py-2 border-2 border-black font-bold text-sm text-center focus:outline-none focus:ring-0 focus:border-brand-500"
                                                placeholder="%">
                                            <span class="font-extrabold text-xs">%</span>
                                        </div>
                                    </div>
                                    <button type="button" onclick="this.closest('.collaborator-row').remove()"
                                        class="px-3 py-2 bg-red-100 border-2 border-black font-extrabold text-xs hover:bg-red-200 transition-all">
                                        ✕
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <button type="button" onclick="addCollaborator()"
                        class="mt-2 px-4 py-2 bg-gray-100 border-2 border-black font-extrabold text-xs uppercase hover:bg-gray-200 transition-all">
                        + Add Collaborating Artist
                    </button>
                </div>

                <script>
                    function addCollaborator() {
                        const container = document.getElementById('collaborators-container');
                        const artists = @json($artists->map(fn($a) => ['id' => $a->id, 'name' => $a->artist_name]));
                        
                        const row = document.createElement('div');
                        row.className = 'collaborator-row flex items-center gap-3 mb-3 p-3 bg-gray-50 border-2 border-black';
                        
                        let options = '<option value="">Select artist...</option>';
                        artists.forEach(a => {
                            options += `<option value="${a.id}">${a.name}</option>`;
                        });
                        
                        row.innerHTML = `
                            <div class="flex-1">
                                <select name="collaborating_artists[]" class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                                    ${options}
                                </select>
                            </div>
                            <div class="w-32">
                                <div class="flex items-center gap-1">
                                    <input type="number" name="collaborating_shares[]" value=""
                                        min="0" max="100" step="0.01"
                                        class="w-20 px-3 py-2 border-2 border-black font-bold text-sm text-center focus:outline-none focus:ring-0 focus:border-brand-500"
                                        placeholder="%">
                                    <span class="font-extrabold text-xs">%</span>
                                </div>
                            </div>
                            <button type="button" onclick="this.closest('.collaborator-row').remove()"
                                class="px-3 py-2 bg-red-100 border-2 border-black font-extrabold text-xs hover:bg-red-200 transition-all">
                                ✕
                            </button>
                        `;
                        container.appendChild(row);
                    }
                </script>

                <!-- Album Name -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Release Name <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $album->title) }}" required
                        class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                </div>

                <!-- Release Type & Genre -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Release Type <span class="text-red-500">*</span></label>
                        <select name="release_type" required class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                            <option value="single" {{ $album->release_type === 'single' ? 'selected' : '' }}>Single</option>
                            <option value="ep" {{ $album->release_type === 'ep' ? 'selected' : '' }}>EP</option>
                            <option value="album" {{ $album->release_type === 'album' ? 'selected' : '' }}>Album</option>
                        </select>
                    </div>
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Genre <span class="text-red-500">*</span></label>
                        <select name="genre" required class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                            <option value="">Select genre...</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre }}" {{ $album->genre === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Release Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Digital Release Date <span class="text-red-500">*</span></label>
                        <input type="date" name="release_date" value="{{ old('release_date', $album->release_date?->format('Y-m-d')) }}" required
                            class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                    </div>
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Physical Release Date</label>
                        <input type="date" name="physical_release_date" value="{{ old('physical_release_date', $album->physical_release_date?->format('Y-m-d')) }}"
                            class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                    </div>
                </div>

                <!-- Price -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Price (USD) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-extrabold text-lg">$</span>
                        <input type="number" name="price" value="{{ old('price', $album->price) }}" required
                            step="0.01" min="0" max="999.99"
                            class="w-full pl-10 pr-4 py-3 border-2 border-black font-bold text-lg focus:outline-none focus:ring-0 focus:border-brand-500">
                    </div>
                </div>

                <!-- Cover Art -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Album Artwork</label>
                    <p class="text-xs font-bold text-black/50 mb-3">3000 × 3000 pixels · JPG or PNG only · Leave empty to keep current.</p>
                    @if($album->cover_art)
                        <div class="mb-3 w-32 h-32 border-2 border-black overflow-hidden">
                            <img src="{{ Storage::url($album->cover_art) }}" alt="Current cover" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <div class="border-2 border-dashed border-black p-4 text-center">
                        <input type="file" name="cover_art" accept=".jpg,.jpeg,.png"
                            class="w-full text-sm"
                            onchange="this.parentElement.querySelector('.file-label').textContent = this.files[0]?.name || 'No file selected'">
                        <p class="file-label text-xs font-bold text-black/40 mt-1">No file selected</p>
                    </div>
                </div>

                <!-- Copyright & Phonogram -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Copyright Holder <span class="text-red-500">*</span></label>
                        <input type="text" name="copyright_holder" value="{{ old('copyright_holder', $album->copyright_holder) }}" required
                            class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                    </div>
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Phonogram Right Holder <span class="text-red-500">*</span></label>
                        <input type="text" name="phonogram_right_holder" value="{{ old('phonogram_right_holder', $album->phonogram_right_holder) }}" required
                            class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('artist.catalog.show', $album) }}" class="px-6 py-3 bg-gray-200 border-2 border-black font-extrabold text-sm uppercase hover:bg-gray-300 transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        Update Release
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
