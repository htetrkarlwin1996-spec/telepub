<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('All Songs') }}</h2>
            <a href="{{ route('admin.songs.create') }}" class="px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                + Create Song
            </a>
        </div>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Title</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Album</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Track</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">ISRC</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($songs as $song)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $song->title }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70">{{ $song->artist->artist_name ?? 'N/A' }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70 hidden md:table-cell">{{ $song->album->title ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $song->track_number ?? '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($song->status == 'draft') bg-gray-100 text-black
                                        @elseif($song->status == 'submitted') bg-amber-200 text-black
                                        @elseif($song->status == 'approved') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($song->status) }}</span>
                                </td>
                                <td class="py-3 px-2 font-mono text-xs font-semibold text-black/50 hidden lg:table-cell">{{ $song->isrc_code ?? '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.songs.edit', $song) }}" class="inline-flex items-center px-2 py-1 bg-amber-200 border-2 border-black text-xs font-extrabold uppercase hover:bg-amber-300 transition-all" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.songs.destroy', $song) }}" onsubmit="return confirm('Delete this song? This will also remove associated distributions and royalties.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-200 border-2 border-black text-xs font-extrabold uppercase hover:bg-red-300 transition-all" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No songs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $songs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
