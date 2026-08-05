<x-container class="mt-6 sm:mt-10">
    <style>
        @foreach ($fonts as $font)
            @font-face {
                font-family: {!! json_encode($font->family_name) !!};
                src: url({!! json_encode($fontPreviews[$font->id]['url']) !!}) format({!! json_encode($fontPreviews[$font->id]['format']) !!});
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }
        @endforeach
    </style>

    <div class="max-w-5xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl text-cod-gray-900 dark:text-cod-gray-100">Font Manager</h1>
            <p class="mt-2 text-xl text-cod-gray-600 dark:text-cod-gray-400">Choose the global app font and install additional typefaces.</p>
        </div>

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

        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
                <h2 class="text-2xl font-medium text-cod-gray-900 dark:text-cod-gray-100">Installed Fonts</h2>
            </div>

            <div class="divide-y divide-cod-gray-200 dark:divide-cod-gray-700">
                @foreach ($fonts as $font)
                    <div wire:key="font-row-{{ $font->id }}" class="px-6 py-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-2xl text-cod-gray-900 dark:text-cod-gray-100">
                                <span style='font-family: {!! json_encode($font->family_name) !!}, monospace;'>
                                    {{ $font->label }}
                                </span>
                                @if ($font->id === $activeFontId)
                                    <span class="ml-2 text-sm bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-200 px-2 py-0.5 rounded">Active</span>
                                @endif
                                @if ($font->is_bundled)
                                    <span class="ml-2 text-sm bg-cod-gray-200 dark:bg-cod-gray-700 text-cod-gray-700 dark:text-cod-gray-200 px-2 py-0.5 rounded">Bundled</span>
                                @endif
                            </div>
                            <div class="text-xl text-cod-gray-600 dark:text-cod-gray-400">
                                {{ $font->family_name }} · {{ strtoupper($font->format) }} · {{ $font->relative_path }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if ($font->id !== $activeFontId)
                                <button
                                    wire:click="activate({{ $font->id }})"
                                    class="inline-flex items-center px-4 py-1.5 border border-transparent text-xl font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition duration-150 ease-in-out"
                                >
                                    Set Active
                                </button>
                            @endif

                            @unless ($font->is_bundled)
                                <button
                                    wire:click="openDeleteModal({{ $font->id }})"
                                    class="inline-flex items-center px-4 py-1.5 border border-cod-gray-300 dark:border-cod-gray-600 text-xl font-medium rounded-md text-cod-gray-700 dark:text-cod-gray-200 bg-cod-gray-100 dark:bg-cod-gray-700 hover:bg-cod-gray-200 dark:hover:bg-cod-gray-600 transition duration-150 ease-in-out"
                                >
                                    Delete
                                </button>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl text-cod-gray-900 dark:text-cod-gray-100 mb-4">Upload Font</h2>

            <form wire:submit="install" class="space-y-4">
                <div>
                    <label for="font-file" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Font file</label>
                    <input wire:model="fontFile" id="font-file" type="file" accept=".ttf,.otf,.woff,.woff2,font/ttf,font/otf,font/woff,font/woff2"
                        class="form-field w-full px-3 py-2">
                    @error('fontFile')
                        <p class="mt-2 text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="font-label" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">Label</label>
                    <input wire:model="label" id="font-label" type="text"
                        class="form-field w-full px-3 py-2">
                    @error('label')
                        <p class="mt-2 text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="font-family-name" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">CSS family name</label>
                    <input wire:model="familyName" id="font-family-name" type="text"
                        class="form-field w-full px-3 py-2">
                    @error('familyName')
                        <p class="mt-2 text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-1.5 border border-transparent text-xl font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition duration-150 ease-in-out">
                        Install Font
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($showDeleteModal)
        <div
            class="fixed inset-0 z-[100] overflow-y-auto"
            role="dialog"
            aria-modal="true"
            x-data
            x-init="document.body.style.overflow = 'hidden'"
            @keydown.escape.window="$wire.closeDeleteModal()"
        >
            <div class="flex min-h-screen items-center justify-center px-4 pt-20 pb-8">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeDeleteModal" aria-hidden="true"></div>
                <div class="relative w-full max-w-lg rounded-xl bg-cod-gray-50 shadow-2xl dark:bg-cod-gray-900" @click.stop>
                    <div class="flex items-center gap-x-3 px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
                        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40">
                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold text-cod-gray-900 dark:text-cod-gray-100">Delete Font</h3>
                            <p class="text-base font-mono text-cod-gray-500 dark:text-cod-gray-400 mt-0.5 truncate max-w-xs">{{ $deletingFontLabel }}</p>
                        </div>
                    </div>

                    <div class="px-6 py-5">
                        <div class="rounded-md bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 px-4 py-3 text-base text-red-800 dark:text-red-300">
                            This will permanently remove the uploaded font. This action cannot be undone.
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-cod-gray-200 dark:border-cod-gray-700 flex items-center justify-between gap-x-4">
                        <button
                            type="button"
                            wire:click="closeDeleteModal"
                            class="px-4 py-1.5 rounded-md text-xl border border-cod-gray-300 dark:border-cod-gray-600 text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-cod-gray-800 smooth-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="confirmDelete"
                            wire:loading.attr="disabled"
                            wire:target="confirmDelete"
                            class="inline-flex items-center gap-x-2 px-5 py-1.5 rounded-md text-xl font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 smooth-300 disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="confirmDelete" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span wire:loading.remove wire:target="confirmDelete">Delete</span>
                            <span wire:loading wire:target="confirmDelete">Deleting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-container>
