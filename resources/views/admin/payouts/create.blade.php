<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Process Payout') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            @if($errors->any())
                <div role="alert" class="mb-6 border-2 border-black bg-red-100 px-4 py-3 text-sm font-bold">{{ $errors->first() }}</div>
            @endif
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('admin.payouts.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="artist_id" value="Artist" />
                            <select id="artist_id" name="artist_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Artist</option>
                                @foreach($artists as $a)
                                <option value="{{ $a->id }}" @selected(old('artist_id') == $a->id)>{{ $a->artist_name }} ({{ money($a->available_balance) }} available)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="amount" value="Amount" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" min="0.01" name="amount" :value="old('amount')" required />
                        </div>
                        <div>
                            <x-input-label for="fee" value="Processing Fee" />
                            <x-text-input id="fee" class="block mt-1 w-full" type="number" step="0.01" min="0" name="fee" :value="old('fee', 0)" required />
                        </div>
                        <div>
                            <x-input-label for="currency" value="Currency" />
                            <select id="currency" name="currency" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="payment_method" value="Payment Method" />
                            <select id="payment_method" name="payment_method" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 rounded-none">
                                @foreach(['admin_payout' => 'Admin Payout', 'kbz_pay' => 'KBZ Pay', 'wave_pay' => 'Wave Pay', 'thai_bank_transfer' => 'Thai Bank Transfer', 'wire_transfer' => 'Wire Transfer', 'paypal' => 'PayPal', 'wise' => 'Wise', 'payoneer' => 'Payoneer'] as $method => $label)
                                    <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="payment_reference" value="Payment Reference" />
                            <x-text-input id="payment_reference" class="block mt-1 w-full" type="text" name="payment_reference" :value="old('payment_reference')" />
                        </div>
                        <div>
                            <x-input-label for="period_start" value="Period Start" />
                            <x-text-input id="period_start" class="block mt-1 w-full" type="date" name="period_start" />
                        </div>
                        <div>
                            <x-input-label for="period_end" value="Period End" />
                            <x-text-input id="period_end" class="block mt-1 w-full" type="date" name="period_end" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="notes" value="Notes" />
                            <textarea id="notes" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-primary-button>{{ __('Process Payout') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
