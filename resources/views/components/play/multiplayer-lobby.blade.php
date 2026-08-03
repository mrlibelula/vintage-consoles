@props([
    'game',
])

@php
    $seats = [
        ['id' => 'P1', 'label' => 'Host', 'role' => 'host'],
        ['id' => 'P2', 'label' => 'Open', 'role' => 'guest'],
        ['id' => 'P3', 'label' => 'Open', 'role' => 'guest'],
        ['id' => 'P4', 'label' => 'Open', 'role' => 'guest'],
    ];
@endphp

<div
    id="play-multiplayer"
    {{ $attributes->class([
        'play-multiplayer-lobby relative z-0 mt-[var(--play-stack-gap,0.375rem)] shrink-0 max-play:mx-3',
    ]) }}
    aria-disabled="true"
>
    <div class="overflow-hidden rounded-md border border-cod-gray-400 bg-[#d0d2d8] shadow-sm shadow-cod-gray-500/15 dark:border-cod-gray-700 dark:bg-cod-gray-900/50 dark:shadow-black/40">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-x-2 border-b border-cod-gray-400/80 px-2 py-1.5 dark:border-cod-gray-700">
            <div class="flex min-w-0 items-center gap-x-1.5">
                <span class="shrink-0 text-rose-600 dark:text-rose-400" aria-hidden="true">
                    {{-- Linked twin pads — split-screen vintage vibe --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" class="block">
                        <path d="M2 8h8v2H2zm0 6h8v2H2zM1 10h2v4H1zm9 0h2v4h-2zM4 11h2v2H4zm3-1h2v1H7zm0 3h2v1H7z"/>
                        <path d="M14 8h8v2h-8zm0 6h8v2h-8zm-1 2h2v4h-2zm9 0h2v4h-2zm-6 1h2v2h-2zm3-1h2v1h-2zm0 3h2v1h-2z"/>
                        <path d="M10 11h4v2h-4z" opacity=".55"/>
                        <path d="M11 7h2v1h-2zm0 9h2v1h-2z" opacity=".4"/>
                    </svg>
                </span>
                <span class="truncate leading-none text-cod-gray-800 dark:text-cod-gray-200">
                    Multiplayer
                </span>
                <span class="truncate leading-none text-cod-gray-500 dark:text-cod-gray-500">
                    · {{ $game->title }}
                </span>
            </div>
            <button
                type="button"
                disabled
                class="inline-flex shrink-0 cursor-not-allowed items-center gap-x-1 rounded-md bg-emerald-600/20 px-2 py-1 leading-none text-emerald-700 opacity-80 dark:bg-emerald-500/20 dark:text-emerald-400"
                aria-label="Join lobby"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="block" aria-hidden="true">
                    <path d="M5 9h14v2H5zm0 6h14v2H5zM4 11h2v4H4zm14 0h2v4h-2zM8 12h2v2H8zm5-1h2v1h-2zm0 3h2v1h-2z"/>
                </svg>
                Join lobby
            </button>
        </div>

        {{-- Arcade table + seats --}}
        <div class="px-2 py-2.5 sm:px-3">
            <div class="relative w-full">
                {{-- LAN chassis HUD — cut-corner plate + seat ports + crawling bus --}}
                <svg
                    class="pointer-events-none absolute inset-x-0 top-1/2 z-0 h-16 w-full -translate-y-1/2 text-cod-gray-400 dark:text-cod-gray-700"
                    viewBox="0 0 280 64"
                    preserveAspectRatio="none"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <defs>
                        <pattern id="mp-chassis-hatch" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                            <line x1="0" y1="0" x2="0" y2="6" stroke="currentColor" stroke-width="1" opacity=".18"/>
                        </pattern>
                    </defs>

                    {{-- Outer beveled chassis (scoreboard plate, not a soft oval) --}}
                    <path
                        d="M8 10 L20 2 H260 L272 10 V54 L260 62 H20 L8 54 Z"
                        fill="url(#mp-chassis-hatch)"
                        stroke="currentColor"
                        stroke-width="1.75"
                        opacity=".55"
                    />
                    <path
                        d="M16 14 L24 8 H256 L264 14 V50 L256 56 H24 L16 50 Z"
                        stroke="currentColor"
                        stroke-width="1"
                        opacity=".35"
                        stroke-dasharray="3 3"
                    />

                    {{-- HUD corner brackets --}}
                    <path d="M12 18 V12 H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" opacity=".7"/>
                    <path d="M268 18 V12 H258" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" opacity=".7"/>
                    <path d="M12 46 V52 H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" opacity=".7"/>
                    <path d="M268 46 V52 H258" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" opacity=".7"/>

                    {{-- Data bus across seats (P1→P4) with pixel jogs --}}
                    <path
                        d="M35 32 H62 M62 32 l4-5 4 10 4-10 4 5 H105 M105 32 H140 M140 32 H175 M175 32 l4-5 4 10 4-10 4 5 H245"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="square"
                        opacity=".4"
                    />
                    <path
                        class="text-rose-600/70 dark:text-rose-400/55"
                        d="M35 32 H245"
                        stroke="currentColor"
                        stroke-width="1.25"
                        stroke-linecap="square"
                        stroke-dasharray="5 7"
                        opacity=".85"
                    >
                        <animate attributeName="stroke-dashoffset" values="0;-48" dur="2.4s" repeatCount="indefinite"/>
                    </path>

                    {{-- Seat port diamonds (aligned under P1–P4 column centers) --}}
                    @foreach ([35, 105, 175, 245] as $i => $cx)
                        <g transform="translate({{ $cx }} 32)" opacity="{{ $i === 0 ? '.9' : '.55' }}">
                            <path
                                d="M0 -7 L7 0 L0 7 L-7 0 Z"
                                class="{{ $i === 0 ? 'text-rose-600 dark:text-rose-400' : '' }}"
                                stroke="currentColor"
                                stroke-width="1.5"
                                fill="{{ $i === 0 ? 'currentColor' : 'none' }}"
                                fill-opacity="{{ $i === 0 ? '.22' : '0' }}"
                            />
                            <rect x="-2" y="-2" width="4" height="4" fill="currentColor" opacity=".55"/>
                        </g>
                    @endforeach

                    {{-- Central switch / hub chip --}}
                    <rect x="128" y="24" width="24" height="16" rx="1" stroke="currentColor" stroke-width="1.25" fill="currentColor" fill-opacity=".08" opacity=".65"/>
                    <path d="M134 28h4M142 28h4M150 28h4M134 36h4M142 36h4M150 36h4" stroke="currentColor" stroke-width="1.25" stroke-linecap="square" opacity=".45"/>
                    <path d="M140 20v4M140 40v4" stroke="currentColor" stroke-width="1" opacity=".35"/>
                </svg>

                <div class="relative z-10 flex w-full items-stretch justify-between gap-x-3 sm:gap-x-5" role="list" aria-label="Player seats">
                    @foreach ($seats as $seat)
                        <div
                            role="listitem"
                            class="flex w-[21%] min-w-[4.5rem] max-w-[7.5rem] flex-col items-center gap-y-1 rounded-md border border-dashed border-cod-gray-500 bg-cod-gray-200 px-1.5 py-2.5 dark:border-cod-gray-600 dark:bg-cod-gray-900"
                            title="{{ $seat['id'] }} — {{ $seat['label'] }} (unavailable)"
                        >
                            <span class="leading-none tabular-nums text-cod-gray-500 dark:text-cod-gray-500">
                                {{ $seat['id'] }}
                            </span>
                            <span class="flex h-10 w-10 items-center justify-center text-cod-gray-400 dark:text-cod-gray-600" aria-hidden="true">
                                @if ($seat['role'] === 'host')
                                    {{-- Crown + empty pad for host seat --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" class="block">
                                        <path d="M4 10h2l2-4 4 5 4-5 2 4h2v2H4zm0 4h16v2H4zm2 2h12v2H6z" opacity=".85"/>
                                        <path d="M8 6h2v2H8zm6 0h2v2h-2zM11 4h2v2h-2z" opacity=".5"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" class="block opacity-80">
                                        <path d="M5 9h14v2H5zm0 6h14v2H5zM4 11h2v4H4zm14 0h2v4h-2zM8 12h2v2H8zm5-1h2v1h-2zm0 3h2v1h-2z"/>
                                        <path d="M10 7h4v1h-4z" opacity=".35"/>
                                    </svg>
                                @endif
                            </span>
                            <span class="leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                {{ $seat['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Lobby list --}}
        <div class="border-t border-cod-gray-400/80 dark:border-cod-gray-700">
            <div class="flex items-center justify-between gap-x-2 px-2 py-1.5">
                <span class="leading-none text-cod-gray-600 dark:text-cod-gray-400">Open rooms</span>
                <span class="leading-none tabular-nums text-cod-gray-500 dark:text-cod-gray-500">0 online</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[18rem] border-collapse text-left">
                    <thead>
                        <tr class="border-y border-cod-gray-400/80 text-cod-gray-500 dark:border-cod-gray-700 dark:text-cod-gray-500">
                            <th scope="col" class="px-2 py-1.5 font-normal leading-none">
                                <span class="inline-flex items-center gap-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="block shrink-0 opacity-80" aria-hidden="true">
                                        <path d="M4 3h12v2H4zm0 16h12v2H4zM2 5h2v14H2zm14 0h2v6h-2zm0 8h2v6h-2z"/>
                                        <path d="M8 11h2v4H8z" opacity=".7"/>
                                        <path d="M16 9h4v2h-4zm2-2h2v2h-2zm0 4h2v2h-2z" opacity=".55"/>
                                    </svg>
                                    Room
                                </span>
                            </th>
                            <th scope="col" class="px-2 py-1.5 font-normal leading-none">
                                <span class="inline-flex items-center gap-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="block shrink-0 opacity-80" aria-hidden="true">
                                        <path d="M4 10h2l2-4 4 5 4-5 2 4h2v2H4zm0 4h16v2H4z"/>
                                    </svg>
                                    Host
                                </span>
                            </th>
                            <th scope="col" class="px-2 py-1.5 font-normal leading-none tabular-nums">
                                <span class="inline-flex items-center gap-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="block shrink-0 opacity-80" aria-hidden="true">
                                        <path d="M5 9h14v2H5zm0 6h14v2H5zM4 11h2v4H4zm14 0h2v4h-2zM8 12h2v2H8zm5-1h2v1h-2zm0 3h2v1h-2z"/>
                                    </svg>
                                    Players
                                </span>
                            </th>
                            <th scope="col" class="px-2 py-1.5 font-normal leading-none text-right"> </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="px-2 py-6 text-center text-cod-gray-500 dark:text-cod-gray-500">
                                <div class="mx-auto flex max-w-xs flex-col items-center gap-y-3">
                                    {{-- Awaiting rooms: door + outbound pings + blinking dots --}}
                                    <span class="text-cod-gray-400 dark:text-cod-gray-600" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="112" height="112" viewBox="0 0 40 40" fill="currentColor" class="block">
                                            {{-- Door frame --}}
                                            <path d="M10 6h14v2H10zm0 26h14v2H10zM8 8h2v24H8zm16 0h2v10h-2zm0 14h2v10h-2z"/>
                                            {{-- Door panel lines --}}
                                            <path d="M12 10h10v2H12zm0 4h10v2H12zm0 4h6v2h-6zm0 4h10v2H12zm0 4h8v2h-8z" opacity=".35"/>
                                            {{-- Knob --}}
                                            <path d="M20 20h2v4h-2z" opacity=".75">
                                                <animate attributeName="opacity" values=".45;.9;.45" dur="1.6s" repeatCount="indefinite"/>
                                            </path>
                                            {{-- Outbound search pings (pixel steps right of door) --}}
                                            <g class="text-rose-600/70 dark:text-rose-400/55">
                                                <path d="M28 16h2v2h-2zm0 6h2v2h-2z" fill="currentColor" opacity="0">
                                                    <animate attributeName="opacity" values="0;.85;0;0" keyTimes="0;.2;.4;1" dur="2.4s" repeatCount="indefinite"/>
                                                </path>
                                                <path d="M32 14h2v2h-2zm0 4h2v2h-2zm0 4h2v2h-2zm0 4h2v2h-2z" fill="currentColor" opacity="0">
                                                    <animate attributeName="opacity" values="0;0;.7;0;0" keyTimes="0;.2;.4;.6;1" dur="2.4s" repeatCount="indefinite"/>
                                                </path>
                                                <path d="M36 12h2v2h-2zm0 4h2v2h-2zm0 4h2v2h-2zm0 4h2v2h-2zm0 4h2v2h-2z" fill="currentColor" opacity="0">
                                                    <animate attributeName="opacity" values="0;0;0;.55;0" keyTimes="0;.35;.55;.75;1" dur="2.4s" repeatCount="indefinite"/>
                                                </path>
                                            </g>
                                            {{-- Waiting dots under door --}}
                                            <g>
                                                <rect x="14" y="36" width="2" height="2" opacity="0.25">
                                                    <animate attributeName="opacity" values=".25;1;.25;.25" keyTimes="0;.2;.4;1" dur="1.5s" repeatCount="indefinite"/>
                                                </rect>
                                                <rect x="19" y="36" width="2" height="2" opacity="0.25">
                                                    <animate attributeName="opacity" values=".25;.25;1;.25" keyTimes="0;.2;.5;1" dur="1.5s" repeatCount="indefinite"/>
                                                </rect>
                                                <rect x="24" y="36" width="2" height="2" opacity="0.25">
                                                    <animate attributeName="opacity" values=".25;.25;.25;1;.25" keyTimes="0;.35;.55;.75;1" dur="1.5s" repeatCount="indefinite"/>
                                                </rect>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="leading-tight">No open rooms yet.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer actions (disabled for everyone) --}}
        <div class="flex flex-col gap-y-1.5 border-t border-cod-gray-400/80 px-2 py-2 dark:border-cod-gray-700 sm:flex-row sm:items-center sm:justify-end sm:gap-x-3">
            <div class="flex shrink-0 items-center gap-x-1.5">
                <button
                    type="button"
                    disabled
                    class="inline-flex cursor-not-allowed items-center gap-x-1 rounded-md border border-cod-gray-500 px-2.5 py-1.5 leading-none text-cod-gray-500 opacity-60 dark:border-cod-gray-600 dark:text-cod-gray-400"
                    aria-label="Host table"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="block" aria-hidden="true">
                        <path d="M4 10h2l2-4 4 5 4-5 2 4h2v2H4zm0 4h16v2H4z"/>
                    </svg>
                    Host table
                </button>
                <button
                    type="button"
                    disabled
                    class="inline-flex cursor-not-allowed items-center gap-x-1 rounded-md bg-emerald-600/20 px-2.5 py-1.5 leading-none text-emerald-700 opacity-80 dark:bg-emerald-500/20 dark:text-emerald-400"
                    aria-label="Create room"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="block" aria-hidden="true">
                        <path d="M11 5h2v14h-2zM5 11h14v2H5z"/>
                    </svg>
                    Create room
                </button>
            </div>
        </div>
    </div>
</div>
