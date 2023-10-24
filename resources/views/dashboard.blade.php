<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-cod-gray-800 dark:text-cod-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-cod-gray-100/60 dark:bg-cod-gray-900 overflow-hidden shadow-xl sm:rounded-lg">
                <x-welcome />
            </div>
        </div>
    </div>
</x-app-layout>
