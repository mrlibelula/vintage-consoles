@props([
    'rating' => null,
    'diamonds' => 5,
    'size' => 14,
])

@php
    $score = $rating !== null ? max(0, min(100, (int) round((float) $rating * 100))) : null;
    $ratingValue = $score !== null
        ? max(0, min((float) $diamonds, (float) $rating * (float) $diamonds))
        : null;
@endphp

@if ($ratingValue !== null)
    <div
        {{ $attributes->class(['pointer-events-none inline-flex']) }}
        role="img"
        aria-label="Rated {{ $score }} out of 100"
    >
        <div class="game-card-rating-badge inline-flex items-center gap-px rounded bg-gray-200/60 dark:bg-black/50 px-1 py-1">
            @for ($index = 0; $index < $diamonds; $index++)
                @php
                    $fill = min(1, max(0, $ratingValue - $index));
                    $state = $fill >= 1 ? 'full' : ($fill >= 0.5 ? 'half' : 'empty');
                @endphp

                <div
                    class="relative shrink-0"
                    style="width: {{ $size }}px; height: {{ $size }}px;"
                >
                    @if ($state === 'full')
                        <x-pixelarticon
                            name="diamond-gem"
                            :size="$size"
                            class="pixel-icon-rose"
                        />
                    @elseif ($state === 'half')
                        <x-pixelarticon
                            name="diamond-gem"
                            :size="$size"
                            class="pixel-icon-rose-faint"
                        />
                        <div class="absolute inset-y-0 left-0 w-1/2 overflow-hidden">
                            <x-pixelarticon
                                name="diamond-gem"
                                :size="$size"
                                class="pixel-icon-rose"
                            />
                        </div>
                    @else
                        <x-pixelarticon
                            name="diamond-gem"
                            :size="$size"
                            class="pixel-icon-rose-faint"
                        />
                    @endif
                </div>
            @endfor
        </div>
    </div>
@endif
