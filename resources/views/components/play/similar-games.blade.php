@props(['games' => []])

@if (! empty($games))
<div class="w-full">
    <swiper-container slides-per-view="auto" space-between="10" free-mode="true" grab-cursor="true" class="w-full">
        @foreach ($games as $item)
            <swiper-slide style="width: 7.5rem">
                <a
                    href="{{ $item['url'] }}"
                    wire:navigate
                    class="group flex flex-col gap-y-1 no-underline"
                >
                    <div class="relative overflow-hidden rounded-md bg-cod-gray-900 aspect-[3/4]">
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
                    <div class="min-w-0">
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
