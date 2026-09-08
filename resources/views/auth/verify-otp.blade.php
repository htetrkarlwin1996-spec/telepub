<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-black tracking-tight">
            {{ $type === 'registration' ? 'Verify Your Email' : 'Reset Password' }}
        </h2>
        <p class="mt-2 text-sm font-bold text-black/50">
            Enter the 6-digit OTP code sent to<br>
            <span class="text-black font-extrabold">{{ $email }}</span>
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <!-- OTP Code -->
        <div>
            <x-input-label for="otp" :value="__('OTP Code')" />
            <x-text-input
                id="otp"
                class="block mt-1 w-full text-center text-2xl tracking-[0.5em] font-black"
                type="text"
                name="otp"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <form method="POST" action="{{ route('otp.resend') }}">
                @csrf
                <button type="submit" class="text-sm font-bold text-brand-600 hover:text-brand-700 underline underline-offset-4 transition-colors">
                    {{ __('Resend OTP') }}
                </button>
            </form>

            <x-primary-button class="ms-3">
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
