<x-container class="mt-6 sm:mt-10">
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

        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg p-6 mb-6">
            <h2 class="text-2xl text-cod-gray-900 dark:text-cod-gray-100 mb-4">Preview</h2>
            <p class="text-3xl" style="font-family: var(--app-font-family);">
                The quick brown fox jumps over the lazy dog.
            </p>
            <p class="mt-2 text-xl text-cod-gray-600 dark:text-cod-gray-400">
                Active family: {{ $fonts->firstWhere('id', $activeFontId)?->family_name ?? 'Unknown' }}
            </p>
        </div>

        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
                <h2 class="text-2xl font-medium text-cod-gray-900 dark:text-cod-gray-100">Installed Fonts</h2>
            </div>

            <div class="divide-y divide-cod-gray-200 dark:divide-cod-gray-700">
                @foreach ($fonts as $font)
                    <div wire:key="font-row-{{ $font->id }}" class="px-6 py-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-2xl text-cod-gray-900 dark:text-cod-gray-100">
                                {{ $font->label }}
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
                                    wire:click="delete({{ $font->id }})"
                                    wire:confirm="Delete this font?"
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
</x-container>
