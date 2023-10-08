<div {{ $attributes->merge(['class' => 'flex items-center justify-center w-full']) }}>
    <div class=" max-w-5xl w-full px-4 xl:px-6 lg:px-8">
        {{ $slot }}
    </div>
</div>