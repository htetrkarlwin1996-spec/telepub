<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('My Songs') }}</h2>
            <a href="{{ route('artist.songs.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ Upload Song</a>
        </div>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
            <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
            @endif
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Title</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Album</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Track</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Duration</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">ISRC</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($songs as $song)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $song->title }}</td>
                                <td class="py-3 px-2 font-semibold text-black/50">{{ $song->album->title ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $song->track_number ?? '-' }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70">{{ $song->duration ?? '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($song->status == 'draft') bg-gray-100 text-black
                                        @elseif($song->status == 'submitted') bg-amber-200 text-black
                                        @elseif($song->status == 'approved') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($song->status) }}</span>
                                </td>
                                <td class="py-3 px-2 font-mono text-xs font-semibold text-black/50">{{ $song->isrc_code ?? '-' }}</td>
                                <td class="py-3 px-2 text-right">
                                    <a href="{{ route('artist.songs.show', $song) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs mr-3">View</a>
                                    <a href="{{ route('artist.songs.edit', $song) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No songs yet. <a href="{{ route('artist.songs.create') }}" class="text-black underline decoration-brand-500 decoration-2">Upload your first song</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $songs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
