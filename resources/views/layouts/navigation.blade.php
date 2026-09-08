<nav x-data="{ open: false, catalogOpen: false }" class="w-64 bg-white border-r-2 border-black flex-shrink-0 min-h-screen">
    <!-- Logo -->
    <div class="p-5 border-b-2 border-black">
        <a href="{{ Auth::user()?->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="flex items-center justify-center group">
            <div class="w-10 h-10 bg-brand-500 border-2 border-black flex items-center justify-center shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] group-hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] group-hover:translate-x-[2px] group-hover:translate-y-[2px] transition-all">
                <img src="{{ asset('logo.png') }}" alt="TeleMusic" class="w-full h-full object-cover">
            </div>
        </a>
    </div>

    @auth
    <!-- Navigation Links -->
    <div class="p-4 space-y-1">
        @if(Auth::user()->isAdmin())
        <!-- Admin Section -->
        <div class="mb-2">
            <span class="text-[11px] font-extrabold text-black/40 uppercase tracking-wider px-3">Admin Panel</span>
        </div>
        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            {{ __('Dashboard') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.artists')" :active="request()->routeIs('admin.artists*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            {{ __('Artists') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.releases')" :active="request()->routeIs('admin.releases*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
            {{ __('Releases') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.albums')" :active="request()->routeIs('admin.albums*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
            {{ __('Albums') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.songs')" :active="request()->routeIs('admin.songs*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
            {{ __('Songs') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.stores')" :active="request()->routeIs('admin.stores*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            {{ __('Stores') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.distributions')" :active="request()->routeIs('admin.distributions*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            {{ __('Distribution') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.royalties')" :active="request()->routeIs('admin.royalties*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('Royalties') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.withdrawals')" :active="request()->routeIs('admin.withdrawals*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            {{ __('Withdrawals') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.payouts')" :active="request()->routeIs('admin.payouts*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ __('Payouts') }}
        </x-nav-link>
        <x-nav-link :href="route('admin.analytics')" :active="request()->routeIs('admin.analytics*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            {{ __('Analytics') }}
        </x-nav-link>
        @else
        <!-- Artist Section -->
        <div class="mb-2">
            <span class="text-[11px] font-extrabold text-black/40 uppercase tracking-wider px-3">Artist Panel</span>
        </div>
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            {{ __('Dashboard') }}
        </x-nav-link>

        <!-- Catalog (replaces Albums, Songs, Distribution) -->
        <div x-data="{ open: {{ request()->routeIs('artist.catalog*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm font-bold text-black hover:bg-brand-500 border-2 border-transparent hover:border-black transition-all rounded-none">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                    {{ __('Catalog') }}
                </div>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" class="ml-6 mt-1 space-y-1">
                <x-nav-link :href="route('artist.catalog.index')" :active="request()->routeIs('artist.catalog.index')" class="!text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0l-4-4m4 4l-4 4"/></svg>
                    {{ __('All Releases') }}
                </x-nav-link>
                <x-nav-link :href="route('artist.catalog.create')" :active="request()->routeIs('artist.catalog.create')" class="!text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Release') }}
                </x-nav-link>
            </div>
        </div>

        <x-nav-link :href="route('artist.royalties')" :active="request()->routeIs('artist.royalties*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('Royalties') }}
        </x-nav-link>
        <x-nav-link :href="route('artist.analytics')" :active="request()->routeIs('artist.analytics*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            {{ __('Analytics') }}
        </x-nav-link>
        @endif
    </div>
    @endauth

    <!-- User Menu at Bottom -->
    @auth
    <div class="border-t-2 border-black p-4 mt-auto">
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-9 h-9 bg-brand-500 border-2 border-black flex items-center justify-center font-extrabold text-sm">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-sm text-black truncate">{{ Auth::user()->name }}</div>
                <div class="text-[11px] font-semibold text-black/50 uppercase">{{ Auth::user()->role }}</div>
            </div>
        </div>
        <div class="mt-2 space-y-1">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-black hover:bg-brand-500 border-2 border-transparent hover:border-black transition-all rounded-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-bold text-black hover:bg-red-100 border-2 border-transparent hover:border-black transition-all rounded-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
    @endauth
</nav>
