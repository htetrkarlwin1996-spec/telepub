<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">Transactions</h2>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0_#000]">
                    <p class="text-[10px] font-black uppercase text-black/50">Transactions</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format($transactionCount) }}</p>
                </div>
                <div class="bg-emerald-200 border-2 border-black p-5 shadow-[4px_4px_0_#000]">
                    <p class="text-[10px] font-black uppercase text-black/50">Paid Transactions</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format($paidCount) }}</p>
                </div>
                @forelse($currencyTotals as $currency => $total)
                    <div class="bg-brand-500 border-2 border-black p-5 shadow-[4px_4px_0_#000]">
                        <p class="text-[10px] font-black uppercase text-black/50">Total Received · {{ $currency }}</p>
                        <p class="mt-2 text-3xl font-black">{{ number_format((float) $total, 2) }}</p>
                    </div>
                @empty
                    <div class="bg-gray-100 border-2 border-black p-5 shadow-[4px_4px_0_#000] sm:col-span-2">
                        <p class="text-[10px] font-black uppercase text-black/50">Total Received</p>
                        <p class="mt-2 text-xl font-black text-black/40">No paid transactions</p>
                    </div>
                @endforelse
            </div>

            <form method="GET" action="{{ route('admin.transactions') }}" class="bg-white border-2 border-black p-5 shadow-[4px_4px_0_#000]">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="transaction-search" class="block text-[10px] font-black uppercase text-black/50 mb-1">Search</label>
                        <input id="transaction-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, user, email, artist or release"
                            class="w-full border-2 border-black px-3 py-2 font-bold focus:border-brand-500 focus:ring-0">
                    </div>
                    <div>
                        <label for="transaction-status" class="block text-[10px] font-black uppercase text-black/50 mb-1">Status</label>
                        <select id="transaction-status" name="status" class="w-full border-2 border-black px-3 py-2 font-bold focus:border-brand-500 focus:ring-0">
                            <option value="">All statuses</option>
                            @foreach(['paid', 'pending', 'failed', 'cancelled', 'expired', 'needs_review'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="transaction-provider" class="block text-[10px] font-black uppercase text-black/50 mb-1">Method</label>
                        <select id="transaction-provider" name="provider" class="w-full border-2 border-black px-3 py-2 font-bold focus:border-brand-500 focus:ring-0">
                            <option value="">All methods</option>
                            @foreach(['stripe' => 'Stripe / Card', 'paypal' => 'PayPal', 'myanmyanpay' => 'MyanMyanPay MMQR', 'offline' => 'Thai Bank Transfer'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['provider'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex gap-3">
                    <button class="border-2 border-black bg-brand-500 px-5 py-2 text-xs font-black uppercase">Apply Filters</button>
                    <a href="{{ route('admin.transactions') }}" class="border-2 border-black bg-white px-5 py-2 text-xs font-black uppercase">Clear</a>
                </div>
            </form>

            <div class="bg-white border-2 border-black shadow-[4px_4px_0_#000] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-sm">
                        <thead>
                            <tr class="border-b-2 border-black bg-black text-white">
                                <th class="p-3 text-left text-xs font-black uppercase">Date / Reference</th>
                                <th class="p-3 text-left text-xs font-black uppercase">User / Artist</th>
                                <th class="p-3 text-left text-xs font-black uppercase">Release</th>
                                <th class="p-3 text-left text-xs font-black uppercase">Method</th>
                                <th class="p-3 text-right text-xs font-black uppercase">Amount</th>
                                <th class="p-3 text-center text-xs font-black uppercase">Status</th>
                                <th class="p-3 text-left text-xs font-black uppercase">Add-ons</th>
                                <th class="p-3 text-center text-xs font-black uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                @php $transactionAddons = collect($transaction->addon_services ?? []); @endphp
                                <tr class="border-b border-black/20 align-top hover:bg-brand-500/10">
                                    <td class="p-3">
                                        <p class="font-bold">{{ ($transaction->paid_at ?? $transaction->created_at)->format('Y-m-d H:i') }}</p>
                                        <p class="mt-1 max-w-[180px] break-all font-mono text-[10px] font-bold text-black/50">{{ $transaction->reference }}</p>
                                    </td>
                                    <td class="p-3">
                                        <p class="font-black">{{ $transaction->user->name ?? 'Unknown User' }}</p>
                                        <p class="text-xs font-bold text-black/50">{{ $transaction->user->email ?? '—' }}</p>
                                        <p class="mt-1 text-xs font-extrabold">Artist: {{ $transaction->album?->artist?->artist_name ?? '—' }}</p>
                                    </td>
                                    <td class="p-3">
                                        <p class="font-black">{{ $transaction->album?->title ?? 'Deleted release' }}</p>
                                        <p class="text-xs font-bold text-black/50">{{ ucfirst($transaction->album?->release_type ?? '—') }}</p>
                                    </td>
                                    <td class="p-3 font-extrabold">{{ ['stripe' => 'Stripe / Card', 'paypal' => 'PayPal', 'myanmyanpay' => 'MyanMyanPay MMQR', 'offline' => 'Thai Bank Transfer'][$transaction->provider] ?? ucwords(str_replace('_', ' ', $transaction->provider)) }}</td>
                                    <td class="p-3 text-right whitespace-nowrap text-base font-black">{{ strtoupper($transaction->currency) }} {{ number_format((float) $transaction->amount, 2) }}</td>
                                    <td class="p-3 text-center">
                                        <span class="inline-flex border border-black px-2 py-1 text-[10px] font-black uppercase
                                            @if($transaction->status === 'paid') bg-emerald-300
                                            @elseif($transaction->status === 'pending') bg-amber-200
                                            @elseif($transaction->status === 'cancelled') bg-gray-200
                                            @else bg-red-200 @endif">{{ str_replace('_', ' ', $transaction->status) }}</span>
                                    </td>
                                    <td class="p-3">
                                        @forelse($transactionAddons as $addonKey)
                                            <span class="mb-1 block text-[10px] font-extrabold">✓ {{ data_get($addonCatalog, $addonKey.'.name', ucwords(str_replace('_', ' ', $addonKey))) }}</span>
                                        @empty
                                            <span class="text-xs font-bold text-black/40">None</span>
                                        @endforelse
                                    </td>
                                    <td class="p-3 text-center">
                                        @if($transaction->album)
                                            <a href="{{ route('admin.releases.show', $transaction->album) }}" class="inline-flex border-2 border-black bg-white px-3 py-2 text-[10px] font-black uppercase hover:bg-brand-500">View Release</a>
                                        @else
                                            <span class="text-xs font-bold text-black/40">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="p-10 text-center font-bold text-black/40">No transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-5 border-t-2 border-black">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
