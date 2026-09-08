<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @php($maintenanceMode = app(\App\Services\MaintenanceMode::class))
            @php($maintenanceActive = $maintenanceMode->active())
            @php($maintenanceRemaining = $maintenanceMode->remainingSeconds())
            @php($maintenanceEndsAt = $maintenanceMode->endsAt())

            <!-- Maintenance Control -->
            <section class="mb-8 border-2 border-black {{ $maintenanceActive ? 'bg-amber-200' : 'bg-white' }} shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 shrink-0 border-2 border-black {{ $maintenanceActive ? 'bg-brand-500' : 'bg-emerald-400' }} flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.3 2.7a1 1 0 011.4 0l1.6 1.6a1 1 0 001 .25l2.2-.6a1 1 0 011.22.7l.6 2.2a1 1 0 00.73.73l2.2.6a1 1 0 01.7 1.22l-.6 2.2a1 1 0 00.25 1l1.6 1.6a1 1 0 010 1.4l-1.6 1.6a1 1 0 00-.25 1l.6 2.2a1 1 0 01-.7 1.22l-2.2.6a1 1 0 00-.73.73l-.6 2.2a1 1 0 01-1.22.7l-2.2-.6a1 1 0 00-1 .25l-1.6 1.6a1 1 0 01-1.4 0l-1.6-1.6a1 1 0 00-1-.25l-2.2.6a1 1 0 01-1.22-.7l-.6-2.2a1 1 0 00-.73-.73l-2.2-.6a1 1 0 01-.7-1.22l.6-2.2a1 1 0 00-.25-1L.7 15.7a1 1 0 010-1.4l1.6-1.6a1 1 0 00.25-1l-.6-2.2a1 1 0 01.7-1.22l2.2-.6a1 1 0 00.73-.73l.6-2.2a1 1 0 011.22-.7l2.2.6a1 1 0 001-.25l1.6-1.6z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-xl font-black">Site Maintenance</h3>
                                <span class="px-3 py-1 border-2 border-black text-xs font-black uppercase tracking-wider {{ $maintenanceActive ? 'bg-brand-500' : 'bg-emerald-400' }}">
                                    {{ $maintenanceActive ? 'Maintenance ON' : 'Site is Live' }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-black/60 max-w-2xl">
                                @if($maintenanceActive)
                                    Public users are seeing the maintenance screen. Your authenticated Admin Panel remains available.
                                @else
                                    Turn this on to show the animated maintenance screen to all public users while admins continue working.
                                @endif
                            </p>
                            @if($maintenanceActive && $maintenanceEndsAt)
                                <p class="mt-3 text-sm font-black" id="admin-maintenance-countdown"
                                   data-seconds="{{ $maintenanceRemaining }}">
                                    Ends in: calculating…
                                </p>
                            @elseif($maintenanceActive)
                                <p class="mt-3 text-sm font-black">No automatic end time is set. Turn maintenance off, then enable it with a duration.</p>
                            @endif
                        </div>
                    </div>

                    @if($maintenanceActive)
                        <form method="POST" action="{{ route('admin.maintenance.disable') }}">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Make the public site live again?')"
                                    class="w-full lg:w-auto px-6 py-3 bg-emerald-400 border-2 border-black font-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                                Turn Maintenance OFF
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.maintenance.enable') }}" class="w-full lg:w-auto">
                            @csrf
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <label class="text-xs font-black uppercase">
                                    Days
                                    <input type="number" name="days" value="{{ old('days', 0) }}" min="0" max="30" required
                                           class="mt-1 w-full border-2 border-black px-2 py-2 font-black">
                                </label>
                                <label class="text-xs font-black uppercase">
                                    Hours
                                    <input type="number" name="hours" value="{{ old('hours', 1) }}" min="0" max="23" required
                                           class="mt-1 w-full border-2 border-black px-2 py-2 font-black">
                                </label>
                                <label class="text-xs font-black uppercase">
                                    Minutes
                                    <input type="number" name="minutes" value="{{ old('minutes', 0) }}" min="0" max="59" required
                                           class="mt-1 w-full border-2 border-black px-2 py-2 font-black">
                                </label>
                            </div>
                            @error('duration')<p class="mb-2 text-xs font-bold text-red-700">{{ $message }}</p>@enderror
                            @if($errors->hasAny(['days', 'hours', 'minutes']))
                                <p class="mb-2 text-xs font-bold text-red-700">Enter a valid duration using the fields above.</p>
                            @endif
                            <button type="submit"
                                    onclick="return confirm('Enable maintenance mode for all public users?')"
                                    class="w-full lg:w-auto px-6 py-3 bg-brand-500 border-2 border-black font-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                                Turn Maintenance ON
                            </button>
                        </form>
                    @endif
                </div>
            </section>

            @if($maintenanceActive && $maintenanceEndsAt)
                <script>
                    (() => {
                        const element = document.getElementById('admin-maintenance-countdown');
                        let seconds = Number(element.dataset.seconds || 0);
                        const pad = value => String(value).padStart(2, '0');
                        const tick = () => {
                            const days = Math.floor(seconds / 86400);
                            const hours = Math.floor((seconds % 86400) / 3600);
                            const minutes = Math.floor((seconds % 3600) / 60);
                            const secs = seconds % 60;
                            element.textContent = `Ends in: ${days}d ${pad(hours)}h ${pad(minutes)}m ${pad(secs)}s`;
                            if (seconds <= 0) { window.location.reload(); return; }
                            seconds--;
                            setTimeout(tick, 1000);
                        };
                        tick();
                    })();
                </script>
            @endif

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-black/50 uppercase tracking-wider">Total Artists</div>
                            <div class="mt-1 text-3xl font-black text-black">{{ $totalArtists }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-black text-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(250,229,0,1)] p-6">
                    <div class="text-xs font-extrabold text-white/60 uppercase tracking-wider">TeleMusic Fee Revenue</div>
                    <div class="mt-1 text-3xl font-black">${{ number_format($totalTeleMusicFees, 2) }}</div>
                    <div class="mt-1 text-xs font-bold text-white/50">Artist shares: ${{ number_format($totalArtistShares, 2) }}</div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-black/50 uppercase tracking-wider">Total Albums</div>
                            <div class="mt-1 text-3xl font-black text-black">{{ $totalAlbums }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-black/50 uppercase tracking-wider">Total Songs</div>
                            <div class="mt-1 text-3xl font-black text-black">{{ $totalSongs }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-black/50 uppercase tracking-wider">Total Royalties</div>
                            <div class="mt-1 text-3xl font-black text-black">${{ number_format($totalRoyalties, 2) }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-black/50 uppercase tracking-wider">Total Paid Out</div>
                            <div class="mt-1 text-3xl font-black text-black">${{ number_format($totalPayouts, 2) }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-black/50 uppercase tracking-wider">Pending Withdrawals</div>
                            <div class="mt-1 text-3xl font-black text-black">${{ number_format($pendingWithdrawals, 2) }}</div>
                        </div>
                        <div class="w-12 h-12 bg-brand-500 border-2 border-black flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Artists -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-5 border-b-2 border-black">
                        <h3 class="text-lg font-extrabold text-black tracking-tight">Recent Artists</h3>
                    </div>
                    <div class="p-5">
                        @forelse($recentArtists as $artist)
                            <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0 hover:bg-brand-500/10 px-2 -mx-2 transition-colors">
                                <div>
                                    <div class="font-bold text-black">{{ $artist->artist_name }}</div>
                                    <div class="text-xs font-semibold text-black/50">{{ $artist->user->email ?? 'N/A' }}</div>
                                </div>
                                <span class="text-xs font-bold border border-black px-2 py-1 {{ $artist->albums_count > 0 ? 'bg-brand-500' : 'bg-gray-100' }}">
                                    {{ $artist->albums_count }} albums
                                </span>
                            </div>
                        @empty
                            <p class="text-sm font-bold text-black/40 text-center py-4">No artists yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Distributions -->
                <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="p-5 border-b-2 border-black">
                        <h3 class="text-lg font-extrabold text-black tracking-tight">Recent Distributions</h3>
                    </div>
                    <div class="p-5">
                        @forelse($recentDistributions as $dist)
                            <div class="flex items-center justify-between py-3 border-b border-black/10 last:border-0 hover:bg-brand-500/10 px-2 -mx-2 transition-colors">
                                <div>
                                    <div class="font-bold text-black">{{ $dist->song->title ?? 'N/A' }}</div>
                                    <div class="text-xs font-semibold text-black/50">{{ $dist->store->name ?? 'N/A' }}</div>
                                </div>
                                <span class="text-xs font-bold border border-black px-2 py-1
                                    @if($dist->status == 'pending') bg-gray-100
                                    @elseif($dist->status == 'submitted') bg-amber-200
                                    @elseif($dist->status == 'approved') bg-blue-200
                                    @elseif($dist->status == 'live') bg-emerald-400
                                    @else bg-red-200
                                    @endif">{{ ucfirst($dist->status) }}</span>
                            </div>
                        @empty
                            <p class="text-sm font-bold text-black/40 text-center py-4">No distributions yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
