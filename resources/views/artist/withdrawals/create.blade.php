<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Request Withdrawal') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <!-- Balance Info -->
            <div class="bg-brand-500 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6 mb-6">
                <div class="text-xs font-extrabold uppercase tracking-wider text-black/60">Available Balance</div>
                <div class="text-3xl font-black text-black mt-1">${{ number_format($artist->available_balance, 2) }}</div>
                <div class="text-xs font-extrabold text-black/60 mt-2">Minimum withdrawal: $10.00</div>
            </div>

            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('artist.withdrawals.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-input-label for="amount" value="Withdrawal Amount ($)" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" min="10" max="{{ $artist->available_balance }}" name="amount" required placeholder="Enter amount to withdraw" />
                            <p class="text-xs font-bold text-black/50 mt-1">Min: $10.00 | Max: ${{ number_format($artist->available_balance, 2) }}</p>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="payment_method" value="Payment Method *" />
                            <select id="payment_method" name="payment_method" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Method</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="wise">Wise</option>
                                <option value="payoneer">Payoneer</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="payment_details" value="Payment Details" />
                            <textarea id="payment_details" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="payment_details" rows="2" placeholder="Enter your PayPal email, bank account details, etc."></textarea>
                            <p class="text-xs font-bold text-black/50 mt-1">Make sure your payment details are correct to avoid delays.</p>
                        </div>
                        <div>
                            <x-input-label for="notes" value="Notes (Optional)" />
                            <x-text-input id="notes" class="block mt-1 w-full" type="text" name="notes" placeholder="Any notes for the admin" />
                        </div>
                    </div>

                    <div class="bg-brand-500/20 border-2 border-black p-4 mt-4">
                        <h4 class="font-extrabold text-black mb-2">Summary</h4>
                        <div class="flex justify-between text-sm font-bold text-black/70"><span>Withdrawal Amount:</span><span id="summary-amount">$0.00</span></div>
                        <div class="flex justify-between text-sm font-bold text-black/70"><span>Processing Fee (2%):</span><span id="summary-fee">$0.00</span></div>
                        <div class="flex justify-between text-sm font-black text-black border-t-2 border-black pt-1 mt-1"><span>You'll Receive:</span><span id="summary-total">$0.00</span></div>
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
        document.getElementById('amount').addEventListener('input', function() {
            let amount = parseFloat(this.value) || 0;
            let fee = amount * 0.02;
            let total = amount - fee;
            document.getElementById('summary-amount').textContent = '$' + amount.toFixed(2);
            document.getElementById('summary-fee').textContent = '$' + fee.toFixed(2);
            document.getElementById('summary-total').textContent = '$' + total.toFixed(2);
        });
    </script>
</x-app-layout>
