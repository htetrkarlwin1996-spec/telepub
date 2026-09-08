<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border-2 border-black font-extrabold text-xs text-black uppercase tracking-widest shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:bg-black/5 focus:outline-none focus:ring-0 disabled:opacity-25 transition-all rounded-none']) }}>
    {{ $slot }}
</button>
