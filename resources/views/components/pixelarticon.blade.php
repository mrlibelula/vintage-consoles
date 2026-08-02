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
        'info-box' => [
            'M4 2h16v2H4zm0 18h16v2H4zM2 4h2v16H2zm18 0h2v16h-2zm-9 5h2V7h-2zm0 8h2v-6h-2z',
        ],
        'save' => [
            'M20 22H4V20H6V14H8V20H16V14H18V20H20V22ZM4 20H2V4H4V20ZM22 20H20V6H22V20ZM16 14H8V12H16V14ZM12 10H6V6H12V10ZM20 6H18V4H20V6ZM18 4H4V2H18V4Z',
        ],
        'square' => [
            'M2 4h2v16H2zm2 16h16v2H4zM20 4h2v16h-2zM4 2h16v2H4z',
        ],
        'gamepad' => [
            'M4 4h16v2H4zm0 14h16v2H4zM2 6h2v12H2zm18 0h2v12h-2zM8 9h2v6H8z',
            'M6 11h6v2H6zm8-2h2v2h-2zm2 4h2v2h-2z',
        ],
        'directions' => [
            'M2 2h2v2H2zm2 2h2v2H4zm2-2h2v2H6zM2 6h2v2H2zm4 0h2v2H6zm11 9h3v2h-3zm-2 2h2v3h-2zm2 3h3v2h-3zm3-3h2v3h-2zM15 2h2v10h-2zm-2 2h2v2h-2z',
            'M11 6h9v2h-9z',
            'M19 6h2v2h-2zm-2-2h2v2h-2zM6 12h9v2H6zm-2 2h2v4H4zm0 6h2v2H4z',
        ],
        'search' => [
            'M22 22h-2v-2h2v2Zm-2-2h-2v-2h2v2Zm-6-2H6v-2h8v2Zm4 0h-2v-2h2v2ZM6 16H4v-2h2v2Zm10 0h-2v-2h2v2ZM4 14H2V6h2v8Zm14 0h-2V6h2v8ZM6 6H4V4h2v2Zm10 0h-2V4h2v2Zm-2-2H6V2h8v2Z',
        ],
        'cancel' => [
            'M6 2h12v2H6zm0 18h12v2H6zM2 6h2v12H2zm18 0h2v12h-2zm-2-2h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2h-2zm-2 2h2v2H8zm-2 2h2v2H6zm12 2h2v2h-2zM4 4h2v2H4zm0 14h2v2H4z',
        ],
        'close' => [
            'M7 19H5V17H7V19ZM19 19H17V17H19V19ZM9 15V17H7V15H9ZM17 17H15V15H17V17ZM11 15H9V13H11V15ZM15 15H13V13H15V15ZM13 13H11V11H13V13ZM11 11H9V9H11V11ZM15 11H13V9H15V11ZM9 9H7V7H9V9ZM17 9H15V7H17V9ZM7 7H5V5H7V7ZM19 7H17V5H19V7Z',
        ],
        'chevron-down' => [
            'M13 16h-2v-2h2v2Zm-2-2H9v-2h2v2Zm4 0h-2v-2h2v2Zm-6-2H7v-2h2v2Zm8 0h-2v-2h2v2ZM7 10H5V8h2v2Zm12 0h-2V8h2v2Z',
        ],
        'moon' => [
            'M18 22H8v-2h10v2ZM8 20H6v-2h2v2Zm12 0h-2v-2h2v2ZM6 18H4v-2h2v2Zm16 0h-2v-4h-2v-2h2v-2h2v8ZM4 16H2V6h2v10Zm14 0h-6v-2h6v2Zm-6-2h-2v-2h2v2Zm-2-2H8V6h2v6ZM6 6H4V4h2v2Zm8-2h-2v2h-2V4H6V2h8v2Z',
        ],
        'sun' => [
            'M11 0h2v3h-2zm0 21h2v3h-2zM0 11h3v2H0zm21 0h3v2h-3zM3 3h2v2H3zm16 0h2v2h-2zM3 19h2v2H3zm16 0h2v2h-2zM8 6h8v2H8zM6 8h2v8H6zm10 0h2v8h-2zM8 16h8v2H8z',
        ],
        'computer' => [
            'M6 1h12v2H6zm0 8h12v2H6zM4 3h2v6H4zm14 0h2v6h-2zM4 13h16v2H4zm0 8h16v2H4zm-2-6h2v6H2zm18 0h2v6h-2zM6 17h2v2H6zm4 0h8v2h-8zm-2-6h2v2H8zm6 0h2v2h-2z',
        ],
        'user' => [
            'M9 2h6v2H9zm0 8h6v2H9zm6-6h2v6h-2zM7 4h2v6H7zM4 18h2v4H4zm14 0h2v4h-2zM8 14h8v2H8zm-2 2h2v2H6zm10 0h2v2h-2z',
        ],
        'logout' => [
            'M8 11h12v2H8zm8-2h2v2h-2z',
            'M14 7h2v10h-2zm2 6h2v2h-2zM6 2h12v2H6zm0 18h12v2H6zM4 4h2v16H4zm14 0h2v3h-2zm0 13h2v3h-2z',
        ],
        'login' => [
            'M2 11h14v2H2zm10-2h2v2h-2z',
            'M10 7h2v10h-2zm2 6h2v2h-2zM6 2h12v2H6zm0 18h12v2H6zM4 4h2v5H4zm0 11h2v5H4zM18 4h2v16h-2z',
        ],
        'cursor' => [
            'M6 4h2v16H6zm2 0h2v2H8zm2 2h2v2h-2zm2 2h2v2h-2zm2 2h2v2h-2zm2 2h2v2h-2zm-8 6h2v2H8zm2-2h2v2h-2zm2-2h6v2h-6z',
        ],
        'archive' => [
            'M3 2h18v2H3zm0 5h18v2H3zM1 4h2v3H1zm20 0h2v3h-2zm-2 5h2v11h-2zM3 9h2v11H3zm2 11h14v2H5zm4-9h6v2H9z',
        ],
        'font' => [
            'M12 6h10v2H12zm0 4h10v2H12zM2 14h20v2H2zm0 4h20v2H2zM2 6h2v6H2zm6 0h2v6H8zM4 4h4v2H4zm0 4h4v2H4z',
        ],
        'script' => [
            'M16 19h2v2H4v-2h10v-2h2v2ZM6 15h8v2H4v2H2v-4h2V5h2v10ZM20 5h2v6h-2v8h-2V5H6V3h14v2Z',
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
