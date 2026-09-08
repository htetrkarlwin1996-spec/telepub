<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('artist.catalog.index') }}" class="inline-flex items-center gap-1 text-sm font-extrabold text-black/60 hover:text-black">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            My Releases
                        </a>
                        <span class="text-black/30 font-extrabold">/</span>
                        <h1 class="text-3xl font-black text-black uppercase">Collaborations</h1>
                    </div>
                    <p class="text-sm font-bold text-black/60 mt-1">Releases where you are a collaborating artist</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Releases Grid -->
            @if($albums->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($albums as $album)
                        <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] overflow-hidden hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                            <!-- Cover Art -->
                            <div class="aspect-square bg-gray-100 border-b-2 border-black overflow-hidden">
                                @if($album->cover_art)
                                    <img src="{{ Storage::url($album->cover_art) }}" alt="{{ $album->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-brand-100">
                                        <svg class="w-16 h-16 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h3 class="font-extrabold text-lg text-black leading-tight">{{ $album->title }}</h3>
                                        <p class="text-xs font-bold text-black/50 uppercase mt-1">
                                            By {{ $album->artist->artist_name ?? 'Unknown' }}
                                        </p>
                                        <p class="text-xs font-bold text-black/30 uppercase mt-0.5">
                                            {{ ucfirst($album->release_type) }} · {{ $album->genre }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-[10px] font-extrabold uppercase border-2 border-black
                                        @if($album->status === 'approved') bg-green-200
                                        @elseif($album->status === 'rejected') bg-red-200
                                        @elseif($album->status === 'submitted') bg-yellow-200
                                        @else bg-gray-200 @endif">
                                        {{ $album->status }}
                                    </span>
                                </div>

                                <div class="text-xs font-bold text-black/60 space-y-1 mb-4">
                                    <div>{{ $album->songs->count() }} track(s) · {{ $album->release_date ? $album->release_date->format('M d, Y') : 'TBA' }}</div>
                                    @if($album->price)
                                        <div>${{ number_format($album->price, 2) }}</div>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('artist.catalog.show', $album) }}" class="flex-1 text-center px-3 py-2 bg-black text-white font-extrabold text-xs uppercase border-2 border-black hover:bg-brand-500 hover:text-black transition-all">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $albums->links() }}
                </div>
            @else
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 mx-auto text-black/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        <h3 class="text-xl font-black text-black mb-2">No Collaborations Yet</h3>
                        <p class="text-sm font-bold text-black/50 mb-6">When another artist adds you as a collaborator on their release, it will appear here.</p>
                        <a href="{{ route('artist.catalog.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                            Back to My Releases
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
