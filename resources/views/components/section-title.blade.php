<div class="md:col-span-1 flex justify-between sepia_">
    <div class="px-4 sm:px-0">
        <h3 class="text-2xl text-cod-gray-900 dark:text-cod-gray-100">{{ $title }}</h3>

        <p class="mt-1 text-2xl text-cod-gray-600 dark:text-cod-gray-400">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
