<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('All Albums') }}</h2>
            <a href="{{ route('admin.albums.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Create Album
            </a>
        </div>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Title</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Tracks</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Genre</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Release Date</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($albums as $album)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $album->title }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70">{{ $album->artist->artist_name ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $album->songs_count }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70 hidden md:table-cell">{{ $album->genre ?? '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($album->status == 'draft') bg-gray-100 text-black
                                        @elseif($album->status == 'submitted') bg-amber-200 text-black
                                        @elseif($album->status == 'approved') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($album->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50 hidden lg:table-cell">{{ $album->release_date ? $album->release_date->format('Y-m-d') : '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.releases.show', $album) }}" class="p-1.5 bg-blue-100 border border-black hover:bg-blue-200 transition-colors" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.albums.edit', $album) }}" class="p-1.5 bg-amber-100 border border-black hover:bg-amber-200 transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.albums.destroy', $album) }}" class="inline" onsubmit="return confirm('Delete this album? All songs, distributions, and royalties will be permanently deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-red-100 border border-black hover:bg-red-200 transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No albums found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $albums->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
