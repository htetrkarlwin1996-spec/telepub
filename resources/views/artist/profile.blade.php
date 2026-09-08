<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Artist Profile') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            @if(session('success'))
            <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
            @endif
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('artist.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label for="artist_name" value="Artist Name" />
                            <x-text-input id="artist_name" class="block mt-1 w-full" type="text" name="artist_name" value="{{ $artist->artist_name }}" required />
                        </div>
                        <div>
                            <x-input-label for="genre" value="Genre" />
                            <select id="genre" name="genre" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                <option value="">Select Genre</option>
                                @foreach($genres as $genreOption)
                                    <option value="{{ $genreOption }}" {{ $artist->genre === $genreOption ? 'selected' : '' }}>{{ $genreOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="country" value="Country" />
                            <select id="country" name="country" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                <option value="">Select Country</option>
                                @foreach($countries as $countryOption)
                                    <option value="{{ $countryOption }}" {{ $artist->country === $countryOption ? 'selected' : '' }}>{{ $countryOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="bio" value="Bio" />
                            <textarea id="bio" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="bio" rows="3">{{ $artist->bio }}</textarea>
                        </div>

                        <!-- Profile Links -->
                        <div class="md:col-span-2 border-t-2 border-black pt-4 mt-2">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-2">Profile Links</h3>
                            <p class="text-[10px] font-bold text-black/50 mb-3">Add your music platform profile links so they auto-fill when creating releases.</p>
                        </div>
                        <div class="md:col-span-2 border-2 border-black p-4 mb-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="profile_spotify_profile_url" value="Spotify Profile URL" />
                                    <x-text-input id="profile_spotify_profile_url" class="block mt-1 w-full" type="url" name="spotify_profile_url" value="{{ $artist->spotify_profile_url }}" placeholder="https://open.spotify.com/artist/..." />
                                </div>
                                <div>
                                    <x-input-label for="profile_apple_music_profile_url" value="Apple Music Profile URL" />
                                    <x-text-input id="profile_apple_music_profile_url" class="block mt-1 w-full" type="url" name="apple_music_profile_url" value="{{ $artist->apple_music_profile_url }}" placeholder="https://music.apple.com/artist/..." />
                                </div>
                                <div>
                                    <x-input-label for="profile_youtube_profile_url" value="YouTube Profile URL" />
                                    <x-text-input id="profile_youtube_profile_url" class="block mt-1 w-full" type="url" name="youtube_profile_url" value="{{ $artist->youtube_profile_url }}" placeholder="https://youtube.com/@..." />
                                </div>
                                <div>
                                    <x-input-label for="profile_tidal_profile_url" value="Tidal Profile URL" />
                                    <x-text-input id="profile_tidal_profile_url" class="block mt-1 w-full" type="url" name="tidal_profile_url" value="{{ $artist->tidal_profile_url }}" placeholder="https://tidal.com/artist/..." />
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="md:col-span-2 border-t-2 border-black pt-4 mt-2">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-2">Payout Methods</h3>
                        </div>

                        <!-- PayPal -->
                        <div class="md:col-span-2 border-2 border-black p-4 mb-2">
                            <h4 class="font-bold text-black mb-2">PayPal</h4>
                            <div>
                                <x-input-label for="profile_paypal_email" value="PayPal Email" />
                                <x-text-input id="profile_paypal_email" class="block mt-1 w-full" type="email" name="paypal_email" value="{{ $artist->paypal_email }}" placeholder="artist@example.com" />
                            </div>
                        </div>

                        <!-- Bank Transfer -->
                        <div class="md:col-span-2 border-2 border-black p-4 mb-2">
                            <h4 class="font-bold text-black mb-2">Bank Transfer</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="profile_bank_account_name" value="Account Name" />
                                    <x-text-input id="profile_bank_account_name" class="block mt-1 w-full" type="text" name="bank_account_name" value="{{ $artist->bank_account_name }}" />
                                </div>
                                <div>
                                    <x-input-label for="profile_bank_name" value="Bank Name" />
                                    <x-text-input id="profile_bank_name" class="block mt-1 w-full" type="text" name="bank_name" value="{{ $artist->bank_name }}" />
                                </div>
                                <div>
                                    <x-input-label for="profile_bank_country" value="Bank Country" />
                                    <select id="profile_bank_country" name="bank_country" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $countryOption)
                                            <option value="{{ $countryOption }}" {{ $artist->bank_country === $countryOption ? 'selected' : '' }}>{{ $countryOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="profile_bank_account_no" value="Account Number" />
                                    <x-text-input id="profile_bank_account_no" class="block mt-1 w-full" type="text" name="bank_account_no" value="{{ $artist->bank_account_no }}" />
                                </div>
                            </div>
                        </div>

                        <!-- KBZ Pay -->
                        <div class="md:col-span-2 border-2 border-black p-4 mb-2">
                            <h4 class="font-bold text-black mb-2">KBZ Pay</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="profile_kbz_pay_name" value="Name" />
                                    <x-text-input id="profile_kbz_pay_name" class="block mt-1 w-full" type="text" name="kbz_pay_name" value="{{ $artist->kbz_pay_name }}" />
                                </div>
                                <div>
                                    <x-input-label for="profile_kbz_pay_phone" value="Phone Number" />
                                    <x-text-input id="profile_kbz_pay_phone" class="block mt-1 w-full" type="text" name="kbz_pay_phone" value="{{ $artist->kbz_pay_phone }}" placeholder="09xxxxxxxxx" />
                                </div>
                            </div>
                        </div>

                        <!-- Wave Pay -->
                        <div class="md:col-span-2 border-2 border-black p-4 mb-2">
                            <h4 class="font-bold text-black mb-2">Wave Pay</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="profile_wave_pay_name" value="Name" />
                                    <x-text-input id="profile_wave_pay_name" class="block mt-1 w-full" type="text" name="wave_pay_name" value="{{ $artist->wave_pay_name }}" />
                                </div>
                                <div>
                                    <x-input-label for="profile_wave_pay_phone" value="Phone Number" />
                                    <x-text-input id="profile_wave_pay_phone" class="block mt-1 w-full" type="text" name="wave_pay_phone" value="{{ $artist->wave_pay_phone }}" placeholder="09xxxxxxxxx" />
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Share Info (Read-only) -->
                        <div class="md:col-span-2 border-t-2 border-black pt-4 mt-2">
                            <h3 class="font-extrabold text-lg text-black tracking-tight mb-2">Revenue Share Agreement</h3>
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
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-primary-button>{{ __('Update Profile') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
