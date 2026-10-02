<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">Admin Settings</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto space-y-8">
            @if(session('success'))
                <div class="bg-emerald-400 border-2 border-black px-4 py-3 font-bold">{{ session('success') }}</div>
            @endif

            <section class="bg-white border-2 border-black shadow-[4px_4px_0_#000]">
                <div class="p-6 border-b-2 border-black">
                    <h3 class="text-xl font-black">Global Display Currency</h3>
                    <p class="mt-2 text-sm font-bold text-black/60">Changes the currency label everywhere. Existing amounts and stored royalty values are not converted.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.currency') }}" class="p-6 flex flex-col sm:flex-row items-end gap-4">
                    @csrf
                    @method('PUT')
                    <div class="w-full sm:max-w-xs">
                        <x-input-label for="display_currency" value="Display Currency" />
                        <select id="display_currency" name="display_currency" class="mt-1 w-full border-2 border-black px-3 py-2.5 font-extrabold">
                            <option value="USD" @selected($displayCurrency === 'USD')>USD</option>
                            <option value="EUR" @selected($displayCurrency === 'EUR')>EUR</option>
                        </select>
                        <x-input-error :messages="$errors->get('display_currency')" class="mt-2" />
                    </div>
                    <button class="px-6 py-3 bg-brand-500 border-2 border-black font-black uppercase shadow-[3px_3px_0_#000]">Save Currency</button>
                </form>
            </section>

            <section class="bg-white border-2 border-black shadow-[4px_4px_0_#000]">
                <div class="p-6 border-b-2 border-black">
                    <h3 class="text-xl font-black">Withdrawal Processing Fee</h3>
                    <p class="mt-2 text-sm font-bold text-black/60">Applied to every new Web and API withdrawal. Set 0 to disable the fee.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.withdrawal-fee') }}" class="p-6 flex flex-col sm:flex-row items-end gap-4">
                    @csrf @method('PUT')
                    <div class="w-full sm:max-w-xs">
                        <x-input-label for="withdrawal_fee_percentage" value="Processing Fee (%)" />
                        <x-text-input id="withdrawal_fee_percentage" name="withdrawal_fee_percentage" type="number" step="0.01" min="0" max="100" value="{{ old('withdrawal_fee_percentage', $withdrawalFeePercentage) }}" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('withdrawal_fee_percentage')" class="mt-2" />
                    </div>
                    <div class="w-full sm:max-w-xs">
                        <x-input-label for="minimum_withdrawal_amount" :value="'Minimum Withdrawal ('.display_currency().')'" />
                        <x-text-input id="minimum_withdrawal_amount" name="minimum_withdrawal_amount" type="number" step="0.01" min="0" value="{{ old('minimum_withdrawal_amount', $minimumWithdrawalAmount) }}" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('minimum_withdrawal_amount')" class="mt-2" />
                    </div>
                    <button class="px-6 py-3 bg-brand-500 border-2 border-black font-black uppercase shadow-[3px_3px_0_#000]">Save Withdrawal Fee</button>
                </form>
            </section>

            <section class="border-2 border-black bg-white shadow-[4px_4px_0_#000]">
                <div class="border-b-2 border-black p-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="text-xl font-black">Login Announcement Popup</h3>
                        <span class="border-2 border-black px-3 py-1 text-xs font-black uppercase {{ $loginAnnouncement['enabled'] ? 'bg-emerald-300' : 'bg-gray-100' }}">{{ $loginAnnouncement['enabled'] ? 'Active' : 'Disabled' }}</span>
                    </div>
                    <p class="mt-2 text-sm font-bold text-black/60">Shown once after each Artist or Master Account login. Add text, an image, a YouTube video, or any combination.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.login-announcement') }}" enctype="multipart/form-data" class="space-y-5 p-6">
                    @csrf @method('PUT')
                    <input type="hidden" name="enabled" value="0">
                    <label class="flex items-center gap-3 font-black"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $loginAnnouncement['enabled'])) class="h-5 w-5 border-2 border-black text-brand-500 focus:ring-0"> Enable login announcement</label>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div><x-input-label for="announcement_title" value="Title" /><x-text-input id="announcement_title" name="title" class="mt-1 block w-full" :value="old('title', $loginAnnouncement['title'])" maxlength="200" /><x-input-error :messages="$errors->get('title')" class="mt-2" /></div>
                        <div><x-input-label for="announcement_youtube_url" value="YouTube URL" /><x-text-input id="announcement_youtube_url" name="youtube_url" type="url" class="mt-1 block w-full" :value="old('youtube_url', $loginAnnouncement['youtube_url'])" placeholder="https://youtu.be/..." /><x-input-error :messages="$errors->get('youtube_url')" class="mt-2" /></div>
                    </div>
                    <div><x-input-label for="announcement_body" value="Announcement Text" /><textarea id="announcement_body" name="body" rows="6" maxlength="5000" class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-semibold focus:border-brand-500 focus:ring-0">{{ old('body', $loginAnnouncement['body']) }}</textarea><x-input-error :messages="$errors->get('body')" class="mt-2" /></div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div><x-input-label for="announcement_image" value="Image (JPG, PNG, WebP, GIF — max 10 MB)" /><input id="announcement_image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="mt-1 block w-full border-2 border-black bg-white p-2 font-bold"><x-input-error :messages="$errors->get('image')" class="mt-2" />@if($loginAnnouncement['image_path'])<label class="mt-2 flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="remove_image" value="1"> Remove current image</label><img src="{{ asset('storage/'.$loginAnnouncement['image_path']) }}" alt="Current announcement" class="mt-3 max-h-48 border-2 border-black object-contain">@endif</div>
                        <div>@if($loginAnnouncementEmbedUrl)<x-input-label value="Current YouTube Preview" /><div class="mt-1 aspect-video border-2 border-black"><iframe class="h-full w-full" src="{{ $loginAnnouncementEmbedUrl }}" title="YouTube preview" allowfullscreen></iframe></div>@else<div class="border-2 border-dashed border-black/30 p-5 text-sm font-bold text-black/40">Paste a public YouTube, Shorts, or youtu.be URL to embed a video.</div>@endif</div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div><x-input-label for="announcement_button_text" value="Button Text (optional)" /><x-text-input id="announcement_button_text" name="button_text" class="mt-1 block w-full" :value="old('button_text', $loginAnnouncement['button_text'])" placeholder="Learn More" /></div>
                        <div><x-input-label for="announcement_button_url" value="Button URL (optional)" /><x-text-input id="announcement_button_url" name="button_url" type="url" class="mt-1 block w-full" :value="old('button_url', $loginAnnouncement['button_url'])" placeholder="https://..." /><x-input-error :messages="$errors->get('button_url')" class="mt-2" /></div>
                    </div>
                    <button class="border-2 border-black bg-brand-500 px-6 py-3 font-black uppercase shadow-[3px_3px_0_#000]">Save Login Announcement</button>
                </form>
            </section>

            <section class="border-2 border-black {{ $maintenanceActive ? 'bg-amber-200' : 'bg-white' }} shadow-[4px_4px_0_#000]">
                <div class="p-6 border-b-2 border-black">
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="text-xl font-black">Site Maintenance</h3>
                        <span class="px-3 py-1 border-2 border-black text-xs font-black uppercase {{ $maintenanceActive ? 'bg-brand-500' : 'bg-emerald-400' }}">{{ $maintenanceActive ? 'Maintenance ON' : 'Site is Live' }}</span>
                    </div>
                    <p class="mt-2 text-sm font-bold text-black/60">Control the public maintenance screen while keeping Admin access available.</p>
                    @if($maintenanceActive && $maintenanceEndsAt)
                        <p id="admin-maintenance-countdown" data-seconds="{{ $maintenanceRemaining }}" class="mt-3 font-black">Ends in: calculating…</p>
                    @endif
                </div>
                <div class="p-6">
                    @if($maintenanceActive)
                        <form method="POST" action="{{ route('admin.maintenance.disable') }}">
                            @csrf
                            <button onclick="return confirm('Make the public site live again?')" class="px-6 py-3 bg-emerald-400 border-2 border-black font-black shadow-[3px_3px_0_#000]">Turn Maintenance OFF</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.maintenance.enable') }}" class="flex flex-col lg:flex-row lg:items-end gap-4">
                            @csrf
                            @foreach(['days' => [0, 30], 'hours' => [1, 23], 'minutes' => [0, 59]] as $field => [$default, $max])
                                <label class="text-xs font-black uppercase">{{ ucfirst($field) }}
                                    <input type="number" name="{{ $field }}" value="{{ old($field, $default) }}" min="0" max="{{ $max }}" required class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-black">
                                </label>
                            @endforeach
                            <button onclick="return confirm('Enable maintenance mode for all public users?')" class="px-6 py-3 bg-brand-500 border-2 border-black font-black shadow-[3px_3px_0_#000]">Turn Maintenance ON</button>
                        </form>
                        @error('duration')<p class="mt-3 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
                    @endif
                </div>
            </section>

            <section class="bg-white border-2 border-black shadow-[4px_4px_0_#000]">
                <div class="p-6 border-b-2 border-black"><h3 class="text-xl font-black">Release Checkout Pricing</h3><p class="mt-2 text-sm font-bold text-black/60">First release uses one fixed USD price. Later releases use the Single, EP, or Album price. THB and MMK values are converted at checkout with the rates below.</p></div>
                <form method="POST" action="{{ route('admin.settings.release-pricing') }}" class="p-6 space-y-6">@csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach(['release_price_first_usd'=>'First Release (USD)','release_price_single_usd'=>'Single (USD)','release_price_ep_usd'=>'EP (USD)','release_price_album_usd'=>'Album (USD)'] as $key=>$label)
                            <label class="text-xs font-black uppercase">{{ $label }}<input type="number" step="0.01" min="0" name="{{ $key }}" value="{{ old($key, $releasePricing[str_replace(['release_price_','_usd'], '', $key)]) }}" class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-black"></label>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="text-xs font-black uppercase">1 USD to THB<input type="number" step="0.0001" name="usd_to_thb_rate" value="{{ old('usd_to_thb_rate', $releasePricing['usd_to_thb']) }}" class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-black"></label>
                        <label class="text-xs font-black uppercase">1 USD to MMK<input type="number" step="0.0001" name="usd_to_mmk_rate" value="{{ old('usd_to_mmk_rate', $releasePricing['usd_to_mmk']) }}" class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-black"></label>
                    </div>
                    <label class="block text-xs font-black uppercase">Thai Offline Bank Transfer Instructions<textarea name="offline_bank_instructions" rows="5" class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-bold">{{ old('offline_bank_instructions', $releasePricing['offline_instructions']) }}</textarea></label>
                    <button class="px-6 py-3 bg-brand-500 border-2 border-black font-black uppercase shadow-[3px_3px_0_#000]">Save Release Pricing</button>
                </form>
            </section>

            @if($pendingOfflinePayments->isNotEmpty())
            <section class="bg-amber-100 border-2 border-black shadow-[4px_4px_0_#000]">
                <div class="p-6 border-b-2 border-black"><h3 class="text-xl font-black">Pending Offline Release Payments</h3></div>
                <div class="p-6 space-y-3">@foreach($pendingOfflinePayments as $payment)<div class="bg-white border-2 border-black p-4 flex flex-wrap items-center justify-between gap-4"><div><div class="font-black">{{ $payment->album->title }}</div><div class="text-sm font-bold text-black/60">{{ $payment->user->email }} · {{ $payment->currency }} {{ number_format($payment->amount, 2) }} · {{ $payment->reference }}</div></div><form method="POST" action="{{ route('admin.release-payments.approve', $payment) }}">@csrf<button class="px-4 py-2 bg-emerald-400 border-2 border-black font-black">Mark Paid & Submit</button></form></div>@endforeach</div>
            </section>
            @endif

            <section class="bg-blue-100 border-2 border-black shadow-[4px_4px_0_#000] p-6">
                <h3 class="text-xl font-black">More Settings</h3>
                <p class="mt-2 text-sm font-bold text-black/60">Future application settings can be added to this page.</p>
            </section>
        </div>
    </div>

    @if($maintenanceActive && $maintenanceEndsAt)
    <script>
        (() => {
            const element = document.getElementById('admin-maintenance-countdown');
            let seconds = Number(element.dataset.seconds || 0);
            const pad = value => String(value).padStart(2, '0');
            const tick = () => {
                const days = Math.floor(seconds / 86400);
                const hours = Math.floor((seconds % 86400) / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                element.textContent = `Ends in: ${days}d ${pad(hours)}h ${pad(minutes)}m ${pad(seconds % 60)}s`;
                if (seconds-- > 0) setTimeout(tick, 1000);
            };
            tick();
        })();
    </script>
    @endif
</x-app-layout>
