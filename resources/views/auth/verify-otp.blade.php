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

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="ms-3">
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-5 pt-5 border-t-2 border-black flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('otp.resend') }}">
            @csrf
            <button type="submit" class="text-sm font-bold text-brand-600 hover:text-brand-700 underline underline-offset-4 transition-colors">
                {{ __('Resend OTP') }}
            </button>
        </form>

        <div class="flex items-center gap-4">
            @if(auth()->check() && session()->has('impersonator_admin_id'))
                <form method="POST" action="{{ route('impersonation.stop') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0_#000]">
                        {{ __('Return to Admin') }}
                    </button>
                </form>
            @endif

            @if(auth()->check())
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-extrabold underline decoration-red-500 decoration-2 underline-offset-2">
                        {{ __('Log Out') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-guest-layout>
