<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Step Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-green-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">✓</div>
                        <span class="text-xs font-extrabold uppercase text-green-700">Release Info</span>
                    </div>
                    <div class="w-12 h-0.5 bg-green-400"></div>
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-green-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">✓</div>
                        <span class="text-xs font-extrabold uppercase text-green-700">Tracks</span>
                    </div>
                    <div class="w-12 h-0.5 bg-green-400"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-500 border-2 border-black flex items-center justify-center font-extrabold text-sm">3</div>
                        <span class="text-xs font-extrabold uppercase">Pricing</span>
                    </div>
                    <div class="w-12 h-0.5 bg-black/30"></div>
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-gray-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">4</div>
                        <span class="text-xs font-extrabold uppercase">Stores</span>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-black text-center">Step 3: Pricing & Release Dates</h1>
                <p class="text-sm font-bold text-black/60 text-center mt-1">Release: <span class="text-brand-600">{{ $album->title }}</span></p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-2 border-black font-bold text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('artist.catalog.store-step3', $album) }}" class="space-y-6">
                @csrf

                <!-- Album Release Date -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Digital Release Date <span class="text-red-500">*</span></label>
                    <p class="text-xs font-bold text-black/50 mb-2">The date your music will be available on streaming platforms.</p>
                    <input type="date" name="release_date" value="{{ old('release_date', $album->release_date?->format('Y-m-d')) }}" required
                        class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                </div>

                <!-- Physical Release Date -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Physical Release Date</label>
                    <p class="text-xs font-bold text-black/50 mb-2">Optional. For physical formats like CD, vinyl, etc.</p>
                    <input type="date" name="physical_release_date" value="{{ old('physical_release_date', $album->physical_release_date?->format('Y-m-d')) }}"
                        class="w-full px-4 py-3 border-2 border-black font-bold text-sm focus:outline-none focus:ring-0 focus:border-brand-500">
                </div>

                <!-- Price -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Album Price (USD) <span class="text-red-500">*</span></label>
                    <p class="text-xs font-bold text-black/50 mb-2">Set the price for iTunes, Amazon Music, and other stores. Max $999.99.</p>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-extrabold text-lg">$</span>
                        <input type="number" name="price" value="{{ old('price', $album->price) }}" required
                            step="0.01" min="0" max="999.99"
                            class="w-full pl-10 pr-4 py-3 border-2 border-black font-bold text-lg focus:outline-none focus:ring-0 focus:border-brand-500"
                            placeholder="9.99">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('artist.catalog.step2', $album) }}" class="px-6 py-3 bg-gray-200 border-2 border-black font-extrabold text-sm uppercase hover:bg-gray-300 transition-all">
                        ← Back to Tracks
                    </a>
                    <button type="submit" class="px-8 py-3 bg-brand-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        Next: Select Stores →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
