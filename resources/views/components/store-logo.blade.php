@props(['store' => null, 'size' => 8])

@php
    $slug = $store?->slug ?? '';
    $localLogo = public_path("images/stores/{$slug}.svg");
    $logoUrl = is_file($localLogo) ? asset("images/stores/{$slug}.svg") : $store?->logo;
    $sizeClass = match ((int) $size) {
        3 => 'w-3 h-3', 4 => 'w-4 h-4', 5 => 'w-5 h-5', 6 => 'w-6 h-6',
        7 => 'w-7 h-7', 8 => 'w-8 h-8', default => 'w-5 h-5',
    };
@endphp

<span class="{{ $sizeClass }} shrink-0 rounded-md bg-white border border-black/10 flex items-center justify-center p-0.5 overflow-hidden">
    @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $store?->name ?? 'Store' }} logo" class="w-full h-full object-contain" loading="lazy">
    @else
        <span class="text-[9px] leading-none font-black text-black/50">{{ $store ? strtoupper(substr($store->name, 0, 2)) : 'NA' }}</span>
    @endif
</span>
