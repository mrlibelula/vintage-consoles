<div {{ $attributes->merge(['class' => 'flex items-center justify-center w-full']) }}>
    <div class="max-w-[85rem] w-full h-full min-h-0 px-4 xl:px-6 lg:px-8">
        {{ $slot }}
    </div>
</div>