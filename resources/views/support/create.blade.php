<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-black">Contact Support</h2></x-slot>
    <div class="px-6 py-8">
        <div class="mx-auto max-w-3xl">
            @if(session('success'))
                <div role="alert" class="mb-6 border-2 border-black bg-emerald-300 px-4 py-3 font-bold">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div role="alert" class="mb-6 border-2 border-black bg-red-100 px-4 py-3 font-bold">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('support.store') }}" class="space-y-5 border-2 border-black bg-white p-6 shadow-[5px_5px_0_#000]">
                @csrf
                <div>
                    <x-input-label for="subject" value="Subject" />
                    <x-text-input id="subject" name="subject" class="mt-1 block w-full" :value="old('subject')" required maxlength="200" />
                </div>
                <div>
                    <x-input-label for="message" value="Message" />
                    <textarea id="message" name="message" rows="8" maxlength="5000" required class="mt-1 block w-full border-2 border-black px-3 py-2.5 font-semibold focus:border-brand-500 focus:ring-0">{{ old('message') }}</textarea>
                </div>
                <button class="border-2 border-black bg-brand-500 px-6 py-3 font-black uppercase shadow-[3px_3px_0_#000]">Send Message</button>
            </form>
        </div>
    </div>
</x-app-layout>
