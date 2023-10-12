<div class="leading-tight bg-gradient-to-b from-cod-gray-800 via-cod-gray-700/60 to-cod-gray-900 w-full pt-5 rounded-b-md h-[44vh]">
    <div {{ $attributes->merge(['class' => 'h-full px-4 overflow-hidden overflow-y-auto']) }}>
        <!-- main content -->
        {{ $slot }}
    </div>
</div>