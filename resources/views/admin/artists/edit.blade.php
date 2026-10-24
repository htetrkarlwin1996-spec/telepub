<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Edit Artist') }}: {{ $artist->artist_name }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                @if(session('success'))
                    <div class="mb-5 border-2 border-black bg-green-100 p-4 font-bold">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-5 border-2 border-black bg-red-100 p-4 font-bold">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.artists.update', $artist) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="md:col-span-2 border-b-2 border-black pb-2">
                            <h3 class="text-lg font-black">Artist Profile</h3>
                        </div>
                        <div>
                            <x-input-label for="artist_name" value="Artist Name" />
                            <x-text-input id="artist_name" class="block mt-1 w-full" type="text" name="artist_name" value="{{ $artist->artist_name }}" required />
                        </div>
                        <div>
                            <x-input-label for="genre" value="Genre" />
                            <x-text-input id="genre" class="block mt-1 w-full" type="text" name="genre" value="{{ $artist->genre }}" />
                        </div>
                        <div>
                            <x-input-label for="country" value="Country" />
                            <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" value="{{ $artist->country }}" />
                        </div>
                        <div>
                            <x-input-label for="payment_email" value="Payment Email" />
                            <x-text-input id="payment_email" class="block mt-1 w-full" type="email" name="payment_email" value="{{ $artist->payment_email }}" />
                        </div>
                        <div>
                            <x-input-label for="paypal_email" value="PayPal Email" />
                            <x-text-input id="paypal_email" class="block mt-1 w-full" type="email" name="paypal_email" value="{{ $artist->paypal_email }}" />
                        </div>
                        <div class="md:col-span-2 border-t-2 border-black pt-4 mt-2">
                            <h3 class="font-extrabold text-sm text-black mb-2">Profile Links</h3>
                        </div>
                        <div>
                            <x-input-label for="spotify_profile_url" value="Spotify Profile URL" />
                            <x-text-input id="spotify_profile_url" class="block mt-1 w-full" type="url" name="spotify_profile_url" value="{{ $artist->spotify_profile_url }}" placeholder="https://open.spotify.com/artist/..." />
                        </div>
                        <div>
                            <x-input-label for="apple_music_profile_url" value="Apple Music Profile URL" />
                            <x-text-input id="apple_music_profile_url" class="block mt-1 w-full" type="url" name="apple_music_profile_url" value="{{ $artist->apple_music_profile_url }}" placeholder="https://music.apple.com/artist/..." />
                        </div>
                        <div>
                            <x-input-label for="youtube_profile_url" value="YouTube Profile URL" />
                            <x-text-input id="youtube_profile_url" class="block mt-1 w-full" type="url" name="youtube_profile_url" value="{{ $artist->youtube_profile_url }}" placeholder="https://youtube.com/@..." />
                        </div>
                        <div>
                            <x-input-label for="tidal_profile_url" value="Tidal Profile URL" />
                            <x-text-input id="tidal_profile_url" class="block mt-1 w-full" type="url" name="tidal_profile_url" value="{{ $artist->tidal_profile_url }}" placeholder="https://tidal.com/artist/..." />
                        </div>
                        <div class="md:col-span-2 border-t-2 border-black pt-4 mt-2">
                            <h3 class="font-extrabold text-sm text-black mb-2">Revenue Share Settings</h3>
                        </div>
                        <div>
                            <x-input-label for="revenue_share_percentage" value="Artist Revenue Share (%)" />
                            <x-text-input id="revenue_share_percentage" class="block mt-1 w-full" type="number" step="0.01" min="0" max="100" name="revenue_share_percentage" value="{{ $artist->revenue_share_percentage ?? 85.00 }}" />
                            <p class="text-[10px] font-bold text-black/40 mt-1">% of total revenue paid to artist. TeleMusic Fee = {{ 100 - ($artist->revenue_share_percentage ?? 85) }}%.</p>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="bio" value="Bio" />
                            <textarea id="bio" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="bio" rows="3">{{ $artist->bio }}</textarea>
                        </div>
                        <div class="md:col-span-2 border-t-2 border-black pt-4 mt-2">
                            <h3 class="text-lg font-black">Account &amp; Security</h3>
                        </div>
                        @if($artist->user)
                            <div>
                                <x-input-label for="account_name" value="Account Holder Name" />
                                <x-text-input id="account_name" class="block mt-1 w-full" type="text" name="account_name" value="{{ old('account_name', $artist->user->name) }}" required />
                            </div>
                            <div>
                                <x-input-label for="account_email" value="Login Email" />
                                <x-text-input id="account_email" class="block mt-1 w-full" type="email" name="account_email" value="{{ old('account_email', $artist->user->email) }}" required />
                            </div>
                            <div>
                                <x-input-label for="password" value="New Password (optional)" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                <p class="mt-1 text-[10px] font-bold text-black/50">Leave blank to keep the current password. Minimum 8 characters.</p>
                            </div>
                            <div>
                                <x-input-label for="password_confirmation" value="Confirm New Password" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                            </div>
                            <div class="md:col-span-2 border-2 border-black bg-brand-500/10 p-4">
                                <input type="hidden" name="email_verified" value="0">
                                <label for="email_verified" class="flex cursor-pointer items-start gap-3 font-black">
                                    <input id="email_verified" type="checkbox" name="email_verified" value="1" class="mt-0.5 border-2 border-black text-brand-500 focus:ring-black" @checked((bool) old('email_verified', $artist->user->hasVerifiedEmail()))>
                                    <span>
                                        Email Verified
                                        <span class="block text-xs font-bold text-black/50">Checked: user can access verified-only pages. Unchecked: user must verify their email again.</span>
                                    </span>
                                </label>
                            </div>
                        @else
                            <div class="md:col-span-2 border-2 border-black bg-amber-100 p-4">
                                <div class="font-black">No direct Artist login account</div>
                                <p class="mt-1 text-sm font-bold text-black/60">
                                    This artist is managed through {{ $artist->masterAccounts->pluck('name')->join(', ') ?: 'a Master Account' }}. Password and email verification are controlled on the Master Account owner login.
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Update Artist') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
