<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-black tracking-tight">Welcome Back</h2>
        <p class="text-sm font-bold text-black/50 mt-1">Sign in to your MusicDistro account</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-5">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 border-2 border-black rounded-none text-brand-500 focus:ring-0 focus:ring-offset-0" name="remember">
                <span class="text-sm font-bold text-black">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black transition-all" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-8 pt-5 border-t-2 border-black">
            <p class="text-sm font-bold text-black">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black transition-all">{{ __('Register') }}</a>
            </p>
        </div>
    </form>
</x-guest-layout>
