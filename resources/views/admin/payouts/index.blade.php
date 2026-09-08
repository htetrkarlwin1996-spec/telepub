<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Payouts') }}</h2>
            <a href="{{ route('admin.payouts.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all rounded-none">+ New Payout</a>
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
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Invoice #</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Amount</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Fee</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Total</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-mono text-xs font-bold text-black">{{ $payout->invoice_number }}</td>
                                <td class="py-3 px-2 font-bold text-black/70">{{ $payout->artist->artist_name ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black">${{ number_format($payout->amount, 2) }}</td>
                                <td class="py-3 px-2 text-right font-semibold text-black/50 hidden md:table-cell">${{ number_format($payout->fee, 2) }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">${{ number_format($payout->total, 2) }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($payout->status == 'paid') bg-emerald-400 text-black
                                        @elseif($payout->status == 'pending') bg-amber-200 text-black
                                        @else bg-gray-100 text-black
                                        @endif">{{ ucfirst($payout->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-right font-semibold text-black/50 text-xs hidden lg:table-cell">{{ $payout->paid_at ? $payout->paid_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No payouts yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $payouts->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
