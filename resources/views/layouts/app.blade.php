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
        <div class="min-h-screen flex">
            <!-- Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col">
                @if(session()->has('impersonator_admin_id'))
                    <div class="bg-amber-300 border-b-2 border-black px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="font-extrabold text-sm">Viewing as Artist: {{ Auth::user()->artist->artist_name ?? Auth::user()->name }}</div>
                        <form method="POST" action="{{ route('impersonation.stop') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-black text-white border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0_#fff]">Return to Admin</button>
                        </form>
                    </div>
                @endif
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b-2 border-black">
                        <div class="max-w-7xl mx-auto py-5 px-6 sm:px-8 lg:px-10">
                            <div class="flex items-center gap-4">
                                <div class="w-2 h-9 bg-brand-500 border-2 border-black"></div>
                                <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">{{ $header }}</h2>
                            </div>
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 py-8 px-6 sm:px-8 lg:px-10">
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
    </body>
</html>
