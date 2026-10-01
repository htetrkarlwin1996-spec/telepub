<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div><h2 class="text-2xl font-black">{{ $account->name }}</h2><p class="text-sm font-bold text-black/50">Master Account</p></div>
            <a href="{{ route('manager.artists.create') }}" class="border-2 border-black bg-brand-500 px-4 py-2 text-xs font-black uppercase shadow-[3px_3px_0_#000]">+ Create Artist</a>
        </div>
    </x-slot>
    <div class="px-6 py-8">
        <div class="mx-auto max-w-7xl">
            @if(session('success'))<div class="mb-6 border-2 border-black bg-emerald-300 p-4 font-bold">{{ session('success') }}</div>@endif
            <div class="grid gap-4 md:grid-cols-4">
                <div class="border-2 border-black bg-white p-5 shadow-[4px_4px_0_#000]"><div class="text-xs font-black uppercase text-black/50">Artists</div><div class="mt-2 text-3xl font-black">{{ $artists->count() }}</div></div>
                <div class="border-2 border-black bg-white p-5 shadow-[4px_4px_0_#000]"><div class="text-xs font-black uppercase text-black/50">Artist Earnings</div><div class="mt-2 text-3xl font-black">{{ money($artistEarnings) }}</div></div>
                <div class="border-2 border-black bg-white p-5 shadow-[4px_4px_0_#000]"><div class="text-xs font-black uppercase text-black/50">Master Earnings</div><div class="mt-2 text-3xl font-black">{{ money($account->total_earnings) }}</div></div>
                <div class="border-2 border-black bg-white p-5 shadow-[4px_4px_0_#000]"><div class="text-xs font-black uppercase text-black/50">TeleMusic Fee</div><div class="mt-2 text-3xl font-black">{{ number_format($account->platform_fee_percentage, 2) }}%</div></div>
            </div>
            <div class="mt-8 overflow-x-auto border-2 border-black bg-white shadow-[4px_4px_0_#000]">
                <table class="w-full text-sm">
                    <thead><tr class="border-b-2 border-black bg-gray-100"><th class="p-3 text-left">Artist</th><th class="p-3">Catalog</th><th class="p-3">Earnings</th><th class="p-3">Access</th><th class="p-3">Master Fee</th><th class="p-3 text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($artists as $artist)
                        <tr class="border-b border-black/10">
                            <td class="p-3"><div class="font-black">{{ $artist->artist_name }}</div><div class="text-xs font-bold text-black/40">{{ $artist->user?->email ?? 'No login yet' }}</div></td>
                            <td class="p-3 text-center font-bold">{{ $artist->albums_count }} releases · {{ $artist->songs_count }} tracks</td>
                            <td class="p-3 text-center font-black">{{ money($earningsByArtist[$artist->id] ?? 0) }}</td>
                            <td class="p-3 text-center font-bold">{{ str_replace('_', ' ', strtoupper($artist->pivot->access_level)) }}</td>
                            <td class="p-3 text-center font-black">{{ number_format($artist->pivot->management_fee_percentage ?? $account->default_management_fee_percentage, 2) }}%</td>
                            <td class="p-3">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('manager.artists.select', $artist) }}">@csrf<button class="border-2 border-black bg-brand-500 px-3 py-2 text-xs font-black uppercase">Manage</button></form>
                                    <form method="POST" action="{{ route('manager.artists.update', $artist) }}" class="flex gap-2">@csrf @method('PATCH')
                                        <input type="number" step="0.01" min="0" max="{{ $account->maximum_management_fee_percentage }}" name="management_fee_percentage" value="{{ $artist->pivot->management_fee_percentage }}" placeholder="Default" class="w-24 border-2 border-black px-2 py-1 text-xs font-bold">
                                        <select name="access_level" class="border-2 border-black px-2 py-1 text-xs font-bold"><option value="report_only" @selected($artist->pivot->access_level === 'report_only')>Report only</option><option value="full_access" @selected($artist->pivot->access_level === 'full_access')>Full access</option></select>
                                        <button class="border-2 border-black bg-white px-3 py-2 text-xs font-black">SAVE</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty<tr><td colspan="6" class="p-8 text-center font-bold text-black/40">No managed artists yet.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
