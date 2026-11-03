<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TeleMusic') }} - Music Distribution</title>

        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-cream text-black">
        @php($loginAnnouncement = app(\App\Services\LoginAnnouncement::class)->forUser(Auth::user()))
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen flex overflow-x-hidden">
            <div
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-black/50 md:hidden"
                aria-hidden="true"
            ></div>

            <!-- Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b-2 border-black bg-white px-4 md:hidden">
                    <a href="{{ Auth::user()?->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="flex items-center gap-3 font-black">
                        <span class="flex h-10 w-10 items-center justify-center border-2 border-black bg-brand-500 shadow-[2px_2px_0_#000]">
                            <img src="{{ asset('logo.png') }}" alt="TeleMusic" class="h-full w-full object-cover">
                        </span>
                        <span>TeleMusic</span>
                    </a>
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="flex h-10 w-10 items-center justify-center border-2 border-black bg-brand-500 shadow-[2px_2px_0_#000]"
                        aria-label="Open navigation"
                        :aria-expanded="sidebarOpen"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                @if(session()->has('impersonator_admin_id'))
                    <div class="bg-amber-300 border-b-2 border-black px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="font-extrabold text-sm">
                            @if(session()->has('impersonated_master_account_id'))
                                Viewing as Master Account: {{ Auth::user()->masterAccount?->name ?? Auth::user()->name }}
                            @else
                                Viewing as Artist: {{ current_artist()?->artist_name ?? Auth::user()->name }}
                            @endif
                        </div>
                        <form method="POST" action="{{ route('impersonation.stop') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-black text-white border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0_#fff]">Return to Admin</button>
                        </form>
                    </div>
                @endif
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b-2 border-black">
                        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-8 sm:py-5 lg:px-10">
                            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                                <div class="w-2 h-9 bg-brand-500 border-2 border-black"></div>
                                <div class="min-w-0 flex-1 font-extrabold text-xl text-black leading-tight tracking-tight sm:text-2xl">{{ $header }}</div>
                            </div>
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="app-main min-w-0 flex-1 px-4 py-5 sm:px-8 sm:py-8 lg:px-10">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="border-t-2 border-black bg-white mt-auto">
                    <div class="max-w-7xl mx-auto py-4 px-6 sm:px-8 lg:px-10">
                        <p class="text-center text-sm font-bold text-black">
                            &copy; {{ date('Y') }} {{ config('app.name', 'TeleMusic') }}. All rights reserved.
                        </p>
                    </div>
                </footer>
            </div>
        </div>
        @if($loginAnnouncement)
            @include('components.login-announcement', ['announcement' => $loginAnnouncement])
        @endif
    </body>
</html>
