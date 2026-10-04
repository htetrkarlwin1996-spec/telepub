<x-app-layout>
    <x-slot name="header">Knowledges</x-slot>
    <article class="mx-auto max-w-4xl overflow-hidden border-2 border-black bg-white shadow-[7px_7px_0_#000]">
        @if($knowledgePost->cover_image)<img src="{{ str_starts_with($knowledgePost->cover_image, 'images/') ? asset($knowledgePost->cover_image) : Storage::url($knowledgePost->cover_image) }}" alt="{{ $knowledgePost->title }}" class="max-h-[460px] w-full border-b-2 border-black object-cover">@endif
        <div class="p-6 sm:p-10">
            <a href="{{ route('knowledge.index') }}" class="text-sm font-black uppercase underline">← All Knowledges</a>
            <h1 class="mt-5 text-3xl font-black leading-tight sm:text-5xl">{{ $knowledgePost->title }}</h1>
            <p class="mt-4 border-l-8 border-brand-500 pl-4 text-lg font-bold text-black/65">{{ $knowledgePost->excerpt }}</p>
            <div class="mt-8 whitespace-pre-line text-base font-medium leading-8 text-black/85">{{ $knowledgePost->body }}</div>
        </div>
    </article>
</x-app-layout>
