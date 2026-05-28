<div>
  <x-container class="py-8 sm:py-12">

    {{-- ───────────────────────────── Page Header ───────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8 sm:mb-10">
        <div class="flex items-center gap-3">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img
            class="w-12 h-12 rounded-full object-cover border-2 border-cod-gray-700 dark:border-cod-gray-800"
            src="{{ Auth::user()->profile_photo_url }}"
            alt="{{ Auth::user()->name }}"
          >
        @else
          <div class="w-12 h-12 rounded-full bg-cod-gray-700 dark:bg-cod-gray-800 flex items-center justify-center text-2xl text-cod-gray-300">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
          </div>
        @endif

        <div>
          <h1 class="text-2xl sm:2 text-cod-gray-800 dark:text-cod-gray-100 leading-none">
            My Saves
          </h1>
          <p class="text-base sm:text-xl text-cod-gray-500 dark:text-cod-gray-500 leading-tight mt-0.5">
            {{ Auth::user()->name }}
          </p>
        </div>
      </div>

      {{-- Stats chips + global upload CTA --}}
      <div class="flex flex-wrap items-center gap-2 sm:ml-auto">
        <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-md bg-cod-gray-300/60 dark:bg-cod-gray-800/80 border border-cod-gray-400/40 dark:border-cod-gray-700 text-base sm:text-xl text-cod-gray-700 dark:text-cod-gray-300">
          <i class="fa fa-save text-rose-500 text-sm sm:text-base"></i>
          {{ $totalSlots }} {{ Str::plural('slot', $totalSlots) }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-md bg-cod-gray-300/60 dark:bg-cod-gray-800/80 border border-cod-gray-400/40 dark:border-cod-gray-700 text-base sm:text-xl text-cod-gray-700 dark:text-cod-gray-300">
          <i class="fa fa-gamepad text-rose-500 text-sm sm:text-base"></i>
          {{ $totalGames }} {{ Str::plural('game', $totalGames) }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-md bg-cod-gray-300/60 dark:bg-cod-gray-800/80 border border-cod-gray-400/40 dark:border-cod-gray-700 text-base sm:text-xl text-cod-gray-700 dark:text-cod-gray-300">
          <i class="fa fa-television text-rose-500 text-sm sm:text-base"></i>
          {{ $totalConsoles }} {{ Str::plural('console', $totalConsoles) }}
        </span>

        <button
          type="button"
          wire:click="syncFromDisk"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-md border border-cod-gray-400/40 dark:border-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-500 dark:hover:text-rose-400 hover:border-rose-400/50 dark:hover:border-rose-600/50 smooth-300"
          title="Scan storage for save states that exist on disk but are missing from the database"
        >
          <i class="fa fa-refresh text-sm sm:text-base"></i>
          Sync
        </button>

        <button
          type="button"
          wire:click="openGlobalUploadModal"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white text-base sm:text-xl smooth-300 shadow-sm"
          title="Upload a .state file for any game on any slot"
        >
          <i class="fa fa-cloud-upload text-sm sm:text-base"></i>
          Upload Save State
        </button>
      </div>
    </div>

    @if ($grouped)
      @php
        $displayGrouped = $this->filteredGrouped;
        $displayGameCount = collect($displayGrouped)->sum(fn ($consoleData) => count($consoleData['games']));
        // Keep the jump UI compact when the list is long.
        $showGameJumpList = $displayGameCount <= 12;
      @endphp

      {{-- ──────────────────────── Jump Bar (mobile / small) ──────────────────────── --}}
      <div class="lg:hidden mb-4">
        <label for="my-saves-game-search-mobile" class="sr-only">Search saved games</label>
        <div class="relative">
          <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-cod-gray-500 dark:text-cod-gray-600 text-sm pointer-events-none" aria-hidden="true"></i>
          <input
            id="my-saves-game-search-mobile"
            type="text"
            role="searchbox"
            wire:model.live.debounce.300ms="gameSearch"
            placeholder="Search saved games"
            autocomplete="off"
            class="w-full rounded-lg border border-cod-gray-300/70 dark:border-cod-gray-800 bg-cod-gray-100/70 dark:bg-cod-gray-900/80 py-2 pl-9 pr-9 text-base sm:text-lg text-cod-gray-800 dark:text-cod-gray-100 placeholder:text-cod-gray-500 dark:placeholder:text-cod-gray-600 focus:outline-none focus:ring-2 focus:ring-rose-500/40 focus:border-rose-400/50 dark:focus:border-rose-600/50 smooth-300"
          >
          @if ($gameSearch !== '')
            <button
              type="button"
              wire:click="clearGameSearch"
              class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 inline-flex items-center justify-center rounded-md text-cod-gray-500 dark:text-cod-gray-600 hover:text-rose-500 dark:hover:text-rose-400 smooth-300"
              aria-label="Clear search"
            >
              <i class="fa fa-times text-sm" aria-hidden="true"></i>
            </button>
          @endif
        </div>
      </div>

      <div class="lg:hidden sticky top-16 z-40 -mx-4 xl:-mx-6 px-4 xl:px-6 mb-8 py-1 sm:py-2 bg-cod-gray-200/95 dark:bg-cod-gray-950/95 backdrop-blur-sm border-b border-cod-gray-300/50 dark:border-cod-gray-800/60">
        <div class="flex flex-wrap gap-x-3 gap-y-1 items-center">
          <span class="text-base sm:text-xl text-cod-gray-500 dark:text-cod-gray-600 shrink-0">Jump to:</span>
          @foreach ($displayGrouped as $consoleShort => $consoleData)
            <a
              href="#console-{{ $consoleShort }}"
              class="text-base sm:text-xl text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-500 dark:hover:text-rose-400 smooth-300 uppercase tracking-wider"
            >
              {{ $consoleShort === 'atari2600' ? 'Atari 2600' : strtoupper($consoleShort) }}
            </a>
          @endforeach
        </div>
      </div>

      {{-- ───────────────────── Desktop layout: vertical jump list + content ───────────────────── --}}
      <div id="my-saves-scroll-spy" class="lg:grid lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-6">
        <aside id="my-saves-desktop-sidebar" class="hidden lg:block lg:min-h-full">
          <div class="lg:sticky lg:top-16 lg:z-10 flex w-full max-h-[calc(100vh-4rem)] flex-col gap-3 overflow-hidden">
            <div class="shrink-0">
              <label for="my-saves-game-search" class="sr-only">Search saved games</label>
              <div class="relative">
                <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-cod-gray-500 dark:text-cod-gray-600 text-sm pointer-events-none" aria-hidden="true"></i>
                <input
                  id="my-saves-game-search"
                  type="text"
                  role="searchbox"
                  wire:model.live.debounce.300ms="gameSearch"
                  placeholder="Search saved games"
                  autocomplete="off"
                  class="w-full rounded-lg border border-cod-gray-300/70 dark:border-cod-gray-800 bg-cod-gray-100/70 dark:bg-cod-gray-900/80 py-2 pl-9 pr-9 text-base sm:text-lg text-cod-gray-800 dark:text-cod-gray-100 placeholder:text-cod-gray-500 dark:placeholder:text-cod-gray-600 focus:outline-none focus:ring-2 focus:ring-rose-500/40 focus:border-rose-400/50 dark:focus:border-rose-600/50 smooth-300"
                >
                @if ($gameSearch !== '')
                  <button
                    type="button"
                    wire:click="clearGameSearch"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 inline-flex items-center justify-center rounded-md text-cod-gray-500 dark:text-cod-gray-600 hover:text-rose-500 dark:hover:text-rose-400 smooth-300"
                    aria-label="Clear search"
                  >
                    <i class="fa fa-times text-sm" aria-hidden="true"></i>
                  </button>
                @endif
              </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto rounded-xl border border-cod-gray-300/60 dark:border-cod-gray-800 bg-cod-gray-100/60 dark:bg-cod-gray-900/70 p-4">
              <div class="text-base sm:text-xl text-cod-gray-500 dark:text-cod-gray-600 mb-3">
                Jump to
              </div>

              <nav class="space-y-3" aria-label="Jump list">
                @foreach ($displayGrouped as $consoleShort => $consoleData)
                  <div>
                    <a
                      href="#console-{{ $consoleShort }}"
                      class="block text-base sm:text-xl text-cod-gray-700 dark:text-cod-gray-300 hover:text-rose-500 dark:hover:text-rose-400 smooth-300 uppercase tracking-wider"
                    >
                      {{ $consoleShort === 'atari2600' ? 'Atari 2600' : strtoupper($consoleShort) }}
                    </a>

                    @if ($showGameJumpList)
                      <div class="mt-2 pl-3 border-l border-cod-gray-300/60 dark:border-cod-gray-800 space-y-1.5">
                        @foreach ($consoleData['games'] as $gameKey => $game)
                          <a
                            href="#game-{{ $consoleShort }}-{{ $gameKey }}"
                            class="block text-base sm:text-lg text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-500 dark:hover:text-rose-400 smooth-300 truncate"
                            title="{{ $game['title'] }}"
                          >
                            {{ $game['title'] }}
                          </a>
                        @endforeach
                      </div>
                    @endif
                  </div>
                @endforeach

                @if (! $showGameJumpList)
                  <div class="pt-2 text-sm sm:text-base text-cod-gray-500 dark:text-cod-gray-600 leading-snug">
                    Tip: game list hidden ({{ $displayGameCount }} games). Use the console links above.
                  </div>
                @endif
              </nav>
            </div>
          </div>
        </aside>

        <div class="min-w-0 pb-24">
          @if (count($displayGrouped) === 0)
            <div class="rounded-xl border border-cod-gray-300/60 dark:border-cod-gray-800 bg-cod-gray-100/60 dark:bg-cod-gray-900/70 px-4 py-8 text-center">
              <p class="text-lg sm:text-xl text-cod-gray-700 dark:text-cod-gray-300">
                No games match “{{ $gameSearch }}”.
              </p>
              <button
                type="button"
                wire:click="clearGameSearch"
                class="mt-3 text-base sm:text-lg text-rose-600 dark:text-rose-400 hover:underline smooth-300"
              >
                Clear search
              </button>
            </div>
          @else
          {{-- ─────────────────────────── Console Sections ─────────────────────────── --}}
          @foreach ($displayGrouped as $consoleShort => $consoleData)
            <section id="console-{{ $consoleShort }}" class="mb-14 scroll-mt-20">

              {{-- Console heading --}}
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3 mb-4">
                @if ($consoleData['console_logo'])
                  <img
                    class="h-5 sm:h-7 opacity-80 dark:opacity-70 grayscale"
                    src="{{ $consoleData['console_logo'] }}"
                    alt="{{ $consoleData['long_name'] }}"
                  >
                @endif
                <h2 class="text-xl sm:text-2xl font-mono uppercase tracking-widest text-cod-gray-700 dark:text-cod-gray-300">
                  {{ $consoleShort === 'atari2600' ? 'Atari 2600' : $consoleData['long_name'] }}
                </h2>
                <div class="w-full sm:flex-1 h-px bg-cod-gray-300/60 dark:bg-cod-gray-800 sm:ml-2"></div>
              </div>

              {{-- Game cards --}}
              <div class="flex flex-col gap-3">
                @foreach ($consoleData['games'] as $gameKey => $game)
                  <div
                    id="game-{{ $consoleShort }}-{{ $gameKey }}"
                    data-my-saves-game
                    class="rounded-xl border border-cod-gray-300/70 dark:border-cod-gray-800 bg-cod-gray-100/60 dark:bg-cod-gray-900/80 overflow-hidden scroll-mt-20"
                  >

                    {{-- Game info row --}}
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-cod-gray-300/50 dark:border-cod-gray-800/70">

                      {{-- Box art --}}
                      <div class="shrink-0 w-10 h-14 rounded overflow-hidden bg-cod-gray-300/40 dark:bg-cod-gray-800/60">
                        @if ($game['poster'] ?? $game['game_preview'] ?? null)
                          <img
                            class="w-full h-full object-cover"
                            src="{{ $game['poster'] ?? $game['game_preview'] }}"
                            alt="{{ $game['title'] }}"
                            loading="lazy"
                          >
                        @else
                          <div class="w-full h-full flex items-center justify-center text-cod-gray-500">
                            <i class="fa fa-image"></i>
                          </div>
                        @endif
                      </div>

                      {{-- Title & meta --}}
                      <div class="flex-1 min-w-0">
                        <p class="text-lg sm:text-2xl text-cod-gray-800 dark:text-cod-gray-100 leading-tight truncate">
                          {{ $game['title'] }}
                        </p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-sm sm:text-base bg-cod-gray-300/50 dark:bg-cod-gray-800 text-cod-gray-600 dark:text-cod-gray-500 uppercase tracking-wider">
                          {{ $consoleShort === 'atari2600' ? 'Atari 2600' : strtoupper($consoleShort) }}
                        </span>
                      </div>

                      {{-- Play link --}}
                      @if ($game['slug'] || $game['title'])
                        <a
                          wire:navigate
                          href="{{ $this->gameRoute($consoleShort, $game) }}"
                          class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-cod-gray-400/40 dark:border-cod-gray-700 text-base sm:text-xl text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-500 dark:hover:text-rose-400 hover:border-rose-400/50 dark:hover:border-rose-600/50 smooth-300"
                        >
                          <i class="fa fa-play text-sm sm:text-base"></i>
                          <span class="hidden sm:inline">Play</span>
                        </a>
                      @endif

                    </div>

                    {{-- Slot rows --}}
                    <div class="divide-y divide-cod-gray-300/30 dark:divide-cod-gray-800/60">
                      @foreach ($game['slots'] as $slotNum => $slot)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 px-4 py-2.5 {{ $slot ? '' : 'opacity-70 dark:opacity-60' }}">

                          {{-- Slot label --}}
                          <span
                            @class([
                              'shrink-0 w-auto sm:w-16 text-base font-mono uppercase tracking-wider whitespace-nowrap',
                              'text-emerald-700 dark:text-emerald-400 font-semibold' => $slot,
                              'text-cod-gray-700 dark:text-cod-gray-400' => ! $slot,
                            ])
                          >
                            Slot {{ $slotNum }}
                          </span>

                          @if ($slot)
                            {{-- Label --}}
                            <span class="w-full sm:flex-1 sm:min-w-[8rem] text-base sm:text-xl text-cod-gray-700 dark:text-cod-gray-300 truncate">
                              {{ $slot['label'] ?: '—' }}
                            </span>

                            {{-- Size --}}
                            <span class="shrink-0 text-base sm:text-xl text-cod-gray-500 dark:text-cod-gray-600 tabular-nums">
                              @if ($slot['size_bytes'] >= 1048576)
                                {{ number_format($slot['size_bytes'] / 1048576, 1) }} MB
                              @elseif ($slot['size_bytes'] >= 1024)
                                {{ number_format($slot['size_bytes'] / 1024, 1) }} KB
                              @else
                                {{ $slot['size_bytes'] }} B
                              @endif
                            </span>

                            {{-- Updated at --}}
                            <span
                              class="shrink-0 hidden sm:inline text-base sm:text-xl text-cod-gray-500 dark:text-cod-gray-600 tabular-nums"
                              title="{{ \Carbon\Carbon::parse($slot['updated_at'])->format('Y-m-d H:i') }}"
                            >
                              {{ \Carbon\Carbon::parse($slot['updated_at'])->diffForHumans() }}
                            </span>

                            <span class="flex items-center gap-1 w-full justify-end sm:w-auto sm:ml-auto" role="group" aria-label="Slot {{ $slotNum }} actions">
                              {{-- Download --}}
                              <a
                                href="{{ $slot['download_url'] }}"
                                class="w-9 h-9 flex items-center justify-center rounded-md border border-cod-gray-300/60 dark:border-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-500 dark:hover:text-rose-400 hover:border-rose-400/40 smooth-300"
                                title="Download slot {{ $slotNum }}"
                                download
                              >
                                <i class="fa fa-download text-base"></i>
                              </a>

                              {{-- Upload / replace --}}
                              <button
                                type="button"
                                wire:click="openUploadModal(@js($consoleShort), @js($game['game_slug']), {{ (int) $slotNum }}, @js($game['title']))"
                                class="w-9 h-9 flex items-center justify-center rounded-md border border-cod-gray-300/60 dark:border-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-400/40 smooth-300"
                                title="Replace slot {{ $slotNum }} with a .state file from your computer"
                              >
                                <i class="fa fa-upload text-base"></i>
                              </button>

                              {{-- Delete --}}
                              <button
                                type="button"
                                wire:click="confirmDelete({{ $slot['id'] }}, {{ $slotNum }}, @js($game['title']))"
                                class="w-9 h-9 flex items-center justify-center rounded-md border border-cod-gray-300/60 dark:border-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:border-red-400/30 smooth-300"
                                title="Delete slot {{ $slotNum }}"
                              >
                                <i class="fa fa-trash text-base"></i>
                              </button>
                            </span>

                          @else
                            <span class="w-full sm:flex-1 sm:min-w-[6rem] text-base sm:text-xl text-cod-gray-400 dark:text-cod-gray-600 italic">
                              Empty
                            </span>

                            <span class="flex items-center w-full justify-end sm:w-auto sm:ml-auto">
                              <button
                                type="button"
                                wire:click="openUploadModal(@js($consoleShort), @js($game['game_slug']), {{ (int) $slotNum }}, @js($game['title']))"
                                class="w-9 h-9 flex items-center justify-center rounded-md border border-cod-gray-300/60 dark:border-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-400/40 smooth-300"
                                title="Upload a .state file into slot {{ $slotNum }}"
                                aria-label="Upload a .state file into slot {{ $slotNum }}"
                              >
                                <i class="fa fa-cloud-upload text-base" aria-hidden="true"></i>
                              </button>
                            </span>
                          @endif

                        </div>
                      @endforeach
                    </div>

                  </div>
                @endforeach
              </div>

            </section>
          @endforeach
          @endif
        </div>
      </div>

    @else

      {{-- ─────────────────────────────── Empty State ──────────────────────────── --}}
      <div class="flex flex-col items-center justify-center gap-6 py-24 text-center max-w-lg mx-auto">
        <div class="w-16 h-16 rounded-full bg-cod-gray-300/50 dark:bg-cod-gray-800/60 flex items-center justify-center">
          <i class="fa fa-floppy-o text-3xl text-cod-gray-500 dark:text-cod-gray-600"></i>
        </div>
        <div>
          <p class="text-2xl sm:text-3xl text-cod-gray-700 dark:text-cod-gray-300 leading-tight">
            No saves yet
          </p>
          <p class="text-lg sm:text-2xl text-cod-gray-500 dark:text-cod-gray-600 mt-1">
            Play a game and use <span class="text-cod-gray-700 dark:text-cod-gray-400 font-medium">Cloud Save Slots</span> in the player to save—or upload a <span class="font-mono text-lg">.state</span> file there to seed your first cloud slot. To copy your current progress to another slot, use <strong>Save</strong> on that row, not <strong>Load</strong>. This page lists games once you have at least one slot.
          </p>
        </div>
        <a
          wire:navigate
          href="{{ route('home') }}"
          class="inline-flex items-center gap-2 px-5 py-2 rounded-lg border border-cod-gray-400/40 dark:border-cod-gray-700 text-lg sm:text-2xl text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-500 dark:hover:text-rose-400 hover:border-rose-400/50 dark:hover:border-rose-600/50 smooth-300"
        >
          <i class="fa fa-gamepad text-lg sm:text-xl"></i>
          Browse Games
        </a>
      </div>

    @endif

  </x-container>

  <x-dialog-modal wire:model.live="confirmingDelete" maxWidth="md">
    <x-slot name="title">
      Confirm deletion
    </x-slot>

    <x-slot name="content">
      Delete slot {{ $pendingDeleteSlot }} of “{{ $pendingDeleteGameTitle }}”? This cannot be undone.
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button wire:click="cancelDelete">
        Cancel
      </x-secondary-button>

      <x-danger-button class="ml-3" wire:click="deleteConfirmed">
        Delete
      </x-danger-button>
    </x-slot>
  </x-dialog-modal>

  <x-dialog-modal wire:model.live="showUploadModal" maxWidth="lg">
    <x-slot name="title">
      Upload to cloud
    </x-slot>

    <x-slot name="content">
      @if ($uploadTarget)
        <div class="space-y-4 text-left">
          <div class="rounded-lg border border-cod-gray-300/60 dark:border-cod-gray-700 bg-cod-gray-100/50 dark:bg-cod-gray-900/60 px-4 py-3">
            <p class="text-2xl text-cod-gray-800 dark:text-cod-gray-100 leading-tight">
              {{ $uploadTarget['game_title'] }}
            </p>
            <p class="text-xl text-cod-gray-500 dark:text-cod-gray-500 mt-1">
              Slot {{ $uploadTarget['slot'] }}
              ·
              <span class="uppercase tracking-wider">{{ $uploadTarget['console'] }}</span>
            </p>
          </div>

          <p class="text-xl text-cod-gray-600 dark:text-cod-gray-400 leading-snug">
            Choose a <span class="font-mono">.state</span> file from this game (same console and title). Uploading replaces anything already in this slot.
          </p>

          <div>
            <label for="upload-save-label" class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-1">Label <span class="text-cod-gray-400">(optional)</span></label>
            <x-input
              id="upload-save-label"
              type="text"
              wire:model="uploadLabel"
              class="w-full"
              placeholder="e.g. Before final boss"
              maxlength="80"
            />
            @error('uploadLabel')
              <p class="mt-1 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="upload-state-file" class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-2">Save file</label>
            <div class="relative">
              <input
                id="upload-state-file"
                type="file"
                wire:model="uploadStateFile"
                accept=".state"
                class="block w-full text-xl text-cod-gray-700 dark:text-cod-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-lg file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 file:cursor-pointer cursor-pointer border border-dashed border-cod-gray-400/70 dark:border-cod-gray-600 rounded-xl px-3 py-4 bg-cod-gray-50/80 dark:bg-cod-gray-950/40"
              />
            </div>
            <div wire:loading wire:target="uploadStateFile" class="mt-2 text-lg text-cod-gray-500">
              Reading file…
            </div>
            @error('uploadStateFile')
              <p class="mt-2 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>
        </div>
      @endif
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button wire:click="closeUploadModal" wire:loading.attr="disabled" wire:target="submitUpload,uploadStateFile">
        Cancel
      </x-secondary-button>

      <x-button
        class="ml-3"
        wire:click="submitUpload"
        wire:loading.attr="disabled"
        wire:target="submitUpload,uploadStateFile"
      >
        <span wire:loading.remove wire:target="submitUpload">Upload to slot</span>
        <span wire:loading wire:target="submitUpload">Uploading…</span>
      </x-button>
    </x-slot>
  </x-dialog-modal>

  {{-- ───────────────────── Global Upload Modal ───────────────────── --}}
  <x-dialog-modal wire:model.live="showGlobalUploadModal" maxWidth="lg">
    <x-slot name="title">
      <span class="flex items-center gap-2">
        <i class="fa fa-cloud-upload text-emerald-500"></i>
        Upload Save State
      </span>
    </x-slot>

    <x-slot name="content">
      <div class="space-y-5 text-left">

        <p class="text-xl text-cod-gray-600 dark:text-cod-gray-400 leading-snug">
          Upload a <span class="font-mono font-semibold">.state</span> file for
          <span class="font-semibold text-cod-gray-700 dark:text-cod-gray-300">any game</span> on any available
          slot—even if that game isn't listed here yet.
        </p>

        {{-- Step 1: Console --}}
        <div>
          <label for="g-console" class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-1">
            Console <span class="text-red-500">*</span>
          </label>
          <select
            id="g-console"
            wire:model.live="globalConsole"
            class="w-full rounded-lg border border-cod-gray-300 dark:border-cod-gray-700 bg-white dark:bg-cod-gray-900 text-cod-gray-800 dark:text-cod-gray-100 px-3 py-2 text-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent smooth-300"
          >
            <option value="">— Select a console —</option>
            @foreach ($consoleOptions as $short => $name)
              <option value="{{ $short }}">{{ $name }}</option>
            @endforeach
          </select>
          @error('globalConsole')
            <p class="mt-1 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>

        {{-- Step 2: Game (shown after console selected) --}}
        @if ($globalConsole)
          <div>
            <div wire:loading wire:target="globalConsole" class="flex items-center gap-2 text-xl text-cod-gray-500 py-2">
              <svg class="animate-spin w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
              Loading games…
            </div>

            <div wire:loading.remove wire:target="globalConsole">
              @if (count($globalGameOptions))
                <label for="g-game" class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-1">
                  Game
                  <span class="text-red-500">*</span>
                  <span class="text-lg text-cod-gray-400 dark:text-cod-gray-600 ml-1">({{ count($globalGameOptions) }} available)</span>
                </label>
                <select
                  id="g-game"
                  wire:model.live="globalGameSlug"
                  class="w-full rounded-lg border border-cod-gray-300 dark:border-cod-gray-700 bg-white dark:bg-cod-gray-900 text-cod-gray-800 dark:text-cod-gray-100 px-3 py-2 text-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent smooth-300"
                  size="{{ min(count($globalGameOptions), 6) }}"
                >
                  <option value="">— Select a game —</option>
                  @foreach ($globalGameOptions as $slug => $title)
                    <option value="{{ $slug }}">{{ $title }}</option>
                  @endforeach
                </select>
                @error('globalGameSlug')
                  <p class="mt-1 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              @else
                <p class="text-xl text-cod-gray-500 dark:text-cod-gray-600 italic">No games found for this console.</p>
              @endif
            </div>
          </div>
        @endif

        {{-- Step 3: Slot + Emulator + Label + File (shown after game selected) --}}
        @if ($globalGameSlug)
          {{-- Slot picker --}}
          <div>
            <span class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-2">
              Slot <span class="text-red-500">*</span>
            </span>
            <div class="flex gap-2 flex-wrap">
              @for ($s = 1; $s <= 5; $s++)
                <button
                  type="button"
                  wire:click="$set('globalSlot', {{ $s }})"
                  @class([
                    'w-11 h-11 rounded-lg border-2 text-xl font-bold transition-all duration-150',
                    'border-emerald-500 bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 shadow-sm' => $globalSlot === $s,
                    'border-cod-gray-300 dark:border-cod-gray-700 text-cod-gray-500 dark:text-cod-gray-400 hover:border-emerald-400 dark:hover:border-emerald-600' => $globalSlot !== $s,
                  ])
                  title="Slot {{ $s }}"
                >{{ $s }}</button>
              @endfor
            </div>
            @error('globalSlot')
              <p class="mt-1 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

        {{-- Emulator selection intentionally hidden; inferred from console --}}

          {{-- Optional label --}}
          <div>
            <label for="g-label" class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-1">
              Label <span class="text-cod-gray-400">(optional)</span>
            </label>
            <x-input
              id="g-label"
              type="text"
              wire:model="globalLabel"
              class="w-full"
              placeholder="e.g. Before final boss"
              maxlength="80"
            />
            @error('globalLabel')
              <p class="mt-1 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          {{-- File input --}}
          <div>
            <label for="g-file" class="block text-xl text-cod-gray-600 dark:text-cod-gray-500 mb-2">
              Save file <span class="text-red-500">*</span>
            </label>
            <input
              id="g-file"
              type="file"
              wire:model="globalStateFile"
              accept=".state"
              class="block w-full text-xl text-cod-gray-700 dark:text-cod-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-lg file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 file:cursor-pointer cursor-pointer border border-dashed border-cod-gray-400/70 dark:border-cod-gray-600 rounded-xl px-3 py-4 bg-cod-gray-50/80 dark:bg-cod-gray-950/40"
            />
            <div wire:loading wire:target="globalStateFile" class="mt-2 text-lg text-cod-gray-500">
              Reading file…
            </div>
            @error('globalStateFile')
              <p class="mt-2 text-lg text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          {{-- Summary chip --}}
          @if ($globalStateFile)
            <div class="rounded-lg border border-emerald-600/30 bg-emerald-600/10 px-4 py-2.5 text-xl text-emerald-800 dark:text-emerald-300">
              <i class="fa fa-info-circle mr-1.5 text-emerald-500"></i>
              Will upload to <strong>Slot {{ $globalSlot }}</strong> for this game on
              <span class="uppercase font-mono tracking-wider">{{ $globalConsole }}</span>. Any existing save in that slot will be replaced.
            </div>
          @endif
        @endif

      </div>
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button
        wire:click="closeGlobalUploadModal"
        wire:loading.attr="disabled"
        wire:target="submitGlobalUpload,globalStateFile,globalConsole"
      >
        Cancel
      </x-secondary-button>

      <x-button
        class="ml-3"
        wire:click="submitGlobalUpload"
        wire:loading.attr="disabled"
        wire:target="submitGlobalUpload,globalStateFile"
        :disabled="! $globalGameSlug || ! $globalStateFile"
      >
        <span wire:loading.remove wire:target="submitGlobalUpload">Upload to cloud</span>
        <span wire:loading wire:target="submitGlobalUpload">Uploading…</span>
      </x-button>
    </x-slot>
  </x-dialog-modal>
</div>
