<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Add Music Store') }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('admin.stores.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 mb-6">
                        <div>
                            <x-input-label for="name" value="Store Name" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required placeholder="e.g. Spotify, Apple Music" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="slug" value="Slug" />
                            <x-text-input id="slug" class="block mt-1 w-full" type="text" name="slug" required placeholder="e.g. spotify" />
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="url" value="Store URL" />
                            <x-text-input id="url" class="block mt-1 w-full" type="url" name="url" placeholder="https://..." />
                        </div>
                        <div>
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Add Store') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
