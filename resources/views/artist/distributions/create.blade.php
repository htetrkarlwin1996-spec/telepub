<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Distribute Music') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <p class="text-sm font-bold text-black/60 mb-4">Select a song and store to distribute your music.</p>
                <form method="POST" action="{{ route('artist.distributions.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="song_id" value="Song *" />
                            <select id="song_id" name="song_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Song</option>
                                @foreach($songs as $song)
                                <option value="{{ $song->id }}">{{ $song->title }} ({{ $song->album->title ?? 'No album' }})</option>
                                @endforeach
                            </select>
                            @if($songs->isEmpty())
                            <p class="text-xs font-bold text-red-600 mt-1">No approved songs available. <a href="{{ route('artist.songs.create') }}" class="text-black underline decoration-brand-500 decoration-2">Upload a song first</a></p>
                            @endif
                        </div>
                        <div>
                            <x-input-label for="store_id" value="Store *" />
                            <select id="store_id" name="store_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Store</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach($stores as $store)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 border border-black text-[10px] font-bold text-black">
                                    <x-store-logo :store="$store" size="3" />
                                    {{ $store->name }}
                                </span>
                                @endforeach
                            </div>
                            @if($stores->isEmpty())
                            <p class="text-xs font-bold text-red-600 mt-1">No stores available. Contact admin.</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-primary-button>{{ __('Submit Distribution') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
