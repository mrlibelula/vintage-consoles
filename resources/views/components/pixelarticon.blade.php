@props([
    'name',
    'size' => 24,
])

@php
    $paths = match ($name) {
        'gallery-thumbnails' => [
            'M4 2h16v2H4zM2 4h2v12H2zm2 12h16v2H4zM20 4h2v12h-2zM3 20h3v2H3zm5 0h3v2H8zm5 0h3v2h-3zm5 0h3v2h-3z',
        ],
        'grid-2x2-2' => [
            'M4 2h16v2H4zM2 4h2v16H2zm2 7h16v2H4zm16-7h2v16h-2z',
            'M11 4h2v18h-2z',
            'M4 20h16v2H4z',
        ],
        'bulletlist' => [
            'M10 5h12v2H10zm0 4h8v2h-8zm0 4h12v2H10zm0 4h8v2h-8zm-4-6H4V9h2v2ZM4 9H2V7h2v2Zm4 0H6V7h2v2ZM6 7H4V5h2v2Zm-2 6h2v2H4zm0 4h2v2H4zm-2 0v-2h2v2zm4 0v-2h2v2z',
        ],
        'arrow-down-z-a' => [
            'M14 3h7v2h-7zm0 6h7v2h-7zm2-2h2v2h-2zm2-2h2v2h-2zM6 3h2v18H6z',
            'M4 17h6v2H4zm-2-2h10v2H2zm12 0h2v6h-2zm2-2h3v2h-3zm3 2h2v6h-2zm-3 2h3v2h-3z',
        ],
        'arrow-up-z-a' => [
            'M14 9h7v2h-7zm0-6h7v2h-7zm2 4h2v2h-2zm2-2h2v2h-2zM6 21h2V3H6z',
            'M4 7h6V5H4zM2 9h10V7H2zm12 6h2v6h-2zm2-2h3v2h-3zm3 2h2v6h-2zm-3 2h3v2h-3z',
        ],
        'diamond-gem' => [
            'M7 1h10v2H7zM5 3h2v2H5zm12 0h2v2h-2zm2 2h2v2h-2zm0 8h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2h-2zm-2-2h2v2H9zm-2-2h2v2H7zm-2-2h2v2H5zm-2-2h2v2H3zm0-8h2v2H3zM1 7h2v6H1zm20 0h2v6h-2zM3 9h18v2H3zm6-6h2v3H9zM7 6h2v3H7zm8 0h2v3h-2zm-8 5h2v2H7zm2 2h2v3H9zm2 3h2v3h-2zm2-3h2v3h-2zm2-2h2v2h-2zm-2-8h2v3h-2z',
        ],
        'square' => [
            'M2 4h2v16H2zm2 16h16v2H4zM20 4h2v16h-2zM4 2h16v2H4z',
        ],
        default => null,
    };
@endphp

@if ($paths)
    <svg
        {{ $attributes->class('shrink-0') }}
        xmlns="http://www.w3.org/2000/svg"
        width="{{ $size }}"
        height="{{ $size }}"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
    >
        @foreach ($paths as $path)
            <path d="{{ $path }}" />
        @endforeach
    </svg>
@endif
