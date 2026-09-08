<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Withdrawals') }}</h2>
            <a href="{{ route('artist.withdrawals.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ New Withdrawal</a>
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
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">ID</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Amount</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Fee</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Net</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Method</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Requested</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Processed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals as $w)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 text-xs font-mono font-bold text-black/50">#{{ $w->id }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black">${{ number_format($w->amount, 2) }}</td>
                                <td class="py-3 px-2 text-right font-semibold text-black/50">${{ number_format($w->fee, 2) }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">${{ number_format($w->total, 2) }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70 capitalize">{{ $w->payment_method }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($w->status == 'pending') bg-amber-200 text-black
                                        @elseif($w->status == 'approved') bg-blue-200 text-black
                                        @elseif($w->status == 'processing') bg-purple-200 text-black
                                        @elseif($w->status == 'completed') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($w->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50">{{ $w->requested_at->format('Y-m-d') }}</td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50">{{ $w->processed_at ? $w->processed_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="py-8 text-center font-bold text-black/40">No withdrawal requests. <a href="{{ route('artist.withdrawals.create') }}" class="text-black underline decoration-brand-500 decoration-2">Request a withdrawal</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $withdrawals->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
