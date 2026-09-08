<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ $song->title }}</h2>
            <a href="{{ route('artist.songs.edit', $song) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-sm">Edit Song</a>
        </div>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-extrabold text-lg text-black tracking-tight mb-4">Song Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Title:</dt><dd class="font-bold text-black">{{ $song->title }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Album:</dt><dd class="font-semibold text-black/70">{{ $song->album->title ?? 'N/A' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Track #:</dt><dd class="font-semibold text-black/70">{{ $song->track_number ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Duration:</dt><dd class="font-semibold text-black/70">{{ $song->duration ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Genre:</dt><dd class="font-semibold text-black/70">{{ $song->genre ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Language:</dt><dd class="font-semibold text-black/70">{{ $song->language ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Status:</dt><dd><span class="text-xs font-bold border border-black px-2 py-1
                                @if($song->status == 'draft') bg-gray-100 text-black
                                @elseif($song->status == 'submitted') bg-amber-200 text-black
                                @elseif($song->status == 'approved') bg-emerald-400 text-black
                                @else bg-red-200 text-black
                                @endif">{{ ucfirst($song->status) }}</span></dd></div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg text-black tracking-tight mb-4">Metadata</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">ISRC:</dt><dd class="font-mono text-sm font-semibold text-black/70">{{ $song->isrc_code ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Explicit:</dt><dd class="font-bold text-black">{{ $song->explicit ? 'Yes' : 'No' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Composers:</dt><dd class="font-semibold text-black/70">{{ $song->composers ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Producers:</dt><dd class="font-semibold text-black/70">{{ $song->producers ?? '-' }}</dd></div>
                            <div class="flex justify-between border-b border-black/10 pb-2"><dt class="font-bold text-black/60">Featuring:</dt><dd class="font-semibold text-black/70">{{ $song->featuring ?? '-' }}</dd></div>
                            @if($song->audio_file)
                            <div class="mt-4">
                                <audio controls class="w-full border-2 border-black">
                                    <source src="{{ asset('storage/'.$song->audio_file) }}" type="audio/mpeg">
                                </audio>
                            </div>
                            @endif
                        </dl>
                    </div>
                    @if($song->lyrics)
                    <div class="md:col-span-2 mt-4">
                        <h3 class="font-extrabold text-lg text-black tracking-tight mb-2">Lyrics</h3>
                        <pre class="bg-gray-100 border-2 border-black p-4 text-sm font-semibold text-black whitespace-pre-wrap">{{ $song->lyrics }}</pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
