<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Create New Artist') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('admin.artists.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label for="name" value="Full Name" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password" value="Password" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="artist_name" value="Artist Name" />
                            <x-text-input id="artist_name" class="block mt-1 w-full" type="text" name="artist_name" required />
                            <x-input-error :messages="$errors->get('artist_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="genre" value="Genre" />
                            <x-text-input id="genre" class="block mt-1 w-full" type="text" name="genre" />
                        </div>
                        <div>
                            <x-input-label for="country" value="Country" />
                            <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" />
                        </div>
                        <div>
                            <x-input-label for="revenue_share_percentage" value="Revenue Share (%)" />
                            <x-text-input id="revenue_share_percentage" class="block mt-1 w-full" type="number" step="0.01" min="0" max="100" name="revenue_share_percentage" value="70.00" />
                            <p class="text-[10px] font-bold text-black/40 mt-1">% of revenue paid to artist. TeleMusic Fee = {{ 100 - 70 }}%.</p>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="bio" value="Bio" />
                            <textarea id="bio" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="bio" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Create Artist') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
