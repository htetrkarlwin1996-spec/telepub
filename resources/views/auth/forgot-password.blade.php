<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-black tracking-tight">Reset Password</h2>
        <p class="text-sm font-bold text-black/50 mt-1">Forgot your password? No problem.</p>
    </div>

    <div class="mb-6 p-4 bg-brand-500 border-2 border-black font-bold text-sm text-black">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="your@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-5 pt-4 border-t-2 border-black">
            <a href="{{ route('login') }}" class="text-sm font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black transition-all">{{ __('Back to Login') }}</a>
        </div>
    </form>
</x-guest-layout>
