<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Request Withdrawal') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            @if($errors->any())
                <div role="alert" class="mb-6 border-2 border-black bg-red-100 px-4 py-3 text-sm font-bold">
                    Withdrawal could not be submitted: {{ $errors->first() }}
                </div>
            @endif
            <!-- Balance Info -->
            <div class="bg-brand-500 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6 mb-6">
                <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Available Balance</div>
                <div class="text-3xl font-black text-black mt-1">{{ money($artist->available_balance) }}</div>
                <div class="text-xs font-extrabold text-black/60 mt-2">Minimum withdrawal: {{ money($minimumWithdrawalAmount) }}</div>
            </div>

            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('artist.withdrawals.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-input-label for="amount" :value="'Withdrawal Amount ('.display_currency().')'" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" min="{{ $minimumWithdrawalAmount }}" max="{{ $artist->available_balance }}" name="amount" :value="old('amount')" required placeholder="Enter amount to withdraw" />
                            <p class="text-xs font-bold text-black/50 mt-1">Min: {{ money($minimumWithdrawalAmount) }} | Max: {{ money($artist->available_balance) }}</p>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="payment_method" value="Payment Method *" />
                            <select id="payment_method" name="payment_method" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Method</option>
                                <option value="kbz_pay" @selected(old('payment_method') === 'kbz_pay')>KBZ Pay</option>
                                <option value="wave_pay" @selected(old('payment_method') === 'wave_pay')>Wave Pay</option>
                                <option value="thai_bank_transfer" @selected(old('payment_method') === 'thai_bank_transfer')>Thai Bank Transfer</option>
                                <option value="wire_transfer" @selected(old('payment_method') === 'wire_transfer')>Wire Transfer</option>
                                <option value="paypal" @selected(old('payment_method') === 'paypal')>PayPal</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                                <option value="wise" @selected(old('payment_method') === 'wise')>Wise</option>
                                <option value="payoneer" @selected(old('payment_method') === 'payoneer')>Payoneer</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>
                        @foreach([
                            'account_name' => 'Account Holder Name',
                            'phone' => 'Phone Number',
                            'bank_name' => 'Bank Name',
                            'account_number' => 'Account Number / IBAN',
                            'branch' => 'Bank Branch (Optional)',
                            'beneficiary_name' => 'Beneficiary Name',
                            'swift_bic' => 'SWIFT / BIC',
                            'bank_address' => 'Bank Address',
                            'beneficiary_address' => 'Beneficiary Address',
                            'bank_country' => 'Bank Country',
                            'routing_number' => 'Routing Number (If Applicable)',
                        ] as $field => $label)
                        <div data-payment-field="{{ $field }}" class="hidden">
                            <x-input-label :for="$field" :value="$label" />
                            <x-text-input :id="$field" class="block mt-1 w-full" type="text" :name="$field" :value="old($field)" />
                            <x-input-error :messages="$errors->get($field)" class="mt-2" />
                        </div>
                        @endforeach
                        <div data-payment-field="payment_details" class="hidden">
                            <x-input-label for="payment_details" value="Payment Details" />
                            <textarea id="payment_details" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm" name="payment_details" rows="3">{{ old('payment_details') }}</textarea>
                            <x-input-error :messages="$errors->get('payment_details')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="notes" value="Notes (Optional)" />
                            <x-text-input id="notes" class="block mt-1 w-full" type="text" name="notes" placeholder="Any notes for the admin" />
                        </div>
                    </div>

                    <div class="bg-brand-500/20 border-2 border-black p-4 mt-4">
                        <h4 class="font-extrabold text-black mb-2">Summary</h4>
                        <div class="flex justify-between text-sm font-bold text-black/70"><span>Withdrawal Amount:</span><span id="summary-amount">{{ money(0) }}</span></div>
                        <div class="flex justify-between text-sm font-bold text-black/70"><span>Processing Fee ({{ number_format($withdrawalFeePercentage, 2) }}%):</span><span id="summary-fee">{{ money(0) }}</span></div>
                        <div class="flex justify-between text-sm font-black text-black border-t-2 border-black pt-1 mt-1"><span>You'll Receive:</span><span id="summary-total">{{ money(0) }}</span></div>
                    </div>

                    <div class="flex justify-end mt-4 gap-3">
                        <a href="{{ route('artist.withdrawals') }}" class="inline-flex items-center px-4 py-2 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest hover:bg-black/5 transition-all rounded-none">Cancel</a>
                        <x-primary-button>{{ __('Submit Withdrawal Request') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const methodFields = {
            kbz_pay: ['account_name', 'phone'],
            wave_pay: ['account_name', 'phone'],
            thai_bank_transfer: ['account_name', 'bank_name', 'account_number', 'branch'],
            wire_transfer: ['beneficiary_name', 'bank_name', 'account_number', 'swift_bic', 'bank_address', 'beneficiary_address', 'bank_country', 'routing_number'],
            paypal: ['payment_details'], bank_transfer: ['payment_details'], wise: ['payment_details'], payoneer: ['payment_details']
        };
        const optionalFields = ['branch', 'routing_number'];
        function updatePaymentFields() {
            const visible = methodFields[document.getElementById('payment_method').value] || [];
            document.querySelectorAll('[data-payment-field]').forEach(section => {
                const field = section.dataset.paymentField;
                const show = visible.includes(field);
                section.classList.toggle('hidden', !show);
                section.querySelector('input, textarea').required = show && !optionalFields.includes(field);
            });
        }
        document.getElementById('payment_method').addEventListener('change', updatePaymentFields);
        updatePaymentFields();
        document.getElementById('amount').addEventListener('input', function() {
            let amount = parseFloat(this.value) || 0;
            let fee = amount * (@json($withdrawalFeePercentage) / 100);
            let total = amount - fee;
            const currency = @json(display_currency());
            document.getElementById('summary-amount').textContent = currency + ' ' + amount.toFixed(2);
            document.getElementById('summary-fee').textContent = currency + ' ' + fee.toFixed(2);
            document.getElementById('summary-total').textContent = currency + ' ' + total.toFixed(2);
        });
    </script>
</x-app-layout>
