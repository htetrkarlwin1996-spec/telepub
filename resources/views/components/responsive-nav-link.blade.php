@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-500 text-start text-base font-bold text-black bg-brand-500/20 focus:outline-none focus:text-black focus:bg-brand-500/30 focus:border-brand-500 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-semibold text-black/70 hover:text-black hover:bg-brand-500/10 hover:border-brand-500/50 focus:outline-none focus:text-black focus:bg-brand-500/10 focus:border-brand-500/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
