<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Step Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-green-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">✓</div>
                        <span class="text-xs font-extrabold uppercase text-green-700">Release Info</span>
                    </div>
                    <div class="w-12 h-0.5 bg-green-400"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-500 border-2 border-black flex items-center justify-center font-extrabold text-sm">2</div>
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
                <h1 class="text-2xl font-black text-black text-center">Step 2: Add Tracks</h1>
                <p class="text-sm font-bold text-black/60 text-center mt-1">Release: <span class="text-brand-600">{{ $album->title }}</span> · Artist: <span class="text-brand-600">{{ $artist->artist_name }}</span></p>
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

            <form method="POST" action="{{ route('admin.releases.store-step2', $album) }}" enctype="multipart/form-data">
                @csrf
                <div id="tracks-container" class="space-y-6">
                    @if($album->songs->count() > 0)
                        @foreach($album->songs as $i => $song)
                            <div class="track-entry bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6" data-index="{{ $i }}">
                                @include('admin.releases._track_form', ['index' => $i, 'song' => $song, 'artist' => $artist])
                            </div>
                        @endforeach
                    @else
                        <div class="track-entry bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6" data-index="0">
                            @include('admin.releases._track_form', ['index' => 0, 'song' => null, 'artist' => $artist])
                        </div>
                    @endif
                </div>

                <!-- Add Track Button -->
                <button type="button" id="add-track"
                    class="mt-4 w-full px-6 py-4 bg-gray-100 border-2 border-dashed border-black font-extrabold text-sm uppercase hover:bg-brand-100 transition-all">
                    + Add Another Track
                </button>

                <!-- Actions -->
                <div class="flex items-center justify-between mt-8">
                    <a href="{{ route('admin.releases.edit-step1', $album) }}" class="px-6 py-3 bg-gray-200 border-2 border-black font-extrabold text-sm uppercase hover:bg-gray-300 transition-all">
                        ← Back to Release Info
                    </a>
                    <button type="submit" class="px-8 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        Next: Pricing →
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== LINKS MODAL ===== -->
    <div id="linksModalOverlay" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center" onclick="closeLinksModal(event)">
        <div class="bg-[#FFF8E7] border-2 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6 w-full max-w-md mx-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-lg text-black">Profile Links</h3>
                <button type="button" onclick="closeLinksModal()" class="text-2xl font-extrabold text-black/50 hover:text-black leading-none">&times;</button>
            </div>
            <p class="text-[10px] font-bold text-black/50 mb-4">Add platform profile links for this person.</p>
            <div class="space-y-3">
                <div>
                    <label class="block font-extrabold text-xs uppercase mb-1">Spotify URL</label>
                    <input type="url" id="modal-spotify" class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500" placeholder="https://open.spotify.com/artist/...">
                </div>
                <div>
                    <label class="block font-extrabold text-xs uppercase mb-1">Apple Music URL</label>
                    <input type="url" id="modal-apple_music" class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500" placeholder="https://music.apple.com/artist/...">
                </div>
                <div>
                    <label class="block font-extrabold text-xs uppercase mb-1">YouTube URL</label>
                    <input type="url" id="modal-youtube" class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500" placeholder="https://youtube.com/@...">
                </div>
                <div>
                    <label class="block font-extrabold text-xs uppercase mb-1">Tidal URL</label>
                    <input type="url" id="modal-tidal" class="w-full px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500" placeholder="https://tidal.com/artist/...">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" onclick="closeLinksModal()"
                    class="px-4 py-2 bg-gray-200 border-2 border-black font-extrabold text-xs uppercase hover:bg-gray-300 transition-all">
                    Cancel
                </button>
                <button type="button" onclick="saveLinksModal()"
                    class="px-6 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                    Save Links
                </button>
            </div>
        </div>
    </div>

    <script>
        let trackIndex = {{ max($album->songs->count(), 1) }};

        // ===== AUDIO FILE UPLOAD (AJAX with progress bar) =====
        const uploadAudioRoute = '{{ route('admin.releases.upload-audio') }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value;

        document.addEventListener('change', function(e) {
            const fileInput = e.target.closest('.audio-file-input');
            if (!fileInput) return;
            const file = fileInput.files[0];
            if (!file) return;

            const zone = fileInput.closest('.audio-upload-zone');
            const pathInput = zone.querySelector('.audio-path-input');
            const fileLabel = zone.querySelector('.file-label');
            const progressContainer = zone.querySelector('.audio-progress');
            const progressBar = zone.querySelector('.audio-progress-bar');
            const statusText = zone.querySelector('.audio-status');

            // Show progress bar, reset to 0%
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            fileLabel.innerHTML = `<span class="text-black/60">Uploading <strong>${file.name}</strong>...</span>`;

            const formData = new FormData();
            formData.append('audio_file', file);
            formData.append('_token', csrfToken);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadAudioRoute, true);

            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    const percent = Math.round((evt.loaded / evt.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        pathInput.value = response.path;
                        fileLabel.innerHTML = `<span class="text-green-600">✓ Uploaded: ${response.filename}</span>`;
                        progressBar.style.width = '100%';
                        progressBar.textContent = '100%';
                        statusText.textContent = 'Upload complete!';
                        statusText.classList.add('text-green-600');
                        statusText.classList.remove('text-black/60');
                    } else {
                        fileLabel.innerHTML = `<span class="text-red-600">✗ Upload failed: ${response.message || 'Unknown error'}</span>`;
                        statusText.textContent = 'Failed. Please try again.';
                        statusText.classList.add('text-red-600');
                        statusText.classList.remove('text-black/60');
                    }
                } else {
                    let errMsg = 'Upload failed';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errMsg = err.message || errMsg;
                    } catch(e) {}
                    fileLabel.innerHTML = `<span class="text-red-600">✗ ${errMsg}</span>`;
                    statusText.textContent = 'Server error. Please try again.';
                    statusText.classList.add('text-red-600');
                    statusText.classList.remove('text-black/60');
                }
            });

            xhr.addEventListener('error', function() {
                fileLabel.innerHTML = `<span class="text-red-600">✗ Network error. Please try again.</span>`;
                statusText.textContent = 'Connection failed.';
                statusText.classList.add('text-red-600');
                statusText.classList.remove('text-black/60');
            });

            xhr.send(formData);
        });

        // ===== TRACK CLONING =====
        document.getElementById('add-track').addEventListener('click', function() {
            const container = document.getElementById('tracks-container');
            const template = document.querySelector('.track-entry').cloneNode(true);

            // Reset the template
            template.dataset.index = trackIndex;
            const allElements = template.querySelectorAll('[name]');
            allElements.forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    const newName = name.replace(/tracks\[\d+\]/g, `tracks[${trackIndex}]`);
                    el.setAttribute('name', newName);
                }
                if (el.type === 'text' || el.type === 'textarea' || el.type === 'url') {
                    el.value = '';
                } else if (el.type === 'hidden') {
                    el.value = '';
                } else if (el.type === 'checkbox') {
                    el.checked = false;
                } else if (el.type === 'file') {
                    el.value = '';
                }
            });

            // Reset audio upload zones in cloned track
            const audioZones = template.querySelectorAll('.audio-upload-zone');
            audioZones.forEach(zone => {
                const pathInput = zone.querySelector('.audio-path-input');
                const fileInput = zone.querySelector('.audio-file-input');
                const fileLabel = zone.querySelector('.file-label');
                const progressContainer = zone.querySelector('.audio-progress');
                const progressBar = zone.querySelector('.audio-progress-bar');
                const statusText = zone.querySelector('.audio-status');

                if (pathInput) {
                    const name = pathInput.getAttribute('name');
                    if (name) pathInput.setAttribute('name', name.replace(/tracks\[\d+\]/g, `tracks[${trackIndex}]`));
                    pathInput.value = '';
                }
                if (fileInput) {
                    fileInput.value = '';
                }
                if (fileLabel) {
                    fileLabel.innerHTML = '<span class="text-black/40">No file selected</span>';
                }
                if (progressContainer) {
                    progressContainer.classList.add('hidden');
                }
                if (progressBar) {
                    progressBar.style.width = '0%';
                    progressBar.textContent = '0%';
                }
                if (statusText) {
                    statusText.textContent = 'Uploading...';
                    statusText.classList.remove('text-green-600', 'text-red-600');
                    statusText.classList.add('text-black/60');
                }
            });

            // Reset person-list containers (keep only one default entry for primary_artists and composers)
            const personLists = template.querySelectorAll('.person-list');
            personLists.forEach(list => {
                const entries = list.querySelectorAll('.person-entry');
                const section = list.closest('.credit-section');
                const field = section ? section.dataset.field : '';
                if (field === 'primary_artists' || field === 'composers') {
                    while (entries.length > 1) {
                        entries[entries.length - 1].remove();
                    }
                    if (entries[0]) {
                        const inputs = entries[0].querySelectorAll('input');
                        inputs.forEach(inp => {
                            if (inp.type === 'hidden') inp.value = '';
                            else if (inp.type === 'text') inp.value = '';
                            const name = inp.getAttribute('name');
                            if (name) inp.setAttribute('name', name.replace(/tracks\[\d+\]/g, `tracks[${trackIndex}]`));
                        });
                        reindexSection(section, trackIndex);
                    }
                } else {
                    entries.forEach(e => e.remove());
                }
            });

            // Update track number heading
            const heading = template.querySelector('.track-heading');
            if (heading) {
                heading.textContent = `Track ${trackIndex + 1}`;
            }

            container.appendChild(template);
            trackIndex++;
        });

        // ===== PERSON ENTRY MANAGEMENT =====
        function addPersonEntry(button, field, trackIdx) {
            const section = button.closest('.credit-section');
            const list = section.querySelector('.person-list');
            const entries = list.querySelectorAll('.person-entry');
            const nextIdx = entries.length;

            const entry = document.createElement('div');
            entry.className = 'person-entry flex items-center gap-2';
            entry.dataset.personIdx = nextIdx;

            const namePrefix = `tracks[${trackIdx}][${field}][${nextIdx}]`;

            entry.innerHTML = `
                <input type="text" name="${namePrefix}[name]" value=""
                    class="flex-1 px-3 py-2 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500"
                    placeholder="${getPlaceholder(field)}">
                <input type="hidden" name="${namePrefix}[spotify_url]" value="" class="link-spotify">
                <input type="hidden" name="${namePrefix}[apple_music_url]" value="" class="link-apple_music">
                <input type="hidden" name="${namePrefix}[youtube_url]" value="" class="link-youtube">
                <input type="hidden" name="${namePrefix}[tidal_url]" value="" class="link-tidal">
                <button type="button" onclick="openLinksModal(this)"
                    class="px-3 py-2 bg-gray-100 border-2 border-black font-extrabold text-[10px] uppercase hover:bg-brand-100 transition-all links-btn">
                    Links
                </button>
                <button type="button" onclick="this.closest('.person-entry').remove()"
                    class="px-2 py-2 bg-red-100 border-2 border-black font-extrabold text-xs text-red-700 hover:bg-red-200 transition-all">
                    ✕
                </button>
            `;

            list.appendChild(entry);
        }

        function getPlaceholder(field) {
            const placeholders = {
                'primary_artists': 'Artist name',
                'featuring': 'Artist name',
                'composers': 'Composer name',
                'lyricist': 'Lyricist name',
                'producers': 'Producer name',
                'vocals': 'Vocalist name',
            };
            return placeholders[field] || 'Name';
        }

        function reindexSection(section, trackIdx) {
            const field = section.dataset.field;
            const entries = section.querySelectorAll('.person-entry');
            entries.forEach((entry, idx) => {
                const prefix = `tracks[${trackIdx}][${field}][${idx}]`;
                const inputs = entry.querySelectorAll('input');
                inputs.forEach(inp => {
                    const name = inp.getAttribute('name');
                    if (name) {
                        inp.setAttribute('name', name.replace(/tracks\[\d+\]\[\w+\]\[\d+\]/g, prefix));
                    }
                });
                entry.dataset.personIdx = idx;
            });
        }

        // ===== LINKS MODAL =====
        let activeLinksEntry = null;

        function openLinksModal(button) {
            activeLinksEntry = button.closest('.person-entry');
            if (!activeLinksEntry) return;

            const spotify = activeLinksEntry.querySelector('.link-spotify');
            const apple = activeLinksEntry.querySelector('.link-apple_music');
            const youtube = activeLinksEntry.querySelector('.link-youtube');
            const tidal = activeLinksEntry.querySelector('.link-tidal');

            document.getElementById('modal-spotify').value = spotify ? spotify.value : '';
            document.getElementById('modal-apple_music').value = apple ? apple.value : '';
            document.getElementById('modal-youtube').value = youtube ? youtube.value : '';
            document.getElementById('modal-tidal').value = tidal ? tidal.value : '';

            document.getElementById('linksModalOverlay').classList.remove('hidden');
        }

        function saveLinksModal() {
            if (!activeLinksEntry) return;

            const spotify = activeLinksEntry.querySelector('.link-spotify');
            const apple = activeLinksEntry.querySelector('.link-apple_music');
            const youtube = activeLinksEntry.querySelector('.link-youtube');
            const tidal = activeLinksEntry.querySelector('.link-tidal');

            if (spotify) spotify.value = document.getElementById('modal-spotify').value;
            if (apple) apple.value = document.getElementById('modal-apple_music').value;
            if (youtube) youtube.value = document.getElementById('modal-youtube').value;
            if (tidal) tidal.value = document.getElementById('modal-tidal').value;

            closeLinksModal();
        }

        function closeLinksModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('linksModalOverlay').classList.add('hidden');
            activeLinksEntry = null;
        }
    </script>
</x-app-layout>
