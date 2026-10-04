<x-app-layout>
    <x-slot name="header">Knowledge</x-slot>
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 border-2 border-black bg-brand-500 p-7 shadow-[6px_6px_0_#000]">
            <p class="text-xs font-black uppercase tracking-[.2em]">TeleMusic, LLC</p>
            <h1 class="mt-2 text-3xl font-black">Music Rights & Royalties Guide</h1>
            <p class="mt-3 max-w-3xl font-bold text-black/70">Master recordings, songwriter rights, global performance, mechanical royalties နှင့် licensing အကြောင်းကို တစ်နေရာတည်းမှာ လေ့လာနိုင်ပါတယ်။</p>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($posts as $post)
                <article class="overflow-hidden border-2 border-black bg-white shadow-[5px_5px_0_#000]">
                    @if($post->cover_image)<img src="{{ str_starts_with($post->cover_image, 'images/') ? asset($post->cover_image) : Storage::url($post->cover_image) }}" alt="{{ $post->title }}" class="h-48 w-full border-b-2 border-black object-cover">@endif
                    <div class="p-5"><h2 class="text-xl font-black leading-tight">{{ $post->title }}</h2><p class="mt-3 text-sm font-semibold leading-6 text-black/65">{{ $post->excerpt }}</p><a href="{{ route('knowledge.show', $post) }}" class="mt-5 inline-flex border-2 border-black bg-brand-500 px-4 py-2 text-sm font-black uppercase">Read Article →</a></div>
                </article>
            @empty
                <div class="border-2 border-black bg-white p-8 font-bold">Knowledge articles will appear here.</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </div>
</x-app-layout>
