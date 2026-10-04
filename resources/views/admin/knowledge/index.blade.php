<x-app-layout>
    <x-slot name="header">Knowledges Management</x-slot>
    <div class="mx-auto max-w-6xl">
        @if(session('success'))<div class="mb-5 border-2 border-black bg-emerald-300 p-4 font-black">{{ session('success') }}</div>@endif
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><h1 class="text-3xl font-black">Knowledges Posts</h1><p class="font-bold text-black/60">Create and publish articles for artists.</p></div><a href="{{ route('admin.knowledge.create') }}" class="border-2 border-black bg-brand-500 px-5 py-3 font-black shadow-[4px_4px_0_#000]">+ New Post</a></div>
        <div class="overflow-x-auto border-2 border-black bg-white shadow-[5px_5px_0_#000]">
            <table class="w-full text-left"><thead class="border-b-2 border-black bg-black text-white"><tr><th class="p-4">Title</th><th class="p-4">Status</th><th class="p-4">Order</th><th class="p-4 text-right">Actions</th></tr></thead><tbody>
            @foreach($posts as $post)<tr class="border-b-2 border-black/15"><td class="p-4"><div class="font-black">{{ $post->title }}</div><div class="text-xs font-bold text-black/50">{{ $post->slug }}</div></td><td class="p-4"><span class="border border-black px-2 py-1 text-xs font-black {{ $post->is_published ? 'bg-emerald-300' : 'bg-gray-200' }}">{{ $post->is_published ? 'PUBLISHED' : 'DRAFT' }}</span></td><td class="p-4 font-black">{{ $post->sort_order }}</td><td class="p-4"><div class="flex justify-end gap-2"><a href="{{ route('knowledge.show', $post) }}" class="border-2 border-black px-3 py-2 text-xs font-black">View</a><a href="{{ route('admin.knowledge.edit', $post) }}" class="border-2 border-black bg-brand-500 px-3 py-2 text-xs font-black">Edit</a><form method="POST" action="{{ route('admin.knowledge.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="border-2 border-black bg-red-200 px-3 py-2 text-xs font-black">Delete</button></form></div></td></tr>@endforeach
            </tbody></table>
        </div><div class="mt-6">{{ $posts->links() }}</div>
    </div>
</x-app-layout>
