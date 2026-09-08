<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('My Distributions') }}</h2>
            <a href="{{ route('artist.distributions.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ New Distribution</a>
        </div>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
            <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="bg-red-200 border-2 border-black text-black font-bold px-4 py-3 mb-6 text-sm">{{ session('error') }}</div>
            @endif
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Song</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Store</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Submitted</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Live Date</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributions as $dist)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2">
                                    <a href="{{ route('artist.distributions.show', $dist) }}" class="font-bold text-black hover:underline hover:decoration-brand-500 hover:decoration-2">{{ $dist->song->title ?? 'N/A' }}</a>
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <x-store-logo :store="$dist->store" size="5" />
                                        <span class="font-semibold text-black/70">{{ $dist->store->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($dist->status == 'pending') bg-gray-100 text-black
                                        @elseif($dist->status == 'submitted') bg-amber-200 text-black
                                        @elseif($dist->status == 'approved') bg-blue-200 text-black
                                        @elseif($dist->status == 'live') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($dist->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50">{{ $dist->submitted_at ? $dist->submitted_at->format('Y-m-d') : '-' }}</td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50">{{ $dist->live_at ? $dist->live_at->format('Y-m-d') : '-' }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black">${{ number_format($dist->distribution_fee, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-8 text-center font-bold text-black/40">No distributions yet. <a href="{{ route('artist.distributions.create') }}" class="text-black underline decoration-brand-500 decoration-2">Distribute your music</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $distributions->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
