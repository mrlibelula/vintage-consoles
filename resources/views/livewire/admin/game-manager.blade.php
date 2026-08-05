<x-container class="mt-6 sm:mt-10">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl text-cod-gray-900 dark:text-cod-gray-100">Game Manager</h1>
            <p class="mt-2 text-xl text-cod-gray-600 dark:text-cod-gray-400">Manage games across all console platforms</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Console Selection & Search -->
        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div class="flex-1">
                    <label for="console-select" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Select Console</label>
                    <select wire:model.live="selectedConsole" id="console-select"
                            @change="$dispatch('loader-top-on'); setTimeout(() => $dispatch('loader-top-off'), 500)"
                            class="form-field w-full md:w-[18rem]_ px-3 py-2 text-xl">
                        @foreach($consoles as $console)
                            <option value="{{ $console->short_name }}">{{ $console->long_name }} ({{ $console->short_name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1">
                    <label for="search" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Search Games</label>
                    <input wire:model.live.debounce.300ms="searchTerm" type="text" id="search" placeholder="Search by title or publisher..."
                        class="form-field w-full px-3 py-2 text-xl">
                </div>

                <div class="w-full sm:w-auto">
                    <label for="per-page" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Per page</label>
                    <select wire:model.live="perPage" id="per-page"
                            class="form-field w-full sm:w-28 px-3 py-2 text-xl">
                        <option value="4">4</option>
                        <option value="8">8</option>
                        <option value="12">12</option>
                        <option value="16">16</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button @click="$dispatch('loader-top-on');" wire:click="openAddModal"
                            class="inline-flex items-center px-4 py-1.5 border border-transparent text-xl font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition duration-150 ease-in-out">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Game
                    </button>
                </div>
            </div>
        </div>

        <!-- Games Table -->
        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
                <h3 class="text-xl font-medium text-cod-gray-900 dark:text-cod-gray-100">
                    Games for {{ $selectedConsole }} ({{ $this->paginatedGames->total() }} games)
                </h3>
            </div>

            @if($this->paginatedGames->total() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-cod-gray-200 dark:divide-cod-gray-700">
                        <thead class="bg-cod-gray-50 dark:bg-cod-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-base bg-cod-gray-50 dark:bg-cod-gray-700 font-medium text-cod-gray-500 dark:text-cod-gray-400 uppercase tracking-wider">
                                    <div class="flex items-center space-x-4">
                                        <button wire:click="sortBy('title')" @click="$dispatch('loader-top-on'); setTimeout(() => $dispatch('loader-top-off'), 300)" class="flex items-center space-x-1 hover:text-cod-gray-700 dark:hover:text-cod-gray-200 transition-colors">
                                            <span>Title</span>
                                        </button>
                                        <span class="text-cod-gray-300 dark:text-cod-gray-600">|</span>
                                        <button wire:click="sortBy('id')" @click="$dispatch('loader-top-on'); setTimeout(() => $dispatch('loader-top-off'), 300)" class="flex items-center space-x-1 hover:text-cod-gray-700 dark:hover:text-cod-gray-200 transition-colors">
                                            <span>ID</span>
                                        </button>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-base bg-cod-gray-50 dark:bg-cod-gray-700 font-medium text-cod-gray-500 dark:text-cod-gray-400 uppercase tracking-wider">ROM File</th>
                                <th class="px-6 py-3 text-right text-base bg-cod-gray-50 dark:bg-cod-gray-700 font-medium text-cod-gray-500 dark:text-cod-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-cod-gray-50 dark:bg-cod-gray-800 divide-y divide-cod-gray-200 dark:divide-cod-gray-700">
                            @foreach($this->paginatedGames as $game)
                                <tr wire:key="game-manager-row-{{ $game->id }}" class="hover:bg-rose-200/60 dark:hover:bg-rose-700/20 transition-colors duration-300">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            <x-game-poster
                                                class="mr-4"
                                                :src="$game->poster"
                                                :alt="$game->title"
                                                loading-targets="previousPage,nextPage,gotoPage,setPage,sortBy,updatedSelectedConsole,updatedSearchTerm,updatedPerPage"
                                            />
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xl text-cod-gray-950 dark:text-cod-gray-50 mb-1">
                                                    {{ $game->title }}
                                                    <span class="text-cod-gray-500 dark:text-cod-gray-400">(ID: {{ $game->id }})</span>
                                                    @if($game->needs_igdb_sync)
                                                        <span class="ml-1 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-1.5 py-0.5 rounded">needs IGDB sync</span>
                                                    @endif
                                                </div>
                                                <div class="flex flex-wrap items-center gap-4 text-xl text-cod-gray-600 dark:text-cod-gray-300">
                                                    <span class="font-medium">{{ $game->publisher }}</span>
                                                </div>
                                                <div class="flex items-center gap-4 text-xl">
                                                    <span>{{ $game->release_year }}</span>
                                                    @if($game->rating)
                                                        <span class="font-medium text-rose-600 dark:text-rose-400">{{ number_format($game->rating * 100, 0) }}%</span>
                                                        <div class="w-12 bg-cod-gray-200 dark:bg-cod-gray-600 rounded-full h-1.5">
                                                            <div class="bg-rose-600 h-1.5 rounded-full" style="width: {{ $game->rating * 100 }}%"></div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-cod-gray-900 dark:text-cod-gray-100">
                                        <div class="max-w-xs text-xs truncate font-mono" title="{{ $game->rom }}">{{ $game->rom }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-xl font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="$dispatch('loader-top-on');" wire:click="openEditModal({{ $game->id }})"
                                                    class="inline-flex items-center px-3 py-1">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="$dispatch('loader-top-on');" wire:click="openDeleteModal({{ $game->id }})"
                                                    class="inline-flex items-center px-2 border border-transparent text-xl leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-300 dark:bg-red-900 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-cod-gray-200 dark:border-cod-gray-700">
                    {{ $this->paginatedGames->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-4xl mx-auto text-cod-gray-400">¯\_(ツ)_/¯</div>
                    <h3 class="mt-2 text-xl font-medium text-cod-gray-900 dark:text-cod-gray-100">No games found</h3>
                    <p class="mt-1 text-xl text-cod-gray-500 dark:text-cod-gray-400">
                        @if($searchTerm) No games match your search criteria. @else No games available for this console. @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Modal -->
        @if($showModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
                 x-data="{ screenshotGalleryOpen: false }"
                 x-init="document.body.style.overflow = 'hidden'"
                 @screenshot-gallery-open.window="screenshotGalleryOpen = true"
                 @screenshot-gallery-close.window="screenshotGalleryOpen = false"
                 @keydown.escape.window="if (!screenshotGalleryOpen && !$wire.showCheatPreviewModal) $wire.closeModal()"
                 x-on:modal-closed.window="document.body.style.overflow = 'auto'">
                <div class="flex items-center justify-center min-h-screen pt-20 px-4 pb-4 text-center sm:block sm:pt-8 sm:pb-4">
                    <div class="fixed inset-0 bg-cod-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="$dispatch('loader-top-on');" wire:click="closeModal"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-cod-gray-50 dark:bg-cod-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-12 sm:align-middle sm:max-w-4xl sm:w-full">
                        @if($modalMode === 'delete')
                            <!-- Delete Confirmation Modal -->
                            <div class="bg-cod-gray-50 dark:bg-cod-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex justify-end mb-2">
                                    <button type="button" @click="$dispatch('loader-top-on');" wire:click="closeModal" class="text-cod-gray-400 hover:text-cod-gray-600 dark:hover:text-cod-gray-200 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-xl leading-6 font-medium text-cod-gray-900 dark:text-cod-gray-100" id="modal-title">Delete Game</h3>
                                        <div class="mt-2">
                                            <p class="text-xl text-cod-gray-500 dark:text-cod-gray-400">
                                                Are you sure you want to delete "{{ $editingGame?->title ?? '' }}"? This action cannot be undone.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-cod-gray-50 dark:bg-cod-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button wire:click="deleteGame" type="button"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-xl">
                                    Delete
                                </button>
                                <button wire:click="closeModal" type="button"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-cod-gray-300 dark:border-cod-gray-600 shadow-sm px-4 py-2 bg-cod-gray-50 dark:bg-cod-gray-800 text-base font-medium text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-50 dark:hover:bg-cod-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-xl">
                                    Cancel
                                </button>
                            </div>
                        @else
                            <!-- Add/Edit Game Modal -->
                            <form wire:submit="saveGame" @submit="$dispatch('loader-top-on')" x-data enctype="multipart/form-data">
                                <div class="bg-cod-gray-50 dark:bg-cod-gray-800 px-4 pt-5 pb-4 sm:p-6 relative">
                                    <button type="button" @click="$dispatch('loader-top-on');" wire:click="closeModal" class="absolute top-4 right-4 text-cod-gray-400 hover:text-cod-gray-600 dark:hover:text-cod-gray-200 transition-colors z-10">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>

                                    <!-- Modal header with console switcher -->
                                    <div class="mb-4 pr-8">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-xl leading-6 font-medium text-cod-gray-900 dark:text-cod-gray-100">
                                                {{ $modalMode === 'add' ? 'Add New Game' : 'Edit Game' }}
                                            </h3>
                                            <div class="min-w-[14rem]">
                                                <label for="form_console_header" class="sr-only">Console</label>
                                                <select wire:model.live="formConsole" id="form_console_header"
                                                        class="form-field w-full px-3 py-1.5 text-xl">
                                                    @foreach($consoles as $consoleOption)
                                                        <option value="{{ $consoleOption->short_name }}">{{ $consoleOption->long_name }} ({{ $consoleOption->short_name }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Basic Information -->
                                        <div class="space-y-4">
                                            <!-- Title + API Fill -->
                                            <div>
                                                <div class="flex justify-between items-center mb-1">
                                                    <label for="title" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Title <span class="text-rose-600">*</span></label>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button"
                                                                @click="$dispatch('loader-top-on'); $wire.fetchGameDataFromIgdb()"
                                                                class="inline-flex items-center px-1 border border-transparent text-sm leading-4 font-medium rounded-md transition duration-150 ease-in-out"
                                                                :class="title && title.trim().length > 0 ?
                                                                    'text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500' :
                                                                    'text-cod-gray-400 bg-cod-gray-300 dark:bg-cod-gray-600 cursor-not-allowed'"
                                                                :disabled="!title || title.trim().length === 0"
                                                                x-data="{ title: $wire.entangle('title') }">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                            </svg>
                                                            <span class="mr-1 text-sm">API Fill</span>
                                                        </button>
                                                        <!-- API Success Message -->
                                                        <div x-data="{ show: false }"
                                                             @api-success.window="show = true; setTimeout(() => show = false, 4000)"
                                                             x-show="show"
                                                             x-transition
                                                             class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-md">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            IGDB data loaded!
                                                        </div>
                                                    </div>
                                                </div>
                                                <input wire:model="title" type="text" id="title" required
                                                    class="form-field mt-1 block w-full">
                                                @error('title') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Enter the game title, then click "API Fill" to fetch data from IGDB</p>
                                            </div>

                                            <div>
                                                <label for="publisher" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Publisher <span class="text-rose-600">*</span></label>
                                                <input wire:model="publisher" type="text" id="publisher" required
                                                    class="form-field mt-1 block w-full">
                                                @error('publisher') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="release_year" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Release Year <span class="text-rose-600">*</span></label>
                                                    <input wire:model="release_year" type="number" id="release_year" required min="1970" max="{{ date('Y') + 5 }}"
                                                        class="form-field mt-1 block w-full">
                                                    @error('release_year') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                                <div>
                                                    <label for="rating" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Rating (0–1) <span class="text-rose-600">*</span></label>
                                                    <input wire:model="rating" type="number" id="rating" required min="0" max="1" step="0.0001"
                                                        class="form-field mt-1 block w-full">
                                                    @error('rating') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                            </div>

                                            <!-- ROM File — URL for MS-DOS, file upload for other consoles -->
                                            <div>
                                                <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">
                                                    ROM File <span class="text-rose-600">*</span>
                                                </label>

                                                @if(strtolower($formConsole) === 'pc')
                                                    <input wire:model="rom" type="url" id="rom_url" placeholder="https://play.libe.dev/games/bundles/game.jsdos"
                                                        class="form-field mt-1 block w-full">
                                                    <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">MS-DOS games require a .jsdos bundle URL</p>
                                                @else
                                                    <input wire:model="romFile" type="file" id="rom_file" accept="{{ $this->romFileAccept }}"
                                                        class="mt-1 block w-full text-cod-gray-900 dark:text-cod-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                                                    @if($rom)
                                                        <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">
                                                            Current: <span class="font-mono">{{ $rom }}</span> — leave empty to keep.
                                                        </p>
                                                    @endif
                                                    <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">{{ $this->romFormatHint }}</p>
                                                @endif
                                                <!-- ROM Error -->
                                                <div x-data="{ showRomError: false, romErrorMessage: '' }"
                                                     @rom-error.window="showRomError = true; romErrorMessage = $event.detail.message"
                                                     x-show="showRomError"
                                                     x-transition
                                                     class="mt-2 p-3 bg-rose-100 dark:bg-rose-900 border border-rose-300 dark:border-rose-700 rounded-md">
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                        </svg>
                                                        <div class="flex-1">
                                                            <h4 class="text-sm font-medium text-rose-800 dark:text-rose-200 mb-1">ROM File Error</h4>
                                                            <p class="text-sm text-rose-700 dark:text-rose-300" x-text="romErrorMessage"></p>
                                                        </div>
                                                        <button @click="showRomError = false" class="ml-2 text-rose-400 hover:text-rose-600 dark:hover:text-rose-200">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Media URLs -->
                                        <div class="space-y-4">
                                            <div>
                                                <label for="poster" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Poster URL</label>
                                                <input wire:model="poster" type="text" id="poster" placeholder="https://images.igdb.com/... (filled by API Fill)"
                                                    class="form-field mt-1 block w-full">
                                                @error('poster') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                            </div>

                                            <div>
                                                <label for="game_preview" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Game Preview URL</label>
                                                <input wire:model="game_preview" type="text" id="game_preview" placeholder="https://example.com/preview.gif or /videos/preview.webm"
                                                    class="form-field mt-1 block w-full">
                                                @error('game_preview') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Animated GIF, WebM, or MP4 preview shown on game cards</p>
                                            </div>

                                            <div>
                                                <div class="flex items-center justify-between gap-x-2">
                                                    <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Walkthrough videos</label>
                                                    <button type="button" wire:click="addWalkthroughVideo" class="text-sm text-rose-600 dark:text-rose-400 hover:underline">+ Add</button>
                                                </div>
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">YouTube URL or 11-char video ID. Shown before IGDB trailers on the play page.</p>
                                                <div class="mt-2 space-y-2">
                                                    @foreach ($walkthroughVideos as $index => $row)
                                                    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                                                        <input wire:model="walkthroughVideos.{{ $index }}.title" type="text" placeholder="Title"
                                                            class="form-field block w-full sm:w-1/3">
                                                        <input wire:model="walkthroughVideos.{{ $index }}.youtube_id" type="text" placeholder="https://youtu.be/... or video id"
                                                            class="form-field block w-full sm:flex-1">
                                                        <button type="button" wire:click="removeWalkthroughVideo({{ $index }})" class="text-sm text-cod-gray-500 hover:text-rose-500 px-1">Remove</button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div>
                                                <label for="cartridge" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Cartridge URL</label>
                                                <input wire:model="cartridge" type="text" id="cartridge" placeholder="https://example.com/cartridge.jpg"
                                                    class="form-field mt-1 block w-full">
                                                @error('cartridge') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Checkboxes -->
                                            <div class="space-y-3">
                                                <label class="flex items-center">
                                                    <input wire:model="multiplayer_support" type="checkbox"
                                                        class="rounded border-cod-gray-300 dark:border-cod-gray-600 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-xl text-cod-gray-700 dark:text-cod-gray-300">Multiplayer Support</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input wire:model="save_state_support" type="checkbox"
                                                        class="rounded border-cod-gray-300 dark:border-cod-gray-600 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-xl text-cod-gray-700 dark:text-cod-gray-300">Save State Support</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input wire:model="is_free" type="checkbox"
                                                        class="rounded border-cod-gray-300 dark:border-cod-gray-600 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-xl text-cod-gray-700 dark:text-cod-gray-300">Free Game</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mt-6">
                                        <label for="description" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Description <span class="text-rose-600">*</span></label>
                                        <textarea wire:model="description" id="description" rows="4" required
                                                class="form-field mt-1 block w-full"></textarea>
                                        @error('description') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Genres -->
                                    <div class="mt-6">
                                        <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Genres</label>
                                        @foreach($genres as $index => $genre)
                                            <div class="flex gap-4 mb-2">
                                                <input wire:model="genres.{{ $index }}.name" type="text" placeholder="Genre slug (e.g. action-adventure)"
                                                    class="form-field flex-1">
                                                <input wire:model="genres.{{ $index }}.description" type="text" placeholder="Genre description"
                                                    class="form-field flex-[2]">
                                                @if(count($genres) > 1)
                                                    <button type="button" wire:click="removeGenre({{ $index }})"
                                                            class="px-3 py-2 text-red-600 hover:text-red-800">Remove</button>
                                                @endif
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addGenre"
                                                class="mt-2 text-xl text-rose-600 hover:text-rose-800">+ Add Genre</button>
                                    </div>

                                    <!-- Screenshots -->
                                    @php
                                        $previewScreenshots = collect($screenshots)
                                            ->map(function (array $shot) {
                                                $thumb = trim((string) ($shot['thumb_url'] ?? ''));
                                                $full = trim((string) ($shot['full_url'] ?? ''));

                                                if ($thumb === '' && $full === '') {
                                                    return null;
                                                }

                                                if ($thumb === '') {
                                                    $thumb = $full;
                                                }

                                                if ($full === '') {
                                                    $full = $thumb;
                                                }

                                                return (object) [
                                                    'thumb_url' => $thumb,
                                                    'full_url' => $full,
                                                ];
                                            })
                                            ->filter()
                                            ->values();
                                    @endphp
                                    <div class="mt-6">
                                        <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">
                                            Screenshots
                                            <span class="text-sm font-normal text-cod-gray-500 dark:text-cod-gray-400 ml-2">(IGDB screenshots can be refreshed with API Fill)</span>
                                        </label>

                                        @if($previewScreenshots->isNotEmpty())
                                            <x-screenshot-gallery
                                                :screenshots="$previewScreenshots"
                                                :game-title="$title ?: ($editingGame?->title ?? 'Game')"
                                            />
                                        @endif

                                        <div class="mt-3 space-y-3">
                                            @foreach($screenshots as $index => $shot)
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" wire:key="screenshot-row-{{ $index }}">
                                                    <div>
                                                        <label for="screenshot-thumb-{{ $index }}" class="block text-sm font-medium text-cod-gray-700 dark:text-cod-gray-300">Thumbnail URL</label>
                                                        <input wire:model.live="screenshots.{{ $index }}.thumb_url" type="text" id="screenshot-thumb-{{ $index }}"
                                                            placeholder="https://images.igdb.com/... or /images/screenshot.jpg"
                                                            class="form-field mt-1 block w-full">
                                                        @error('screenshots.' . $index . '.thumb_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                    </div>
                                                    <div class="flex gap-4 items-end">
                                                        <div class="flex-1">
                                                            <label for="screenshot-full-{{ $index }}" class="block text-sm font-medium text-cod-gray-700 dark:text-cod-gray-300">Full-size URL</label>
                                                            <input wire:model.live="screenshots.{{ $index }}.full_url" type="text" id="screenshot-full-{{ $index }}"
                                                                placeholder="Optional; uses thumbnail when empty"
                                                                class="form-field mt-1 block w-full">
                                                            @error('screenshots.' . $index . '.full_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                        <button type="button" wire:click="removeScreenshot({{ $index }})"
                                                                class="px-3 py-2 text-red-600 hover:text-red-800">Remove</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <button type="button" wire:click="addScreenshot"
                                                class="mt-2 text-xl text-rose-600 hover:text-rose-800">+ Add Screenshot</button>
                                    </div>

                                    @if($modalMode === 'edit')
                                    <!-- Cheat sheet -->
                                    <div class="mt-6" x-data="{ showCheatSuccess: false }" @cheat-import-success.window="showCheatSuccess = true; setTimeout(() => showCheatSuccess = false, 4000)">
                                        <div class="flex items-center justify-between gap-x-2 mb-2">
                                            <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">
                                                Cheat sheet
                                            </label>
                                            @if($cheatExistsOnDisk)
                                                <span class="text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-1.5 py-0.5 rounded">Present</span>
                                            @else
                                                <span class="text-sm bg-cod-gray-200 dark:bg-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 px-1.5 py-0.5 rounded">Missing</span>
                                            @endif
                                        </div>
                                        <p class="mb-2 text-sm text-cod-gray-500 dark:text-cod-gray-400">
                                            <strong>Paste:</strong> AI reformats your text into clean Markdown for the play panel (keeps content — tips, codes, etc.).
                                            <strong>Upload</strong> (.txt/.md/.docx/.pdf): AI analyzes the document and extracts cheat codes/unlockables/secrets plus the important how-to context around them.
                                            Then edit the source, use "Preview rendered MD", and hit "Update Game" to save.
                                        </p>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label for="cheat_source_text" class="block text-sm font-medium text-cod-gray-700 dark:text-cod-gray-300">Paste source text</label>
                                                <textarea wire:model="cheatSourceText" id="cheat_source_text" rows="4" placeholder="Paste cheat codes, manual excerpt, walkthrough text, etc."
                                                    class="form-field mt-1 block w-full"></textarea>
                                            </div>
                                            <div>
                                                <label for="cheat_source_file" class="block text-sm font-medium text-cod-gray-700 dark:text-cod-gray-300">Or upload a file</label>
                                                <input wire:model="cheatSourceFile" type="file" id="cheat_source_file" accept=".txt,.md,.docx,.pdf"
                                                    class="mt-1 block w-full text-cod-gray-900 dark:text-cod-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                                                <div wire:loading wire:target="cheatSourceFile" class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Uploading…</div>
                                                @error('cheatSourceFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">.txt, .md, .docx, or .pdf — legacy .doc is not supported</p>
                                            </div>
                                        </div>

                                        <div class="mt-2 flex items-center gap-x-2">
                                            <button type="button"
                                                    @click="$dispatch('loader-top-on'); $wire.importCheatSheet()"
                                                    wire:loading.attr="disabled"
                                                    wire:target="importCheatSheet,cheatSourceFile"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-60">
                                                <span wire:loading.remove wire:target="importCheatSheet">Import &amp; normalize</span>
                                                <span wire:loading wire:target="importCheatSheet">Analyzing…</span>
                                            </button>
                                            <button type="button" wire:click="clearCheatSheet"
                                                    class="text-sm text-cod-gray-500 hover:text-rose-500 px-1">Clear</button>
                                            <div x-show="showCheatSuccess" x-transition x-cloak
                                                 class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-md">
                                                Cheat sheet normalized!
                                            </div>
                                        </div>

                                        @if($cheatImportError)
                                        <div class="mt-2 p-3 bg-rose-100 dark:bg-rose-900 border border-rose-300 dark:border-rose-700 rounded-md text-sm text-rose-800 dark:text-rose-200">
                                            {{ $cheatImportError }}
                                        </div>
                                        @endif

                                        <div class="mt-3">
                                            <div class="flex items-center justify-between gap-x-2">
                                                <label for="cheat_markdown" class="block text-sm font-medium text-cod-gray-700 dark:text-cod-gray-300">Markdown source (saved with Update Game)</label>
                                                <button type="button" wire:click="openCheatPreview"
                                                        class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    Preview rendered MD
                                                </button>
                                            </div>
                                            <textarea wire:model="cheatMarkdown" id="cheat_markdown" rows="8" placeholder="## Cheat Codes&#10;- Up, Up, Down, Down, Left, Right, Left, Right, B, A, Start — 30 lives"
                                                class="form-field mt-1 block w-full font-mono text-sm"></textarea>
                                            <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Leave empty to remove the cheat sheet from this game.</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="bg-cod-gray-50 dark:bg-cod-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button @click="$dispatch('loader-top-on');" type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-base font-medium text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:ml-3 sm:w-auto sm:text-xl">
                                        {{ $modalMode === 'add' ? 'Add Game' : 'Update Game' }}
                                    </button>
                                    <button @click="$dispatch('loader-top-on');" type="button" wire:click="closeModal"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-cod-gray-300 dark:border-cod-gray-600 shadow-sm px-4 py-2 bg-cod-gray-50 dark:bg-cod-gray-800 text-base font-medium text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-50 dark:hover:bg-cod-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-xl">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Cheat sheet rendered Markdown preview (above the edit modal) --}}
        @if($showCheatPreviewModal)
        <div
            class="fixed inset-0 z-[100] overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="cheat-preview-title"
            x-data
            x-init="document.body.style.overflow = 'hidden'"
            @keydown.escape.window="$wire.closeCheatPreview()"
        >
            <div class="flex min-h-screen items-start justify-center px-4 pb-8 pt-20 text-center">
                <div
                    class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                    aria-hidden="true"
                    wire:click="closeCheatPreview"
                ></div>

                <div class="relative inline-block w-full max-w-3xl text-left align-middle" @click.stop>
                    <div class="overflow-hidden rounded-xl bg-cod-gray-50 shadow-2xl dark:bg-cod-gray-900">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
                            <h3 id="cheat-preview-title" class="text-xl font-medium text-cod-gray-900 dark:text-cod-gray-100">
                                Cheat sheet preview
                            </h3>
                            <button type="button" wire:click="closeCheatPreview"
                                    class="text-cod-gray-400 hover:text-cod-gray-600 dark:hover:text-cod-gray-200"
                                    aria-label="Close preview">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                            <div class="cheat-sheet">
                                {!! $cheatPreviewHtml !!}
                            </div>
                        </div>

                        <div class="flex justify-end gap-x-2 px-6 py-3 border-t border-cod-gray-200 dark:border-cod-gray-700 bg-cod-gray-100/60 dark:bg-cod-gray-800">
                            <button type="button" wire:click="closeCheatPreview"
                                    class="inline-flex justify-center rounded-md border border-cod-gray-300 dark:border-cod-gray-600 shadow-sm px-4 py-2 bg-cod-gray-50 dark:bg-cod-gray-800 text-base font-medium text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-cod-gray-700">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-container>
