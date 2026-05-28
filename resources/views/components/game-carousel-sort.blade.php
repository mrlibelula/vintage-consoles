@props([
    'sortField' => 'rating',
    'sortDirection' => 'desc',
    'activeClass' => 'pixel-icon-rose',
])

@php
    $titleActive = $sortField === 'title';
    $ratingActive = $sortField === 'rating';
    $titleDirectionLabel = $titleActive && $sortDirection === 'desc' ? 'descending' : 'ascending';
    $ratingDirectionLabel = $ratingActive && $sortDirection === 'desc' ? 'descending' : 'ascending';
    $titleTooltip = $titleActive && $sortDirection === 'desc'
        ? 'Sort Z to A'
        : 'Sort A to Z';
    $ratingTooltip = $ratingActive && $sortDirection === 'desc'
        ? 'Lowest rating first'
        : 'Highest rating first';
@endphp

<div {{ $attributes->class(['flex items-center gap-x-3 leading-none']) }} role="group" aria-label="Sort games">
    <button
        type="button"
        wire:click="sortCarouselBy('title')"
        @click="$dispatch('loader-top-on')"
        class="btn-pixel pixel-tooltip"
        data-pixel-tooltip="{{ $titleTooltip }}"
        aria-label="Sort by title, {{ $titleDirectionLabel }}"
        aria-pressed="{{ $titleActive ? 'true' : 'false' }}"
    >
        <x-pixelarticon
            :name="$titleActive && $sortDirection === 'desc' ? 'arrow-down-z-a' : 'arrow-up-z-a'"
            :size="24"
            @class([
                $activeClass => $titleActive,
            ])
        />
    </button>
    <button
        type="button"
        wire:click="sortCarouselBy('rating')"
        @click="$dispatch('loader-top-on')"
        class="btn-pixel pixel-tooltip"
        data-pixel-tooltip="{{ $ratingTooltip }}"
        aria-label="Sort by rating, {{ $ratingDirectionLabel }}"
        aria-pressed="{{ $ratingActive ? 'true' : 'false' }}"
    >
        <x-pixelarticon
            name="diamond-gem"
            :size="24"
            @class([
                $activeClass => $ratingActive,
                'rotate-180 transition-transform duration-300 ease-in-out' => $ratingActive && $sortDirection === 'asc',
            ])
        />
    </button>
</div>
