<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-cod-gray-800 dark:text-cod-gray-200 leading-tight">
            {{ __('API Tokens') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-5xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('api.api-token-manager')
        </div>
    </div>
</x-app-layout>
