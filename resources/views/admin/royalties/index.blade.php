<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">{{ __('Royalty Management') }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success')) <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6">{{ session('success') }}</div> @endif
            @if($errors->any())
                <div class="bg-red-100 border-2 border-black text-black font-bold px-4 py-3 mb-6">
                    <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-yellow-100 border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mb-6">
                <div class="p-5 border-b-2 border-black flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-black tracking-tight">Import Royalties from CSV</h3>
                        <p class="text-sm font-bold text-black/60 mt-1">ISRC and Amount are required. Songs, albums and artists are matched automatically by ISRC.</p>
                    </div>
                    <a href="{{ route('admin.royalties.import-template') }}" class="px-4 py-2 bg-white border-2 border-black font-extrabold text-xs uppercase shadow-[3px_3px_0_#000]">Download CSV Template</a>
                </div>
                <form method="POST" action="{{ route('admin.royalties.import') }}" enctype="multipart/form-data" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    @csrf
                    <div class="lg:col-span-2">
                        <x-input-label for="csv_file" value="CSV File" />
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt,text/csv" required class="block mt-1 w-full bg-white border-2 border-black p-2 font-bold text-sm">
                    </div>
                    <div>
                        <x-input-label for="import_store_id" value="Default Store" />
                        <select id="import_store_id" name="store_id" required class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold rounded-none">
                            @foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="import_month" value="Default Period" />
                        <div class="flex mt-1">
                            <select id="import_month" name="month" class="w-1/2 border-2 border-r-0 border-black text-sm font-bold">
                                @for($m=1;$m<=12;$m++)<option value="{{ $m }}" @selected($m == date('n'))>{{ $m }}</option>@endfor
                            </select>
                            <input name="year" type="number" value="{{ date('Y') }}" min="2020" max="{{ date('Y') + 1 }}" class="w-1/2 border-2 border-black text-sm font-bold">
                        </div>
                    </div>
                    <div>
                        <x-input-label for="import_royalty_type" value="Income Type" />
                        <select id="import_royalty_type" name="royalty_type" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold rounded-none">
                            @foreach(\App\Models\Royalty::TYPES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                        <input type="hidden" name="currency" value="USD">
                    </div>
                    <button class="px-5 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[3px_3px_0_#000]">Import CSV</button>
                </form>
                <div class="px-6 pb-5 text-xs font-bold text-black/60">Optional CSV columns: Streams, Store, Month, Year, Currency, Notes. Row values override the defaults selected above. Maximum 50,000 rows / 20 MB.</div>
            </div>

            <!-- Add Royalty Form -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mb-6">
                <div class="p-5 border-b-2 border-black">
                    <h3 class="text-lg font-extrabold text-black tracking-tight">Add Manual Royalty Entry</h3>
                    <p class="text-sm font-bold text-black/50 mt-1">Enter royalty amounts per store per month for artists</p>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.royalties.store') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <x-input-label for="artist_id" value="Artist" />
                            <select id="artist_id" name="artist_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="">Select Artist</option>
                                @foreach($artists as $a)
                                <option value="{{ $a->id }}">{{ $a->artist_name }}</option>
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
                            <x-input-label for="royalty_type" value="Income Type" />
                            <select id="royalty_type" name="royalty_type" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 rounded-none" required>
                                @foreach(\App\Models\Royalty::TYPES as $value => $label)
                                <option value="{{ $value }}" {{ old('royalty_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="month" value="Month" />
                            <select id="month" name="month" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="year" value="Year" />
                            <select id="year" name="year" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="amount" value="Amount (USD)" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" min="0" name="amount" required />
                        </div>
                        <div>
                            <x-input-label for="currency" value="Currency" />
                            <select id="currency" name="currency" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="JPY">JPY</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="streams" value="Streams" />
                            <x-text-input id="streams" class="block mt-1 w-full" type="number" min="0" name="streams" />
                        </div>
                        <div>
                            <x-input-label for="notes" value="Notes" />
                            <x-text-input id="notes" class="block mt-1 w-full" type="text" name="notes" />
                        </div>
                        <div class="md:col-span-3 lg:col-span-4 flex justify-end">
                            <x-primary-button class="bg-brand-500">{{ __('Add Royalty Entry') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Royalty List -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-5 border-b-2 border-black">
                    <h3 class="text-lg font-extrabold text-black tracking-tight">Royalty History</h3>
                </div>
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Store</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Type</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Period</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Total Revenue</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Artist Share</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">TeleMusic Fee</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Streams</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($royalties as $royalty)
                            @php
                                $artistShare = $royalty->artist ? $royalty->artist->getArtistShareAttribute($royalty->amount) : $royalty->amount;
                                $teleMusicPct = $royalty->artist ? $royalty->artist->tele_music_fee_percentage : 30;
                                $teleMusicFee = $royalty->artist ? $royalty->artist->getTeleMusicFeeAttribute($royalty->amount) : 0;
                                $hasCollaborators = $royalty->relationLoaded('album') && $royalty->album && $royalty->album->relationLoaded('collaboratingArtists') && $royalty->album->collaboratingArtists->count() > 0;
                            @endphp
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2 font-bold text-black/80">
                                    {{ $royalty->artist->artist_name ?? 'N/A' }}
                                    @if($hasCollaborators)
                                        <span class="ml-1 px-1.5 py-0.5 bg-purple-100 border border-black text-[9px] font-extrabold uppercase" title="Has collaborators">+Collab</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <x-store-logo :store="$royalty->store" size="5" />
                                        <span class="font-semibold text-black/80">{{ $royalty->store->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2 font-bold text-black/70">{{ $royalty->royalty_type_label }}</td>
                                <td class="py-3 px-2 font-bold text-black/60">{{ $royalty->month }}/{{ $royalty->year }}</td>
                                <td class="py-3 px-2 text-right font-black text-black">${{ number_format($royalty->amount, 2) }}</td>
                                <td class="py-3 px-2 text-right font-black text-emerald-600">${{ number_format($artistShare, 2) }}</td>
                                <td class="py-3 px-2 text-center font-bold text-black/60">{{ $teleMusicPct }}%<br><span class="text-xs text-black/40">${{ number_format($teleMusicFee, 2) }}</span></td>
                                <td class="py-3 px-2 text-center font-bold text-black/60">{{ number_format($royalty->streams) ?? '-' }}</td>
                                <td class="py-3 px-2 text-right">
                                    <a href="{{ route('admin.royalties.edit', $royalty) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">Edit</a>
                                </td>
                            </tr>
                            @if($hasCollaborators)
                                <tr class="bg-purple-50 border-b border-black/10">
                                    <td colspan="9" class="py-2 px-6 text-xs font-bold text-black/70">
                                        <span class="font-extrabold uppercase text-[10px]">Collaborator Revenue Split:</span>
                                        @foreach($royalty->album->collaboratingArtists as $collab)
                                            <span class="ml-3 inline-flex items-center gap-1">
                                                {{ $collab->artist_name }} ({{ $collab->pivot->share_percentage }}%)
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            @empty
                            <tr><td colspan="9" class="py-8 text-center font-bold text-black/40">No royalties entered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $royalties->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
