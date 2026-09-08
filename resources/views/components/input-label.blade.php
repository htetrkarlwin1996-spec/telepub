@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-extrabold text-sm text-black mb-1']) }}>
    {{ $value ?? $slot }}
</label>
