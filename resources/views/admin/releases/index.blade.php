<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-black uppercase">Releases</h1>
                    <p class="text-sm font-bold text-black/60 mt-1">Manage all artist releases</p>
                </div>
                <a href="{{ route('admin.releases.create-step1') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Create Release
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    {{ session('success') }}
                </div>
            @endif

            @if($releases->count() > 0)
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-black bg-gray-100">
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Release</th>
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Artist</th>
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Type</th>
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Tracks</th>
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Submitted</th>
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Status</th>
                                <th class="px-6 py-4 font-extrabold text-xs uppercase text-black/60">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @foreach($releases as $album)
                                <tr class="hover:bg-brand-50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($album->cover_art)
                                                <div class="w-10 h-10 border-2 border-black overflow-hidden flex-shrink-0">
                                                    <img src="{{ Storage::url($album->cover_art) }}" alt="" class="w-full h-full object-cover">
                                                </div>
                                            @else
                                                <div class="w-10 h-10 bg-gray-200 border-2 border-black flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-extrabold text-sm">{{ $album->title }}</p>
                                                <p class="text-[10px] font-bold text-black/50">{{ $album->genre }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-sm">{{ $album->artist->artist_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-[10px] font-extrabold uppercase border-2 border-black bg-blue-100">
                                            {{ ucfirst($album->release_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-sm">{{ $album->songs->count() }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-black/60">{{ $album->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-[10px] font-extrabold uppercase border-2 border-black
                                            @if($album->status === 'approved') bg-green-200
                                            @elseif($album->status === 'rejected') bg-red-200
                                            @else bg-yellow-200 @endif">
                                            {{ $album->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.releases.show', $album) }}" class="px-3 py-1.5 bg-black text-white font-extrabold text-xs uppercase border-2 border-black hover:bg-brand-500 hover:text-black transition-all">
                                                View
                                            </a>
                                            <a href="{{ route('admin.releases.edit-step1', $album) }}" class="px-3 py-1.5 bg-amber-200 border-2 border-black font-extrabold text-xs uppercase hover:bg-amber-300 transition-all" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.albums.destroy', $album) }}" onsubmit="return confirm('Delete this release? This will also remove all associated songs, distributions, and royalties.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-red-200 border-2 border-black font-extrabold text-xs uppercase hover:bg-red-300 transition-all" title="Delete">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $releases->links() }}
                </div>
            @else
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-black/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                    <h3 class="text-xl font-black text-black mb-2">No Releases Yet</h3>
                    <p class="text-sm font-bold text-black/50">No releases have been submitted by artists.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
