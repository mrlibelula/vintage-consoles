@props(['games' => []])

@if (! empty($games))
<div wire:ignore class="similar-games-carousel w-full overflow-hidden">
    <swiper-container
        class="block w-full"
        slides-per-view="auto"
        space-between="10"
        free-mode="true"
        grab-cursor="true"
        prevent-clicks="false"
        threshold="5"
    >
        @foreach ($games as $item)
            <swiper-slide class="similar-games-carousel__slide">
                {{-- Full reload: wire:navigate Play→Play leaves the ignored emulator dock stale. --}}
                <a
                    href="{{ $item['url'] }}"
                    @click.prevent="$dispatch('loader-top-on'); window.location.assign($el.href)"
                    class="group flex h-full w-full min-w-0 flex-col gap-y-1 no-underline"
                >
                    <div class="relative aspect-[3/4] w-full overflow-hidden rounded-md bg-cod-gray-900">
                        @if (! empty($item['poster']))
                            <img
                                src="{{ $item['poster'] }}"
                                alt="{{ $item['title'] }}"
                                class="h-full w-full object-cover opacity-90 group-hover:opacity-100"
                                loading="lazy"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center px-2 text-center text-xs text-cod-gray-500">
                                {{ $item['console'] ?? '' }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 w-full">
                        <div class="truncate text-base text-cod-gray-800 dark:text-cod-gray-300" title="{{ $item['title'] }}">
                            {{ $item['title'] }}
                        </div>
                        @if (! empty($item['console']))
                        <div class="truncate text-sm uppercase tracking-wide text-cod-gray-500 dark:text-cod-gray-500">
                            {{ $item['console'] }}
                        </div>
                        @endif
                    </div>
                </a>
            </swiper-slide>
        @endforeach
    </swiper-container>
</div>
@endif
