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
                    <label for="console-select" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">
                        Select Console
                    </label>
                    <select wire:model.live="selectedConsole" id="console-select" 
                            @change="$dispatch('loader-top-on'); setTimeout(() => $dispatch('loader-top-off'), 500)"
                            class="w-full md:w-[18rem]_ px-3 py-2 border border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        @foreach($consoles as $console)
                            <option value="{{ $console['short_name'] }}">{{ $console['long_name'] }} ({{ $console['short_name'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1">
                    <label for="search" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">
                        Search Games
                    </label>
                    <input wire:model.live.debounce.300ms="searchTerm" type="text" id="search" placeholder="Search by title or publisher..."
                        class="w-full px-3 py-2 border border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 placeholder-cod-gray-500 dark:placeholder-cod-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
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
                    Games for {{ $selectedConsole }} ({{ count($this->filteredGames) }} games)
                </h3>
            </div>

            @if(count($this->filteredGames) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-cod-gray-200 dark:divide-cod-gray-700">
                        <thead class="bg-cod-gray-50 dark:bg-cod-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-base bg-cod-gray-50 dark:bg-cod-gray-700 font-medium text-cod-gray-500 dark:text-cod-gray-400 uppercase tracking-wider">
                                    <div class="flex items-center space-x-4">
                                        <button wire:click="sortBy('title')" @click="$dispatch('loader-top-on'); setTimeout(() => $dispatch('loader-top-off'), 300)" class="flex items-center space-x-1 hover:text-cod-gray-700 dark:hover:text-cod-gray-200 transition-colors">
                                            <span>Title</span>
                                            <div class="flex flex-col">
                                                <svg class="w-3 h-3 {{ $sortField === 'title' && $sortDirection === 'asc' ? 'text-rose-600' : 'text-cod-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg class="w-3 h-3 -mt-1 {{ $sortField === 'title' && $sortDirection === 'desc' ? 'text-rose-600' : 'text-cod-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </button>
                                        <span class="text-cod-gray-300 dark:text-cod-gray-600">|</span>
                                        <button wire:click="sortBy('id')" @click="$dispatch('loader-top-on'); setTimeout(() => $dispatch('loader-top-off'), 300)" class="flex items-center space-x-1 hover:text-cod-gray-700 dark:hover:text-cod-gray-200 transition-colors">
                                            <span>ID</span>
                                            <div class="flex flex-col">
                                                <svg class="w-3 h-3 {{ $sortField === 'id' && $sortDirection === 'asc' ? 'text-rose-600' : 'text-cod-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg class="w-3 h-3 -mt-1 {{ $sortField === 'id' && $sortDirection === 'desc' ? 'text-rose-600' : 'text-cod-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </button>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-base bg-cod-gray-50 dark:bg-cod-gray-700 font-medium text-cod-gray-500 dark:text-cod-gray-400 uppercase tracking-wider">ROM File</th>
                                <th class="px-6 py-3 text-right text-base bg-cod-gray-50 dark:bg-cod-gray-700 font-medium text-cod-gray-500 dark:text-cod-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-cod-gray-50 dark:bg-cod-gray-800 divide-y divide-cod-gray-200 dark:divide-cod-gray-700">
                            @foreach($this->filteredGames as $game)
                                <tr class="hover:bg-rose-200/60 dark:hover:bg-rose-700/20 transition-colors duration-300">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            @if(isset($game['poster']) && $game['poster'])
                                                <img class="h-[7.3rem] w-[5rem] rounded object-cover mr-4 flex-shrink-0" src="{{ $game['poster'] }}" alt="{{ $game['title'] }}">
                                            @else
                                                <div class="h-20 w-16 bg-cod-gray-300 dark:bg-cod-gray-600 rounded mr-4 flex-shrink-0 flex items-center justify-center">
                                                    <svg class="h-8 w-8 text-cod-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xl text-cod-gray-950 dark:text-cod-gray-50 mb-1">{{ $game['title'] }}
                                                <span class="text-cod-gray-500 dark:text-cod-gray-400">(ID: {{ $game['id'] }})</span>
                                                </div>
                                                
                                                <div class="flex flex-wrap items-center gap-4 text-xl text-cod-gray-600 dark:text-cod-gray-300">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1 text-cod-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0h3m2 0h5M9 7h6m-6 4h6m-6 4h6"></path>
                                                        </svg>
                                                        <span class="font-medium">{{ $game['publisher'] }}</span>
                                                    </div>
                                                    
                                                </div>
                                                <div class="flex items-center gap-4">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1 text-cod-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>{{ $game['release_year'] }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1 text-cod-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                    </svg>
                                                    <span class="font-medium text-rose-600 dark:text-rose-400">{{ number_format($game['rating'] * 100, 0) }}%</span>
                                                    <div class="ml-2 w-12 bg-cod-gray-200 dark:bg-cod-gray-600 rounded-full h-1.5">
                                                        <div class="bg-rose-600 h-1.5 rounded-full" style="width: {{ $game['rating'] * 100 }}%"></div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-cod-gray-900 dark:text-cod-gray-100">
                                        <div class="max-w-xs text-xs truncate font-mono" title="{{ $game['rom'] }}">{{ $game['rom'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-xl font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="$dispatch('loader-top-on');" wire:click="openEditModal({{ $game['id'] }})" 
                                                    class="inline-flex items-center px-3 py-1">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="$dispatch('loader-top-on');" wire:click="openDeleteModal({{ $game['id'] }})" 
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
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-4xl mx-auto text-cod-gray-400">
                        ¯\_(ツ)_/¯
                    </div>
                    <h3 class="mt-2 text-xl font-medium text-cod-gray-900 dark:text-cod-gray-100">No games found</h3>
                    <p class="mt-1 text-xl text-cod-gray-500 dark:text-cod-gray-400">
                        @if($searchTerm)
                            No games match your search criteria.
                        @else
                            No games available for this console.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Modal -->
        @if($showModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" 
                 x-data 
                 x-init="document.body.style.overflow = 'hidden'" 
                 x-effect="if (!$el.closest('body')) document.body.style.overflow = 'auto'"
                 @keydown.escape.window="$wire.closeModal()"
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
                                        <h3 class="text-xl leading-6 font-medium text-cod-gray-900 dark:text-cod-gray-100" id="modal-title">
                                            Delete Game
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-xl text-cod-gray-500 dark:text-cod-gray-400">
                                                Are you sure you want to delete "{{ $editingGame['title'] ?? '' }}"? This action cannot be undone.
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
                            <form wire:submit="saveGame" @submit="$dispatch('loader-top-on')" x-data>
                                <div class="bg-cod-gray-50 dark:bg-cod-gray-800 px-4 pt-5 pb-4 sm:p-6 relative">
                                    <!-- Close button positioned at top-right -->
                                    <button type="button" @click="$dispatch('loader-top-on');" wire:click="closeModal" class="absolute top-4 right-4 text-cod-gray-400 hover:text-cod-gray-600 dark:hover:text-cod-gray-200 transition-colors z-10">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Modal header with inline console switcher -->
                                    <div class="mb-4 pr-8">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-xl leading-6 font-medium text-cod-gray-900 dark:text-cod-gray-100">
                                                {{ $modalMode === 'add' ? 'Add New Game' : 'Edit Game' }}
                                            </h3>
                                            <div class="min-w-[14rem]">
                                                <label for="form_console_header" class="sr-only">Console</label>
                                                <select wire:model="formConsole" id="form_console_header"
                                                        class="w-full px-3 py-1.5 border border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                    @foreach($consoles as $consoleOption)
                                                        <option value="{{ $consoleOption['short_name'] }}">{{ $consoleOption['long_name'] }} ({{ $consoleOption['short_name'] }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Basic Information -->
                                        <div class="space-y-4">
                                            <div>
                                                <div class="flex justify-between items-center mb-1">
                                                    <label for="title" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Title <span class="text-rose-600">*</span></label>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" 
                                                                @click="$dispatch('loader-top-on'); $wire.fetchGameDataFromAI()"
                                                                class="inline-flex items-center px-1 border border-transparent text-sm leading-4 font-medium rounded-md transition duration-150 ease-in-out"
                                                                :class="title && title.trim().length > 0 ? 
                                                                    'text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' : 
                                                                    'text-cod-gray-400 bg-cod-gray-300 dark:bg-cod-gray-600 cursor-not-allowed'"
                                                                :disabled="!title || title.trim().length === 0"
                                                                x-data="{ title: $wire.entangle('title') }">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                            </svg>
                                                            <span class="mr-1 text-sm">
                                                                AI Fill
                                                            </span>
                                                        </button>
                                                        <!-- AI Success Message -->
                                                        <div x-data="{ showAiSuccess: false }" 
                                                             @ai-success.window="showAiSuccess = true; setTimeout(() => showAiSuccess = false, 4000)"
                                                             x-show="showAiSuccess" 
                                                             x-transition:enter="transition ease-out duration-300"
                                                             x-transition:enter-start="opacity-0 scale-90"
                                                             x-transition:enter-end="opacity-100 scale-100"
                                                             x-transition:leave="transition ease-in duration-200"
                                                             x-transition:leave-start="opacity-100 scale-100"
                                                             x-transition:leave-end="opacity-0 scale-90"
                                                             class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-md">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            AI data loaded!
                                                        </div>
                                                    </div>
                                                </div>
                                                <input wire:model="title" type="text" id="title" required
                                                    class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @error('title') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Enter the game title, then click "AI Fill" to auto-populate other fields</p>
                                            </div>

                                            <div>
                                                <label for="publisher" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Publisher <span class="text-rose-600">*</span></label>
                                                <input wire:model="publisher" type="text" id="publisher" required
                                                    class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @error('publisher') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="release_year" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Release Year <span class="text-rose-600">*</span></label>
                                                    <input wire:model="release_year" type="number" id="release_year" required min="1970" max="{{ date('Y') + 5 }}"
                                                        class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                    @error('release_year') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                </div>

                                                <div>
                                                    <label for="rating" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Rating (0-1) <span class="text-rose-600">*</span></label>
                                                    <input wire:model="rating" type="number" id="rating" required min="0" max="1" step="0.01"
                                                        class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                    @error('rating') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                            </div>

                                            <div>
                                                <label for="rom" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">ROM File <span class="text-rose-600">*</span></label>
                                                <input wire:model="rom" type="text" id="rom" required
                                                    class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @error('rom') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                
                                                <!-- ROM Validation Error Message -->
                                                <div x-data="{ showRomError: false, romErrorMessage: '' }" 
                                                     @rom-error.window="showRomError = true; romErrorMessage = $event.detail.message; setTimeout(() => { document.getElementById('rom').focus(); document.getElementById('rom').select(); }, 100)"
                                                     x-show="showRomError" 
                                                     x-transition:enter="transition ease-out duration-300"
                                                     x-transition:enter-start="opacity-0 scale-95"
                                                     x-transition:enter-end="opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-200"
                                                     x-transition:leave-start="opacity-100 scale-100"
                                                     x-transition:leave-end="opacity-0 scale-95"
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
                                                <input wire:model="poster" type="text" id="poster" placeholder="https://example.com/image.jpg or /images/games/image.png"
                                                    class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @error('poster') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Enter a full URL or a local path starting with "/"</p>
                                            </div>

                                            <div>
                                                <label for="box" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Box Art URL</label>
                                                <input wire:model="box" type="text" id="box" placeholder="https://example.com/image.jpg or /images/games/image.png"
                                                    class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @error('box') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Enter a full URL or a local path starting with "/"</p>
                                            </div>

                                            <div>
                                                <label for="cartridge" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300">Cartridge URL</label>
                                                <input wire:model="cartridge" type="text" id="cartridge" placeholder="https://example.com/image.jpg or /images/games/image.png"
                                                    class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @error('cartridge') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                                <p class="mt-1 text-sm text-cod-gray-500 dark:text-cod-gray-400">Enter a full URL or a local path starting with "/"</p>
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
                                                class="mt-1 block w-full border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500"></textarea>
                                        @error('description') <p class="mt-1 text-xl text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Genres -->
                                    <div class="mt-6">
                                        <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Genres</label>
                                        @foreach($genres as $index => $genre)
                                            <div class="flex gap-4 mb-2">
                                                <input wire:model="genres.{{ $index }}.name" type="text" placeholder="Genre name"
                                                    class="flex-1 border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                <input wire:model="genres.{{ $index }}.description" type="text" placeholder="Genre description"
                                                    class="flex-[2] border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
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
                                    <div class="mt-6">
                                        <label class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Screenshots</label>
                                        @foreach($screenshots as $index => $screenshot)
                                            <div class="flex gap-4 mb-2">
                                                <input wire:model="screenshots.{{ $index }}" type="text" placeholder="https://example.com/image.jpg or /images/games/image.png"
                                                    class="flex-1 border-cod-gray-300 dark:border-cod-gray-600 rounded-md shadow-sm bg-cod-gray-50 dark:bg-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-100 focus:ring-rose-500 focus:border-rose-500">
                                                @if(count($screenshots) > 1)
                                                    <button type="button" wire:click="removeScreenshot({{ $index }})" 
                                                            class="px-3 py-2 text-red-600 hover:text-red-800">Remove</button>
                                                @endif
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addScreenshot" 
                                                class="mt-2 text-xl text-rose-600 hover:text-rose-800">+ Add Screenshot</button>
                                    </div>
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
    </div> 
</x-container>