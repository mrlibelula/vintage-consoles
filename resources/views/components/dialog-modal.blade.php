@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg font-medium text-cod-gray-900 dark:text-cod-gray-100">
            {{ $title }}
        </div>

        <div class="mt-4 text-2xl text-cod-gray-600 dark:text-cod-gray-400">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 bg-cod-gray-100 dark:bg-cod-gray-700 text-right">
        {{ $footer }}
    </div>
</x-modal>
