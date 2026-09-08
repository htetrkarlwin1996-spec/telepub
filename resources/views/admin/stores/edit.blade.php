<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-black tracking-tight">{{ __('Edit Store') }}: {{ $store->name }}</h2>
    </x-slot>
    <div class="py-8 px-6 sm:px-8 lg:px-10">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-6">
                <form method="POST" action="{{ route('admin.stores.update', $store) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 gap-4 mb-6">
                        <div>
                            <x-input-label for="name" value="Store Name" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ $store->name }}" required />
                        </div>
                        <div>
                            <x-input-label for="slug" value="Slug" />
                            <x-text-input id="slug" class="block mt-1 w-full" type="text" name="slug" value="{{ $store->slug }}" required />
                        </div>
                        <div>
                            <x-input-label for="url" value="Store URL" />
                            <x-text-input id="url" class="block mt-1 w-full" type="url" name="url" value="{{ $store->url }}" />
                        </div>
                        <div>
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" class="block mt-1 w-full border-2 border-black px-3 py-2.5 text-sm font-semibold text-black placeholder:text-black/30 focus:border-brand-500 focus:ring-0 focus:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none" name="description" rows="3">{{ $store->description }}</textarea>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ $store->is_active ? 'checked' : '' }} class="w-4 h-4 border-2 border-black rounded-none text-brand-500 focus:ring-0">
                                <span class="ml-2 font-extrabold text-sm text-black">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Update Store') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
