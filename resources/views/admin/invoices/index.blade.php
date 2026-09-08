<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Invoices') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
            <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
            @endif

            <!-- Create Invoice Form -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mb-6">
                <div class="p-6 border-b-2 border-black">
                    <h3 class="font-extrabold text-lg text-black tracking-tight">Create New Invoice</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.invoices.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <x-input-label for="artist_id" value="Artist" />
                            <select id="artist_id" name="artist_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select</option>
                                @foreach($artists as $a)
                                <option value="{{ $a->id }}">{{ $a->artist_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="amount" value="Amount" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" required />
                        </div>
                        <div>
                            <x-input-label for="type" value="Type" />
                            <select id="type" name="type" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="royalty">Royalty</option>
                                <option value="distribution_fee">Distribution Fee</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="currency" value="Currency" />
                            <select id="currency" name="currency" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="issue_date" value="Issue Date" />
                            <x-text-input id="issue_date" class="block mt-1 w-full" type="date" name="issue_date" value="{{ date('Y-m-d') }}" required />
                        </div>
                        <div>
                            <x-input-label for="due_date" value="Due Date" />
                            <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" />
                        </div>
                        <div class="md:col-span-3">
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="description" rows="2"></textarea>
                        </div>
                        <div class="md:col-span-3 flex justify-end">
                            <x-primary-button>{{ __('Create Invoice') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoice List -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Invoice #</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Amount</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Type</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Issued</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden lg:table-cell">Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-mono text-xs font-bold text-black">{{ $invoice->invoice_number }}</td>
                                <td class="py-3 px-2 font-bold text-black/70">{{ $invoice->artist->artist_name ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">${{ number_format($invoice->amount, 2) }}</td>
                                <td class="py-3 px-2 font-semibold text-black/70 hidden md:table-cell">{{ ucfirst(str_replace('_', ' ', $invoice->type)) }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($invoice->status == 'paid') bg-emerald-400 text-black
                                        @elseif($invoice->status == 'sent') bg-blue-200 text-black
                                        @else bg-gray-100 text-black
                                        @endif">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50 hidden lg:table-cell">{{ $invoice->issue_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-2 text-xs font-semibold text-black/50 hidden lg:table-cell">{{ $invoice->paid_date ? $invoice->paid_date->format('Y-m-d') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-8 text-center font-bold text-black/40">No invoices yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $invoices->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
