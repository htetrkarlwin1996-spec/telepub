<x-app-layout>
    <div class="min-h-screen bg-[#FFF8E7]">
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
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
                    <div class="flex items-center gap-2 text-black/40">
                        <div class="w-8 h-8 bg-green-200 border-2 border-black flex items-center justify-center font-extrabold text-sm">✓</div>
                        <span class="text-xs font-extrabold uppercase text-green-700">Pricing</span>
                    </div>
                    <div class="w-12 h-0.5 bg-green-400"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-500 border-2 border-black flex items-center justify-center font-extrabold text-sm">4</div>
                        <span class="text-xs font-extrabold uppercase">Stores</span>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-black text-center">Step 4: Select Stores</h1>
                <p class="text-sm font-bold text-black/60 text-center mt-1">Choose where to distribute: <span class="text-brand-600">{{ $album->title }}</span></p>
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

            <!-- Summary -->
            <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6 mb-6">
                <h2 class="font-extrabold text-sm uppercase mb-3">Release Summary</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-black/50">Artist</span>
                        <p class="font-extrabold">{{ $album->artist->artist_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-black/50">Type</span>
                        <p class="font-extrabold">{{ ucfirst($album->release_type) }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-black/50">Genre</span>
                        <p class="font-extrabold">{{ $album->genre }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-black/50">Tracks</span>
                        <p class="font-extrabold">{{ $album->songs->count() }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-black/50">Price</span>
                        <p class="font-extrabold">${{ number_format($album->price, 2) }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.releases.store-step4', $album) }}">
                @csrf

                <!-- Store Selection -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6">
                    <label class="block font-extrabold text-sm uppercase mb-4">Select Stores <span class="text-red-500">*</span></label>
                    <p class="text-xs font-bold text-black/50 mb-4">Choose the stores where this release should be distributed.</p>

                    @php
                        $existingStoreIds = $album->distributions->pluck('store_id')->unique()->toArray();
                        $oldStores = old('stores', $existingStoreIds);
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($stores as $store)
                            <label class="flex items-center gap-3 p-3 border-2 border-black cursor-pointer hover:bg-brand-100 transition-all
                                {{ in_array($store->id, $oldStores) ? 'bg-brand-200' : '' }}">
                                <input type="checkbox" name="stores[]" value="{{ $store->id }}"
                                    {{ in_array($store->id, $oldStores) ? 'checked' : '' }}
                                    class="w-5 h-5 border-2 border-black rounded-none focus:ring-0 focus:ring-offset-0 flex-shrink-0">
                                <x-store-logo :store="$store" size="8" />
                                <span class="font-bold text-sm">{{ $store->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('stores')
                        <p class="text-red-600 text-xs font-bold mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Admin Status Override -->
                <div class="bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] p-6 mt-6">
                    <label class="block font-extrabold text-sm uppercase mb-2">Release Status</label>
                    <p class="text-xs font-bold text-black/50 mb-3">As admin, you can directly set the final status.</p>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="submitted"
                                {{ old('status', 'submitted') === 'submitted' ? 'checked' : '' }}
                                class="w-5 h-5 border-2 border-black focus:ring-0 focus:ring-offset-0">
                            <span class="font-bold text-sm">Submit (pending approval)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="approved"
                                {{ old('status') === 'approved' ? 'checked' : '' }}
                                class="w-5 h-5 border-2 border-black focus:ring-0 focus:ring-offset-0">
                            <span class="font-bold text-sm">Approve Immediately</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="draft"
                                {{ old('status') === 'draft' ? 'checked' : '' }}
                                class="w-5 h-5 border-2 border-black focus:ring-0 focus:ring-offset-0">
                            <span class="font-bold text-sm">Save as Draft</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between mt-8">
                    <a href="{{ route('admin.releases.step3', $album) }}" class="px-6 py-3 bg-gray-200 border-2 border-black font-extrabold text-sm uppercase hover:bg-gray-300 transition-all">
                        ← Back to Pricing
                    </a>
                    <button type="submit" class="px-8 py-3 bg-green-500 border-2 border-black font-extrabold text-sm uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                        🚀 Complete Release
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
