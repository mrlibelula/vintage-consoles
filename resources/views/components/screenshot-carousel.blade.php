@props([
    'screenshots',
    'gameTitle' => 'Game',
    'layout' => 'admin',
])

@php
    $isPlayerLayout = $layout === 'player';
    $carouselClass = $isPlayerLayout ? 'screenshot-carousel--player' : 'screenshot-carousel--admin';
    $slidesPerView = $isPlayerLayout ? '1.15' : '2';
    $slideButtonClass = $isPlayerLayout
        ? 'aspect-video w-full overflow-hidden rounded-md border border-cod-gray-300/70 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:border-cod-gray-600/80'
        : 'h-24 w-[10rem] overflow-hidden rounded-md border border-cod-gray-300/70 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:border-cod-gray-600/80';
@endphp

<div {{ $attributes->class(['screenshot-carousel w-full overflow-hidden pb-6', $carouselClass]) }}>
    <swiper-container
        class="screenshot-swiper block w-full [--swiper-navigation-color:#374151] [--swiper-navigation-size:26px] dark:[--swiper-navigation-color:#e5e7eb]"
        direction="horizontal"
        navigation="true"
        grab-cursor="true"
        keyboard="true"
        space-between="12"
        slides-per-view="{{ $slidesPerView }}"
        free-mode="true"
        speed="500"
        @if($isPlayerLayout)
        breakpoints='{
            "640": {
                "slidesPerView": 1.25,
                "spaceBetween": 12
            },
            "768": {
                "slidesPerView": 1.5,
                "spaceBetween": 16
            },
            "1024": {
                "slidesPerView": 2,
                "spaceBetween": 16
            }
        }'
        @else
        breakpoints='{
            "640": {
                "slidesPerView": 2,
                "spaceBetween": 12
            },
            "768": {
                "slidesPerView": 3,
                "spaceBetween": 16
            },
            "1024": {
                "slidesPerView": 4,
                "spaceBetween": 16
            }
        }'
        @endif
    >
        @foreach ($screenshots as $index => $shot)
            <swiper-slide class="screenshot-swiper-slide">
                <button
                    type="button"
                    @click="openAt({{ $index }})"
                    class="{{ $slideButtonClass }}"
                    aria-label="Open {{ $gameTitle }} screenshot {{ $loop->iteration }}"
                >
                    <img
                        src="{{ $shot->thumb_url }}"
                        alt="{{ $gameTitle }} screenshot {{ $loop->iteration }}"
                        class="h-full w-full object-cover hover:brightness-110 smooth-300 [image-rendering:crisp-edges]"
                        loading="lazy"
                        decoding="async"
                    >
                </button>
            </swiper-slide>
        @endforeach
    </swiper-container>
</div>
