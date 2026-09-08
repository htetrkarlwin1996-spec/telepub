<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-black tracking-tight">Verify Email</h2>
        <p class="text-sm font-bold text-black/50 mt-1">Verify your email address to continue</p>
    </div>

    <div class="mb-6 p-4 bg-brand-500 border-2 border-black font-bold text-sm text-black">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 bg-emerald-400 border-2 border-black font-bold text-sm text-black">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-extrabold text-black underline decoration-red-500 decoration-2 underline-offset-2 hover:decoration-black transition-all">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
