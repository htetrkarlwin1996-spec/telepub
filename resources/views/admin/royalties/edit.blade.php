<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Edit Royalty Entry') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('admin.royalties.update', $royalty) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="artist_id" value="Artist" />
                            <select id="artist_id" name="artist_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @foreach($artists as $a)
                                <option value="{{ $a->id }}" {{ $royalty->artist_id == $a->id ? 'selected' : '' }}>{{ $a->artist_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="store_id" value="Store" />
                            <select id="store_id" name="store_id" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @foreach($stores as $s)
                                <option value="{{ $s->id }}" {{ $royalty->store_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="royalty_type" value="Income Type" />
                            <select id="royalty_type" name="royalty_type" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 rounded-none" required>
                                @foreach(\App\Models\Royalty::TYPES as $value => $label)
                                <option value="{{ $value }}" {{ $royalty->royalty_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="month" value="Month" />
                            <select id="month" name="month" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $royalty->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="year" value="Year" />
                            <select id="year" name="year" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" required>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $royalty->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="amount" value="Amount" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" value="{{ $royalty->amount }}" required />
                        </div>
                        <div>
                            <x-input-label for="streams" value="Streams" />
                            <x-text-input id="streams" class="block mt-1 w-full" type="number" name="streams" value="{{ $royalty->streams }}" />
                        </div>
                        <input type="hidden" name="currency" value="{{ $royalty->currency }}">
                        <div class="md:col-span-2">
                            <x-input-label for="notes" value="Notes" />
                            <x-text-input id="notes" class="block mt-1 w-full" type="text" name="notes" value="{{ $royalty->notes }}" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-primary-button>{{ __('Update Royalty') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
