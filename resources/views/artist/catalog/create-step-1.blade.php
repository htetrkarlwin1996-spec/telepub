<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Step Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-500 border-2 border-black flex items-center justify-center font-extrabold text-sm">1</div>
                        <span class="text-xs font-extrabold uppercase">Release Info</span>
                    </div>
                    <div class="w-12 h-0.5 bg-black/30"></div>
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-gray-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">2</div>
                        <span class="text-xs font-extrabold uppercase">Tracks</span>
                    </div>
                    <div class="w-12 h-0.5 bg-black/30"></div>
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-gray-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">3</div>
                        <span class="text-xs font-extrabold uppercase">Pricing</span>
                    </div>
                    <div class="w-12 h-0.5 bg-black/30"></div>
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-gray-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">4</div>
                        <span class="text-xs font-extrabold uppercase">Stores</span>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-black text-center">Step 1: Release Information</h1>
                <p class="text-sm font-bold text-black/60 text-center mt-1">Tell us about your release</p>
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

            <form method="POST" action="{{ route('artist.catalog.store-step1') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Collaborating Artists (Revenue Share) -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Collaborating Artists (Optional)</label>
                    <p class="text-xs font-bold text-black/50 mb-3">Add other artists who will share revenue on this release. Set each collaborator's share percentage of the net artist revenue.</p>
                    
                    <div id="collaborators-container">
                        @if(isset($album) && $album->relationLoaded('collaboratingArtists'))
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
                    <label class="block font-extrabold text-sm uppercase mb-2">Album / Release Name <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                        placeholder="e.g. Midnight Dreams">
                </div>

                <!-- Release Type & Genre -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Release Type <span class="text-red-500">*</span></label>
                        <select name="release_type" required class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                            <option value="single" {{ old('release_type') === 'single' ? 'selected' : '' }}>Single</option>
                            <option value="ep" {{ old('release_type') === 'ep' ? 'selected' : '' }}>EP</option>
                            <option value="album" {{ old('release_type') === 'album' ? 'selected' : '' }}>Album</option>
                        </select>
                    </div>

                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Genre <span class="text-red-500">*</span></label>
                        <select name="genre" required class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                            <option value="">Select genre...</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre }}" {{ old('genre') === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Release Date -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Release Date <span class="text-red-500">*</span></label>
                    <input type="date" name="release_date" value="{{ old('release_date') }}" required
                        class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                </div>

                <!-- Album Art Upload -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Album Artwork <span class="text-red-500">*</span></label>
                    <p class="text-xs font-bold text-black/50 mb-3">3000 × 3000 pixels · JPG or PNG only · Max 10MB</p>
                    <div class="border-2 border-dashed border-black p-8 text-center" id="upload-area">
                        <svg class="w-12 h-12 mx-auto text-black/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-bold text-black/60 mb-2">Click or drag to upload</p>
                        <p class="text-xs font-bold text-black/40" id="file-name">No file selected</p>
                        <input type="file" name="cover_art" id="cover_art" accept=".jpg,.jpeg,.png" required
                            class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'No file selected'">
                        <button type="button" onclick="document.getElementById('cover_art').click()"
                            class="mt-4 px-6 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs uppercase hover:bg-brand-300 transition-all">
                            Choose File
                        </button>
                    </div>
                    @error('cover_art')
                        <p class="text-red-600 text-xs font-bold mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Copyright & Phonogram -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Copyright Holder <span class="text-red-500">*</span></label>
                        <input type="text" name="copyright_holder" value="{{ old('copyright_holder') }}" required
                            class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="e.g. Star Records">
                    </div>

                    <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                        <label class="block font-extrabold text-sm uppercase mb-2">Phonogram Right Holder <span class="text-red-500">*</span></label>
                        <input type="text" name="phonogram_right_holder" value="{{ old('phonogram_right_holder') }}" required
                            class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="e.g. Star Records">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('artist.catalog.index') }}" class="px-6 py-3 bg-gray-200 border-2 border-black font-extrabold text-sm uppercase hover:bg-gray-300 transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        Next: Tracks →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
