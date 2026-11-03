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

            @if($pendingArtistUsers->isNotEmpty())
            <div class="mb-8 border-2 border-black bg-amber-100 shadow-[4px_4px_0_#000]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b-2 border-black p-5">
                    <div>
                        <h3 class="text-lg font-black uppercase">Pending Artist Setup</h3>
                        <p class="mt-1 text-xs font-bold text-black/60">Registered accounts that have not completed their Artist Profile yet.</p>
                    </div>
                    <span class="border-2 border-black bg-amber-300 px-3 py-1 text-xs font-black uppercase">{{ $pendingArtistUsers->total() }} Pending</span>
                </div>
                <div class="overflow-x-auto p-5">
                    <table class="min-w-[720px] w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="px-2 py-3 text-left text-xs font-extrabold uppercase tracking-wider">Name</th>
                                <th class="px-2 py-3 text-left text-xs font-extrabold uppercase tracking-wider">Email</th>
                                <th class="px-2 py-3 text-left text-xs font-extrabold uppercase tracking-wider">Login Method</th>
                                <th class="px-2 py-3 text-center text-xs font-extrabold uppercase tracking-wider">Email Status</th>
                                <th class="px-2 py-3 text-right text-xs font-extrabold uppercase tracking-wider">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingArtistUsers as $pendingUser)
                            <tr class="border-b border-black/10 bg-white/50">
                                <td class="px-2 py-3 font-black">{{ $pendingUser->name }}</td>
                                <td class="px-2 py-3 font-semibold text-black/60">{{ $pendingUser->email }}</td>
                                <td class="px-2 py-3">
                                    @if($pendingUser->google_id)
                                        <span class="inline-flex border border-black bg-blue-100 px-2 py-1 text-[10px] font-black uppercase">Google</span>
                                    @else
                                        <span class="inline-flex border border-black bg-white px-2 py-1 text-[10px] font-black uppercase">Email & Password</span>
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-center">
                                    <span class="inline-flex border border-black px-2 py-1 text-[10px] font-black uppercase {{ $pendingUser->email_verified_at ? 'bg-emerald-300' : 'bg-red-200' }}">
                                        {{ $pendingUser->email_verified_at ? 'Verified' : 'Not Verified' }}
                                    </span>
                                </td>
                                <td class="px-2 py-3 text-right text-xs font-bold text-black/50">{{ $pendingUser->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $pendingArtistUsers->links() }}</div>
                </div>
            </div>
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
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Gross Revenue</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist Earnings</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($artists as $artist)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $artist->artist_name }}</td>
                                <td class="py-3 px-2 text-black/50 hidden md:table-cell font-semibold">
                                    @if($artist->user)
                                        {{ $artist->user->email }}
                                    @elseif($artist->masterAccounts->first()?->owner)
                                        <div>{{ $artist->masterAccounts->first()->owner->email }}</div>
                                        <div class="text-[10px] font-black uppercase text-black/40">Managed by {{ $artist->masterAccounts->first()->name }}</div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-black/70 hidden lg:table-cell font-semibold">{{ $artist->genre ?? '-' }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $artist->albums_count }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $artist->songs_count }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black/70">{{ money((float) ($artist->royalties_sum_amount ?? 0)) }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">{{ money($artist->getArtistShareAttribute((float) ($artist->royalties_sum_amount ?? 0))) }}</td>
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
                            <tr><td colspan="8" class="py-8 text-center font-bold text-black/40">No artists found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $artists->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
