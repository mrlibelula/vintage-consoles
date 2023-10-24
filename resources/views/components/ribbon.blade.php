<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <div class="flex py-4 overflow-hidden overflow-x-auto flex-col">
        <div class="flex flex-no-wrap gap-x-4">
            {{ $slot }}
        </div>
    </div>
</div>