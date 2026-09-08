<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Manage Artists') }}</h2>
            <a href="{{ route('admin.artists.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ New Artist</a>
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
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Email</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Genre</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Albums</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Songs</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Earnings</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($artists as $artist)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $artist->artist_name }}</td>
                                <td class="py-3 px-2 text-black/50 hidden md:table-cell font-semibold">{{ $artist->user->email ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-black/70 hidden lg:table-cell font-semibold">{{ $artist->genre ?? '-' }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $artist->albums_count }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $artist->songs_count }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">${{ number_format($artist->getArtistShareAttribute((float) ($artist->royalties_sum_amount ?? 0)), 2) }}</td>
                                <td class="py-3 px-2 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.artists.impersonate', $artist) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-brand-500 border-2 border-black font-extrabold text-[10px] uppercase shadow-[2px_2px_0_#000]">Login As User</button>
                                        </form>
                                        <a href="{{ route('admin.artists.edit', $artist) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No artists found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $artists->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
