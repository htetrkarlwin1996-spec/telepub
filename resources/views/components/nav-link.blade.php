@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-2.5 text-sm font-extrabold text-black bg-brand-500 border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none'
            : 'flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-black/70 hover:text-black hover:bg-brand-500/20 border-2 border-transparent hover:border-black hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all rounded-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
