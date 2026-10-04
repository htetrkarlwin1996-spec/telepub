<x-app-layout>
    <x-slot name="header">{{ $post->exists ? 'Edit Knowledge Post' : 'New Knowledge Post' }}</x-slot>
    <div class="mx-auto max-w-4xl">
        @if($errors->any())<div class="mb-5 border-2 border-black bg-red-200 p-4 font-black">{{ $errors->first() }}</div>@endif
        <form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.knowledge.update', $post) : route('admin.knowledge.store') }}" class="space-y-5 border-2 border-black bg-white p-6 shadow-[6px_6px_0_#000]">@csrf @if($post->exists) @method('PUT') @endif
            <label class="block text-sm font-black">Title<input name="title" value="{{ old('title', $post->title) }}" required class="mt-1 w-full border-2 border-black px-4 py-3 font-bold"></label>
            <label class="block text-sm font-black">Short Excerpt<textarea name="excerpt" rows="3" required class="mt-1 w-full border-2 border-black px-4 py-3 font-bold">{{ old('excerpt', $post->excerpt) }}</textarea></label>
            <label class="block text-sm font-black">Article Body<textarea name="body" rows="18" required class="mt-1 w-full border-2 border-black px-4 py-3 font-medium leading-7">{{ old('body', $post->body) }}</textarea></label>
            <div class="grid gap-5 sm:grid-cols-2"><label class="block text-sm font-black">Cover Image<input type="file" name="cover_image" accept="image/png,image/jpeg,image/webp" class="mt-1 w-full border-2 border-black p-3 font-bold"></label><label class="block text-sm font-black">Display Order<input type="number" name="sort_order" min="0" value="{{ old('sort_order', $post->sort_order ?? 0) }}" required class="mt-1 w-full border-2 border-black px-4 py-3 font-black"></label></div>
            @if($post->cover_image)<img src="{{ str_starts_with($post->cover_image, 'images/') ? asset($post->cover_image) : Storage::url($post->cover_image) }}" alt="Current cover" class="h-40 w-72 border-2 border-black object-cover">@endif
            <label class="flex items-center gap-3 border-2 border-black bg-amber-100 p-4 font-black"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $post->is_published)) class="border-2 border-black"> Publish this post to artists</label>
            <div class="flex gap-3"><button class="border-2 border-black bg-brand-500 px-6 py-3 font-black shadow-[3px_3px_0_#000]">Save Post</button><a href="{{ route('admin.knowledge.index') }}" class="border-2 border-black px-6 py-3 font-black">Cancel</a></div>
        </form>
    </div>
</x-app-layout>
