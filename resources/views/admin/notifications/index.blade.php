<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-black">Admin Notifications</h2></x-slot>
    <div class="px-6 py-8"><div class="mx-auto max-w-5xl space-y-3">
        @forelse($notifications as $notification)
            <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                @csrf
                <button class="w-full border-2 border-black p-5 text-left shadow-[3px_3px_0_#000] {{ $notification->read_at ? 'bg-white' : 'bg-brand-500/30' }}">
                    <div class="flex items-start justify-between gap-4"><div><div class="font-black">{{ $notification->data['title'] ?? 'Notification' }}</div><div class="mt-1 text-sm font-bold text-black/60">{{ $notification->data['message'] ?? '' }}</div></div><span class="whitespace-nowrap text-xs font-bold text-black/40">{{ $notification->created_at->diffForHumans() }}</span></div>
                </button>
            </form>
        @empty<div class="border-2 border-black bg-white p-8 text-center font-bold text-black/40">No notifications yet.</div>@endforelse
        {{ $notifications->links() }}
    </div></div>
</x-app-layout>
