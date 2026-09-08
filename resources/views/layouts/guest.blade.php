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
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Brand Logo Area -->
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center justify-center group">
                    <div class="w-28 h-28 bg-brand-500 border-4 border-black flex items-center justify-center shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] group-hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] group-hover:translate-x-[4px] group-hover:translate-y-[4px] transition-all">
                        <img src="{{ asset('logo.png') }}" alt="TeleMusic" class="w-full h-full object-cover">
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-8">
                {{ $slot }}
            </div>

            <p class="mt-8 text-center text-xs font-bold text-black/40 uppercase tracking-wider">
                &copy; {{ date('Y') }} {{ config('app.name', 'TeleMusic') }}. All rights reserved.
            </p>
        </div>
    </body>
</html>
