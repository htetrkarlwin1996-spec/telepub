<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">
            {{ __('Artist Profile Setup') }}
        </h2>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <form method="POST" action="{{ route('artist.setup.update') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Artist Information -->
                        <div class="mb-6">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-4">Artist Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="artist_name" value="Artist Name" />
                                    <x-text-input id="artist_name" class="block mt-1 w-full" type="text" name="artist_name" value="{{ old('artist_name', $artist->artist_name ?? '') }}" required />
                                    <x-input-error :messages="$errors->get('artist_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="genre" value="Genre" />
                                    <select id="genre" name="genre" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                        <option value="">Select Genre</option>
                                        @foreach($genres as $genreOption)
                                            <option value="{{ $genreOption }}" {{ old('genre', $artist->genre ?? '') === $genreOption ? 'selected' : '' }}>{{ $genreOption }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('genre')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="bio" value="Bio" />
                                    <textarea id="bio" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="bio" rows="3">{{ old('bio', $artist->bio ?? '') }}</textarea>
                                    <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="country" value="Country" />
                                    <select id="country" name="country" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $countryOption)
                                            <option value="{{ $countryOption }}" {{ old('country', $artist->country ?? '') === $countryOption ? 'selected' : '' }}>{{ $countryOption }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('country')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Profile Links -->
                        <div class="mb-6">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-4">Profile Links</h3>
                            <p class="text-[10px] font-bold text-black/50 mb-3">Add your music platform profile links so they auto-fill when creating releases.</p>
                            <div class="border-2 border-black p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="setup_spotify_profile_url" value="Spotify Profile URL" />
                                        <x-text-input id="setup_spotify_profile_url" class="block mt-1 w-full" type="url" name="spotify_profile_url" value="{{ old('spotify_profile_url', $artist->spotify_profile_url ?? '') }}" placeholder="https://open.spotify.com/artist/..." />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_apple_music_profile_url" value="Apple Music Profile URL" />
                                        <x-text-input id="setup_apple_music_profile_url" class="block mt-1 w-full" type="url" name="apple_music_profile_url" value="{{ old('apple_music_profile_url', $artist->apple_music_profile_url ?? '') }}" placeholder="https://music.apple.com/artist/..." />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_youtube_profile_url" value="YouTube Profile URL" />
                                        <x-text-input id="setup_youtube_profile_url" class="block mt-1 w-full" type="url" name="youtube_profile_url" value="{{ old('youtube_profile_url', $artist->youtube_profile_url ?? '') }}" placeholder="https://youtube.com/@..." />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_tidal_profile_url" value="Tidal Profile URL" />
                                        <x-text-input id="setup_tidal_profile_url" class="block mt-1 w-full" type="url" name="tidal_profile_url" value="{{ old('tidal_profile_url', $artist->tidal_profile_url ?? '') }}" placeholder="https://tidal.com/artist/..." />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="mb-6">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-4">Payout Methods</h3>

                            <!-- PayPal -->
                            <div class="border-2 border-black p-4 mb-4">
                                <h4 class="font-bold text-black mb-2">PayPal</h4>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-input-label for="setup_paypal_email" value="PayPal Email" />
                                        <x-text-input id="setup_paypal_email" class="block mt-1 w-full" type="email" name="paypal_email" value="{{ old('paypal_email', $artist->paypal_email ?? '') }}" placeholder="artist@example.com" />
                                        <x-input-error :messages="$errors->get('paypal_email')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Transfer -->
                            <div class="border-2 border-black p-4 mb-4">
                                <h4 class="font-bold text-black mb-2">Bank Transfer</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="setup_bank_account_name" value="Account Name" />
                                        <x-text-input id="setup_bank_account_name" class="block mt-1 w-full" type="text" name="bank_account_name" value="{{ old('bank_account_name', $artist->bank_account_name ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_bank_name" value="Bank Name" />
                                        <x-text-input id="setup_bank_name" class="block mt-1 w-full" type="text" name="bank_name" value="{{ old('bank_name', $artist->bank_name ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_bank_country" value="Bank Country" />
                                        <select id="setup_bank_country" name="bank_country" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                            <option value="">Select Country</option>
                                            @foreach($countries as $countryOption)
                                                <option value="{{ $countryOption }}" {{ old('bank_country', $artist->bank_country ?? '') === $countryOption ? 'selected' : '' }}>{{ $countryOption }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="setup_bank_account_no" value="Account Number" />
                                        <x-text-input id="setup_bank_account_no" class="block mt-1 w-full" type="text" name="bank_account_no" value="{{ old('bank_account_no', $artist->bank_account_no ?? '') }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- KBZ Pay -->
                            <div class="border-2 border-black p-4 mb-4">
                                <h4 class="font-bold text-black mb-2">KBZ Pay</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="setup_kbz_pay_name" value="Name" />
                                        <x-text-input id="setup_kbz_pay_name" class="block mt-1 w-full" type="text" name="kbz_pay_name" value="{{ old('kbz_pay_name', $artist->kbz_pay_name ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_kbz_pay_phone" value="Phone Number" />
                                        <x-text-input id="setup_kbz_pay_phone" class="block mt-1 w-full" type="text" name="kbz_pay_phone" value="{{ old('kbz_pay_phone', $artist->kbz_pay_phone ?? '') }}" placeholder="09xxxxxxxxx" />
                                    </div>
                                </div>
                            </div>

                            <!-- Wave Pay -->
                            <div class="border-2 border-black p-4">
                                <h4 class="font-bold text-black mb-2">Wave Pay</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="setup_wave_pay_name" value="Name" />
                                        <x-text-input id="setup_wave_pay_name" class="block mt-1 w-full" type="text" name="wave_pay_name" value="{{ old('wave_pay_name', $artist->wave_pay_name ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-input-label for="setup_wave_pay_phone" value="Phone Number" />
                                        <x-text-input id="setup_wave_pay_phone" class="block mt-1 w-full" type="text" name="wave_pay_phone" value="{{ old('wave_pay_phone', $artist->wave_pay_phone ?? '') }}" placeholder="09xxxxxxxxx" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Share Info (Read-only) -->
                        <div class="mb-6">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-4">Revenue Share Agreement</h3>
                            <div class="bg-brand-100 border-2 border-black p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="font-bold text-black/60">Your Share</span>
                                        <div class="font-black text-lg text-emerald-700">{{ $artist->revenue_share_percentage ?? 70 }}%</div>
                                    </div>
                                    <div>
                                        <span class="font-bold text-black/60">TeleMusic Fee</span>
                                        <div class="font-black text-lg text-black/50">{{ 100 - ($artist->revenue_share_percentage ?? 70) }}%</div>
                                    </div>
                                    <div>
                                        <span class="font-bold text-black/60">Calculation</span>
                                        <div class="font-bold text-black/70">Total Revenue × Your % = Your Earnings</div>
                                    </div>
                                </div>
                                <p class="text-[10px] font-bold text-black/40 mt-3">This agreement is set by the platform admin. Contact admin for changes.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Save Profile') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
