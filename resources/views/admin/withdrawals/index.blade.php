<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Withdrawal Requests') }}</h2>
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
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Amount</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Fee</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Net</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Method</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Requested</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals as $w)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $w->artist->artist_name ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black">${{ number_format($w->amount, 2) }}</td>
                                <td class="py-3 px-2 text-right font-semibold text-black/50 hidden md:table-cell">${{ number_format($w->fee, 2) }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">${{ number_format($w->total, 2) }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70 hidden lg:table-cell">{{ $w->payment_method }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($w->status == 'pending') bg-amber-200 text-black
                                        @elseif($w->status == 'approved') bg-blue-200 text-black
                                        @elseif($w->status == 'completed') bg-emerald-400 text-black
                                        @else bg-red-200 text-black
                                        @endif">{{ ucfirst($w->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50 hidden lg:table-cell">{{ $w->requested_at->format('Y-m-d') }}</td>
                                <td class="py-3 px-2 text-right">
                                    @if($w->status == 'pending')
                                    <form action="{{ route('admin.withdrawals.approve', $w) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs mr-2">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.reject', $w) }}" method="POST" class="inline" onsubmit="return confirm('Reject this withdrawal? Funds will be returned to artist.')">
                                        @csrf
                                        <input type="hidden" name="admin_notes" value="Rejected by admin">
                                        <button type="submit" class="font-extrabold text-red-600 underline decoration-red-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">Reject</button>
                                    </form>
                                    @elseif($w->status == 'approved')
                                    <form action="{{ route('admin.withdrawals.complete', $w) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="font-extrabold text-emerald-600 underline decoration-emerald-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">Mark Complete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="py-8 text-center font-bold text-black/40">No withdrawal requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $withdrawals->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
