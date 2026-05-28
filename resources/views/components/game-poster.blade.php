@props([
    'src' => null,
    'alt' => '',
    'containerClass' => 'h-[7.3rem] w-[5rem]',
    'imageClass' => 'object-cover',
    'showPlaceholder' => true,
    'loadingTargets' => 'previousPage,nextPage,gotoPage,setPage',
])

@php
    $containerClasses = trim('relative flex-shrink-0 overflow-hidden rounded ' . $containerClass);
@endphp

<div
    {{ $attributes->class([$containerClasses]) }}
    x-data="{
        imageLoaded: {{ $src ? 'false' : 'true' }},
        imageFailed: false,
        checkImage() {
            const image = this.$refs.poster;

            if (!image) {
                return;
            }

            if (image.complete && image.naturalWidth > 0) {
                this.imageLoaded = true;
            }
        },
    }"
    x-init="checkImage()"
>
    @if ($loadingTargets)
        <div
            wire:loading.delay
            wire:target="{{ $loadingTargets }}"
            class="absolute inset-0 z-10 skeleton rounded"
            aria-hidden="true"
        ></div>
    @endif

    <div
        x-show="!imageLoaded && !imageFailed"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 z-10 skeleton rounded"
        aria-hidden="true"
    ></div>

    @if ($src)
        <img
            x-ref="poster"
            src="{{ $src }}"
            alt="{{ $alt }}"
            class="h-full w-full rounded {{ $imageClass }} transition-opacity duration-200"
            :class="imageLoaded ? 'opacity-100' : 'opacity-0'"
            loading="lazy"
            decoding="async"
            @@load="imageLoaded = true"
            @@error="imageFailed = true; imageLoaded = true"
        >
    @elseif ($showPlaceholder)
        <div class="flex h-full w-full items-center justify-center rounded bg-cod-gray-300 dark:bg-cod-gray-600">
            <svg class="h-8 w-8 text-cod-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
    @endif
</div>
