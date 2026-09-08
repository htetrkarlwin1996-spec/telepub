@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-bold text-sm text-black bg-emerald-400 border-2 border-black px-4 py-3']) }}>
        {{ $status }}
    </div>
@endif
