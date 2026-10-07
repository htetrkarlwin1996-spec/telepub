<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-black tracking-tight">Welcome Back</h2>
        <p class="text-sm font-bold text-black/50 mt-1">Sign in to your MusicDistro account</p>
    </div>

    <a href="{{ route('auth.google.redirect') }}" class="mb-6 flex w-full items-center justify-center gap-3 border-2 border-black bg-white px-4 py-3 font-black shadow-[3px_3px_0_#000] transition hover:bg-gray-50">
        <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5"><path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.91h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.4Z"/><path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.62-2.43l-3.24-2.54c-.9.6-2.05.96-3.38.96-2.61 0-4.82-1.76-5.61-4.13H3.05v2.62A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.39 13.86A6.01 6.01 0 0 1 6.08 12c0-.65.11-1.28.31-1.86V7.52H3.05A10 10 0 0 0 2 12c0 1.61.39 3.14 1.05 4.48l3.34-2.62Z"/><path fill="#EA4335" d="M12 6.01c1.47 0 2.79.51 3.83 1.5l2.87-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.95 5.52l3.34 2.62C7.18 7.77 9.39 6.01 12 6.01Z"/></svg>
        Continue with Google
    </a>
    <div class="mb-6 flex items-center gap-3"><span class="h-0.5 flex-1 bg-black"></span><span class="text-xs font-black uppercase text-black/50">or use email</span><span class="h-0.5 flex-1 bg-black"></span></div>

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
