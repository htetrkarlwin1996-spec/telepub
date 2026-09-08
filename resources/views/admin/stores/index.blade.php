<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-2xl text-black leading-tight tracking-tight">{{ __('Music Stores') }}</h2>
            <a href="{{ route('admin.stores.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">+ New Store</a>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success')) <div class="bg-emerald-400 border-2 border-black text-black font-bold px-4 py-3 mb-6">{{ session('success') }}</div> @endif
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Name</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Slug</th>
                                <th class="text-left py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider hidden md:table-cell">URL</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Distributions</th>
                                <th class="text-center py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Active</th>
                                <th class="text-right py-3 px-2 text-black font-extrabold uppercase text-xs tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stores as $store)
                            <tr class="border-b border-black/10 hover:bg-brand-500/10 transition-colors">
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <x-store-logo :store="$store" size="6" />
                                        <span class="font-bold text-black">{{ $store->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-sm font-semibold text-black/50">{{ $store->slug }}</td>
                                <td class="py-3 px-2 hidden md:table-cell"><a href="{{ $store->url }}" target="_blank" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">{{ $store->url ?? '-' }}</a></td>
                                <td class="py-3 px-2 text-center font-bold text-black">{{ $store->distributions_count }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="text-xs font-bold border border-black px-2 py-1 {{ $store->is_active ? 'bg-emerald-400' : 'bg-red-200' }}">
                                        {{ $store->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-right">
                                    <a href="{{ route('admin.stores.edit', $store) }}" class="font-extrabold text-black underline decoration-brand-500 decoration-2 underline-offset-2 hover:decoration-black text-xs">Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-8 text-center font-bold text-black/40">No stores configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
