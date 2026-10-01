<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-black">Create Managed Artist</h2></x-slot>
    <div class="px-6 py-8"><div class="mx-auto max-w-3xl border-2 border-black bg-white p-6 shadow-[5px_5px_0_#000]">
        @if($errors->any())<div class="mb-5 border-2 border-black bg-red-100 p-4 font-bold">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('manager.artists.store') }}" class="grid gap-5 md:grid-cols-2">@csrf
            <div><x-input-label for="artist_name" value="Artist Name *"/><x-text-input id="artist_name" name="artist_name" class="mt-1 block w-full" required/></div>
            <div><x-input-label for="genre" value="Genre"/><x-text-input id="genre" name="genre" class="mt-1 block w-full"/></div>
            <div><x-input-label for="country" value="Country"/><x-text-input id="country" name="country" class="mt-1 block w-full"/></div>
            <div><x-input-label for="management_fee_percentage" value="Management Fee (%)"/><x-text-input id="management_fee_percentage" name="management_fee_percentage" type="number" step="0.01" min="0" max="{{ $account->maximum_management_fee_percentage }}" class="mt-1 block w-full" placeholder="Default {{ $account->default_management_fee_percentage }}"/></div>
            <div><x-input-label for="email" value="Artist Login Email (optional)"/><x-text-input id="email" name="email" type="email" class="mt-1 block w-full"/></div>
            <div><x-input-label for="password" value="Temporary Password"/><x-text-input id="password" name="password" type="password" class="mt-1 block w-full"/></div>
            <div class="md:col-span-2"><x-input-label for="access_level" value="Artist Login Access"/><select id="access_level" name="access_level" class="mt-1 w-full border-2 border-black px-3 py-2 font-bold"><option value="report_only">Report only</option><option value="full_access">Full catalog access</option></select></div>
            <div class="md:col-span-2 flex justify-end gap-3"><a href="{{ route('manager.dashboard') }}" class="border-2 border-black px-5 py-3 font-black">Cancel</a><button class="border-2 border-black bg-brand-500 px-5 py-3 font-black uppercase">Create Artist</button></div>
        </form>
    </div></div>
</x-app-layout>
