<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('My Albums') }}</h2>
            <a href="{{ route('artist.albums.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ New Album</a>
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
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Cover</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Title</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Tracks</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Genre</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Release Date</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($albums as $album)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2">
                                    @if($album->cover_art)
                                    <img src="{{ asset('storage/'.$album->cover_art) }}" class="w-10 h-10 object-cover border border-black">
                                    @else
                                    <div class="w-10 h-10 bg-gray-100 border border-black flex items-center justify-center font-bold text-black/40 text-xs">No art</div>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    <a href="{{ route('artist.albums.show', $album) }}" class="font-bold text-black hover:underline hover:decoration-brand-500 hover:decoration-2">{{ $album->title }}</a>
                                </td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $album->songs_count }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70">{{ $album->genre ?? '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($album->status == 'draft') bg-gray-100 text-black
                                        @elseif($album->status == 'submitted') bg-amber-200 text-black
                                        @elseif($album->status == 'approved') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($album->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50">{{ $album->release_date ? $album->release_date->format('Y-m-d') : '-' }}</td>
                                <td class="py-3 px-2 text-right">
                                    <a href="{{ route('artist.albums.edit', $album) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs mr-3">Edit</a>
                                    <a href="{{ route('artist.albums.show', $album) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No albums yet. <a href="{{ route('artist.albums.create') }}" class="text-black underline decoration-brand-500 decoration-2">Create your first album</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $albums->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
