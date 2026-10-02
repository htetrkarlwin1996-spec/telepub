<div id="login-announcement" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-black/75 p-4" role="dialog" aria-modal="true" aria-label="TeleMusic announcement">
    <div class="relative my-auto w-full max-w-2xl border-2 border-black bg-white shadow-[8px_8px_0_#FFE500]">
        <button type="button" data-close-login-announcement class="absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center border-2 border-black bg-white text-xl font-black" aria-label="Close announcement">×</button>
        @if($announcement['youtube_embed_url'])
            <div class="aspect-video w-full border-b-2 border-black bg-black">
                <iframe class="h-full w-full" src="{{ $announcement['youtube_embed_url'] }}" title="{{ $announcement['title'] ?: 'TeleMusic announcement' }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        @endif
        @if($announcement['image_path'])
            <img src="{{ asset('storage/'.$announcement['image_path']) }}" alt="{{ $announcement['title'] ?: 'Announcement' }}" class="max-h-[55vh] w-full border-b-2 border-black object-contain">
        @endif
        <div class="p-6 sm:p-8">
            @if($announcement['title'])<h2 id="login-announcement-title" class="pr-10 text-3xl font-black">{{ $announcement['title'] }}</h2>@endif
            @if($announcement['body'])<div class="mt-4 whitespace-pre-line text-base font-semibold leading-7 text-black/75">{{ $announcement['body'] }}</div>@endif
            <div class="mt-7 flex flex-wrap gap-3">
                @if($announcement['button_text'] && $announcement['button_url'])
                    <a href="{{ $announcement['button_url'] }}" target="_blank" rel="noopener noreferrer" class="border-2 border-black bg-brand-500 px-6 py-3 font-black uppercase shadow-[3px_3px_0_#000]">{{ $announcement['button_text'] }}</a>
                @endif
                <button type="button" data-close-login-announcement class="border-2 border-black bg-white px-6 py-3 font-black uppercase">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('login-announcement');
        const close = () => {
            modal?.classList.add('hidden');
            modal?.classList.remove('flex');
        };
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
        document.querySelectorAll('[data-close-login-announcement]').forEach(button => button.addEventListener('click', close));
        modal?.addEventListener('click', event => { if (event.target === modal) close(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
    });
</script>
