<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">{{ __('Distribution Management') }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success')) <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6">{{ session('success') }}</div> @endif

            <!-- Add Distribution Form -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mb-6">
                <div class="p-5 border-b-2 border-black">
                    <h3 class="text-lg font-extrabold text-black tracking-tight">Submit Distribution</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.distributions.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <x-input-label for="song_id" value="Song" />
                            <select id="song_id" name="song_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Song</option>
                                @foreach($songs as $song)
                                <option value="{{ $song->id }}">{{ $song->title }} - {{ $song->artist->artist_name ?? 'Unknown' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="store_id" value="Store" />
                            <select id="store_id" name="store_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Store</option>
                                @foreach($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach($stores as $s)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 border border-black text-[10px] font-bold text-black/60">
                                    <x-store-logo :store="$s" size="3" />
                                    {{ $s->name }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <x-input-label for="distribution_fee" value="Fee ($)" />
                            <x-text-input id="distribution_fee" class="block mt-1 w-full" type="number" step="0.01" name="distribution_fee" value="0" />
                        </div>
                        <div class="flex items-end">
                            <x-primary-button class="bg-brand-500">{{ __('Submit Distribution') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Distribution List -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Song</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Store</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">Submitted</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributions as $dist)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black">{{ $dist->song->title ?? 'N/A' }}</td>
                                <td class="py-3 px-2 font-semibold text-black/80">{{ $dist->artist->artist_name ?? 'N/A' }}</td>
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <x-store-logo :store="$dist->store" size="5" />
                                        <span class="font-semibold text-black/80">{{ $dist->store->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1
                                        @if($dist->status == 'pending') bg-gray-100
                                        @elseif($dist->status == 'submitted') bg-amber-200
                                        @elseif($dist->status == 'approved') bg-blue-200
                                        @elseif($dist->status == 'live') bg-emerald-400
                                        @else bg-red-200
                                        @endif">{{ ucfirst($dist->status) }}</span>
                                </td>
                                <td class="py-3 px-2 text-xs font-bold text-black/50 hidden md:table-cell">{{ $dist->submitted_at ? $dist->submitted_at->format('Y-m-d') : '-' }}</td>
                                <td class="py-3 px-2 text-right font-bold text-black">${{ number_format($dist->distribution_fee, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-8 text-center font-bold text-black/40">No distributions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $distributions->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
