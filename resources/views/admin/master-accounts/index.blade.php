<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-2xl font-black">Master Accounts</h2><a href="{{ route('admin.master-accounts.create') }}" class="border-2 border-black bg-brand-500 px-4 py-2 text-xs font-black uppercase shadow-[3px_3px_0_#000]">+ New Master Account</a></div></x-slot>
    <div class="px-6 py-8"><div class="mx-auto max-w-7xl">
        @if(session('success'))<div class="mb-5 border-2 border-black bg-emerald-300 p-4 font-bold">{{ session('success') }}</div>@endif
        <div class="overflow-x-auto border-2 border-black bg-white shadow-[4px_4px_0_#000]"><table class="w-full text-sm"><thead><tr class="border-b-2 border-black"><th class="p-3 text-left">Account</th><th class="p-3 text-left">Owner</th><th class="p-3">Artists</th><th class="p-3">TeleMusic Fee</th><th class="p-3">Default Master Fee</th><th class="p-3 text-right">Actions</th></tr></thead><tbody>
        @forelse($accounts as $account)<tr class="border-b border-black/10"><td class="p-3 font-black">{{ $account->name }}</td><td class="p-3"><div class="font-bold">{{ $account->owner->name }}</div><div class="text-xs text-black/50">{{ $account->owner->email }}</div></td><td class="p-3 text-center font-black">{{ $account->artists_count }}</td><td class="p-3 text-center font-black">{{ $account->platform_fee_percentage }}%</td><td class="p-3 text-center font-black">{{ $account->default_management_fee_percentage }}%</td><td class="p-3 text-right"><a href="{{ route('admin.master-accounts.edit', $account) }}" class="font-black underline decoration-2 decoration-brand-500">Edit</a></td></tr>
        @empty<tr><td colspan="6" class="p-8 text-center font-bold text-black/40">No master accounts.</td></tr>@endforelse
        </tbody></table></div><div class="mt-4">{{ $accounts->links() }}</div>
    </div></div>
</x-app-layout>
