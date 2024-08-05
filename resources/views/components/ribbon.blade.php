@props(['id' => '', 'draggable' => true])
<div {{ $attributes->merge(['class' => 'flex flex-col overflow-hidden w-full']) }}>
    
    <div class="relative">
        <div 
            id="scrollContainer-{{ $id }}"
            class="{{ $draggable ? 'ribbon-container cursor-grab' : '' }} flex items-center overflow-hidden overflow-x-auto select-none"
            tabindex="0"
        >
            <div class="flex flex-no-wrap py-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    
</div>

{{-- <div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <div class="flex py-4 overflow-hidden overflow-x-auto flex-col select-none draggable-ribbon">
        <div class="flex flex-no-wrap gap-x-4">
            {{ $slot }}
        </div>
    </div>
</div> --}}