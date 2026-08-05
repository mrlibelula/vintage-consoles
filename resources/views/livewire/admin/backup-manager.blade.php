<x-container class="mt-6 sm:mt-10">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-x-3">
                <x-pixelarticon
                    name="save"
                    :size="32"
                    class="text-cod-gray-900 dark:text-cod-gray-100"
                />
                <h1 class="text-3xl text-cod-gray-900 dark:text-cod-gray-100">Backup / Restore</h1>
            </div>
            <p class="mt-2 text-xl text-cod-gray-600 dark:text-cod-gray-400">
                Create ZIP backups of catalog, chat, cheat sheets, and migration docs. Restore replaces data on this server.
            </p>
        </div>

        {{-- ── Create Backup ─────────────────────────────────────────────────── --}}
        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-cod-gray-800 dark:text-cod-gray-200 mb-4">Create Backup</h2>

            <div class="space-y-4">
                {{-- Savestates checkbox --}}
                <label class="flex items-start gap-x-3 cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:model="includeSavestates"
                        class="mt-1 h-4 w-4 rounded border-cod-gray-300 dark:border-cod-gray-600 text-rose-600 focus:ring-rose-500"
                    >
                    <div>
                        <span class="text-xl font-medium text-cod-gray-800 dark:text-cod-gray-200">Include save states</span>
                        <p class="text-base text-cod-gray-500 dark:text-cod-gray-400">
                            Adds <code class="text-xs bg-cod-gray-200 dark:bg-cod-gray-700 px-1 rounded">emulator_save_states</code>,
                            <code class="text-xs bg-cod-gray-200 dark:bg-cod-gray-700 px-1 rounded">emulator_control_settings</code>
                            and <code class="text-xs bg-cod-gray-200 dark:bg-cod-gray-700 px-1 rounded">storage/app/savestates/</code> to the archive.
                            Save-state rows reference <code class="text-xs bg-cod-gray-200 dark:bg-cod-gray-700 px-1 rounded">user_id</code> — they only map correctly
                            when restoring to the same deployment.
                        </p>
                    </div>
                </label>

                {{-- Info note --}}
                <div class="rounded-md bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 px-4 py-3 text-base text-blue-800 dark:text-blue-300">
                    <strong>Never included:</strong> user accounts, roles, sessions — these stay on the target server.
                    Backup contains: catalog (games, consoles, genres), app fonts &amp; settings, migration docs, chat, cheat sheets.
                </div>

                <button
                    wire:click="createBackup"
                    wire:loading.attr="disabled"
                    wire:target="createBackup"
                    class="inline-flex items-center gap-x-2 px-5 py-2 rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 text-xl font-medium transition disabled:opacity-60"
                >
                    <svg wire:loading wire:target="createBackup" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <svg wire:loading.remove wire:target="createBackup" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span wire:loading.remove wire:target="createBackup">Create Backup</span>
                    <span wire:loading wire:target="createBackup">Creating…</span>
                </button>
            </div>
        </div>

        {{-- ── Upload Backup ─────────────────────────────────────────────────── --}}
        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-cod-gray-800 dark:text-cod-gray-200 mb-4">Upload Backup</h2>
            <p class="mb-4 text-xl text-cod-gray-600 dark:text-cod-gray-400">
                Restore from a ZIP previously downloaded from this app (or another deployment).
                Upload stores it in Available Backups — then preview and confirm restore with your password.
            </p>

            <form wire:submit="uploadBackup" class="space-y-4">
                <div>
                    <label for="backup-upload-file" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-2">
                        Backup ZIP file
                    </label>
                    <input
                        id="backup-upload-file"
                        type="file"
                        wire:model="uploadFile"
                        accept=".zip,application/zip,application/x-zip-compressed"
                        class="form-field w-full px-3 py-2 text-xl"
                    >
                    <div wire:loading wire:target="uploadFile" class="mt-2 text-base text-cod-gray-500 dark:text-cod-gray-400">
                        Uploading file…
                    </div>
                    @error('uploadFile')
                        <p class="mt-2 text-base text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="uploadBackup,uploadFile"
                    class="inline-flex items-center gap-x-2 px-5 py-2 rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 text-xl font-medium transition disabled:opacity-60"
                >
                    <svg wire:loading wire:target="uploadBackup" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <svg wire:loading.remove wire:target="uploadBackup" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span wire:loading.remove wire:target="uploadBackup">Upload Backup</span>
                    <span wire:loading wire:target="uploadBackup">Processing…</span>
                </button>
            </form>
        </div>

        {{-- ── Available Backups ──────────────────────────────────────────────── --}}
        <div class="bg-cod-gray-50 dark:bg-cod-gray-800 shadow rounded-lg p-6">
            <div class="mb-4 flex items-baseline justify-between gap-x-4">
                <h2 class="text-2xl font-semibold text-cod-gray-800 dark:text-cod-gray-200">Available Backups</h2>
                @if(count($backups) > 0)
                    <span class="text-base text-cod-gray-500 dark:text-cod-gray-400">{{ count($backups) }} {{ Str::plural('backup', count($backups)) }}</span>
                @endif
            </div>

            @if(count($backups) === 0)
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-cod-gray-200/80 dark:bg-cod-gray-700">
                        <svg class="h-6 w-6 text-cod-gray-500 dark:text-cod-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <p class="text-xl text-cod-gray-600 dark:text-cod-gray-300">No backups yet</p>
                    <p class="mt-1 text-base text-cod-gray-500 dark:text-cod-gray-400">Create one above to get started.</p>
                </div>
            @else
                <div class="overflow-x-auto -mx-2 sm:mx-0 pb-10">
                    <table class="min-w-full divide-y divide-cod-gray-200 dark:divide-cod-gray-700 text-xl">
                        <thead>
                            <tr class="text-left text-base uppercase tracking-wider text-cod-gray-500 dark:text-cod-gray-400">
                                <th class="py-3 px-2 sm:pr-4">File</th>
                                <th class="py-3 pr-4">Date</th>
                                <th class="py-3 pr-4">Size</th>
                                <th class="py-3 pr-4">Save states</th>
                                <th class="py-3 pl-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cod-gray-100 dark:divide-cod-gray-700">
                            @foreach($backups as $backup)
                            <tr wire:key="backup-{{ $backup['filename'] }}" class="hover:bg-cod-gray-100/50 dark:hover:bg-cod-gray-700/40 smooth-300">
                                <td class="py-3 px-2 sm:pr-4 max-w-[14rem] sm:max-w-xs">
                                    <div class="flex items-center gap-x-2.5 min-w-0">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-cod-gray-200/80 dark:bg-cod-gray-700 text-cod-gray-500 dark:text-cod-gray-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                            </svg>
                                        </span>
                                        <span class="font-mono text-base text-cod-gray-700 dark:text-cod-gray-300 truncate" title="{{ $backup['filename'] }}">
                                            {{ $backup['filename'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 whitespace-nowrap text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $backup['created_at']
                                        ? \Illuminate\Support\Carbon::parse($backup['created_at'])->format('M j, Y H:i')
                                        : '—' }}
                                </td>
                                <td class="py-3 pr-4 whitespace-nowrap text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $backup['size_human'] }}
                                </td>
                                <td class="py-3 pr-4">
                                    @if($backup['includes_savestates'])
                                        <span class="inline-flex items-center gap-x-1 px-2 py-0.5 rounded text-base font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300" title="Save states included">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-x-1 px-2 py-0.5 rounded text-base font-medium bg-cod-gray-100 dark:bg-cod-gray-700 text-cod-gray-500 dark:text-cod-gray-400" title="Save states not included">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 pl-2">
                                    <div class="flex items-center justify-end gap-x-1.5 whitespace-nowrap">
                                        <button
                                            type="button"
                                            wire:click="openPreview(@js($backup['filename']))"
                                            wire:loading.attr="disabled"
                                            wire:target="openPreview(@js($backup['filename']))"
                                            data-tooltip="Preview"
                                            aria-label="Preview {{ $backup['filename'] }}"
                                            class="app-tooltip inline-flex items-center justify-center p-1.5 rounded-md border border-cod-gray-300 dark:border-cod-gray-600 text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-cod-gray-700 smooth-300 disabled:opacity-60"
                                        >
                                            <svg wire:loading.remove wire:target="openPreview(@js($backup['filename']))" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg wire:loading wire:target="openPreview(@js($backup['filename']))" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="downloadBackup(@js($backup['filename']))"
                                            wire:loading.attr="disabled"
                                            wire:target="downloadBackup(@js($backup['filename']))"
                                            data-tooltip="Download"
                                            aria-label="Download {{ $backup['filename'] }}"
                                            class="app-tooltip inline-flex items-center justify-center p-1.5 rounded-md border border-sky-400 dark:border-sky-600 text-sky-700 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/30 smooth-300 disabled:opacity-60"
                                        >
                                            <svg wire:loading.remove wire:target="downloadBackup(@js($backup['filename']))" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <svg wire:loading wire:target="downloadBackup(@js($backup['filename']))" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="openRestoreModal(@js($backup['filename']))"
                                            data-tooltip="Restore"
                                            aria-label="Restore {{ $backup['filename'] }}"
                                            class="app-tooltip inline-flex items-center justify-center p-1.5 rounded-md border border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 smooth-300"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="openDeleteModal(@js($backup['filename']))"
                                            wire:loading.attr="disabled"
                                            wire:target="openDeleteModal(@js($backup['filename']))"
                                            data-tooltip="Delete"
                                            aria-label="Delete {{ $backup['filename'] }}"
                                            class="app-tooltip app-tooltip-end inline-flex items-center justify-center p-1.5 rounded-md border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 smooth-300 disabled:opacity-60"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Preview Modal (same pattern as Game Manager — backdrop + panel)            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($showPreviewModal)
<div
    class="fixed inset-0 z-[100] overflow-y-auto"
    role="dialog"
    aria-modal="true"
    x-data
    x-init="document.body.style.overflow = 'hidden'"
    @keydown.escape.window="$wire.closePreview()"
>
    <div class="flex min-h-screen items-start justify-center px-4 pb-8 pt-20 text-center">
        <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm"
            aria-hidden="true"
            wire:click="closePreview"
        ></div>

        <div
            class="relative inline-block w-full max-w-4xl text-left align-middle"
            @click.stop
        >
            <div class="overflow-hidden rounded-xl bg-cod-gray-50 shadow-2xl dark:bg-cod-gray-900">

        @if($isPreviewLoading)
        <div class="px-6 py-16 text-center text-xl text-cod-gray-600 dark:text-cod-gray-400">
            <svg class="mx-auto mb-4 h-8 w-8 animate-spin text-rose-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Loading preview…
        </div>
        @elseif($previewError)
        <div class="px-6 py-10">
            <p class="text-xl text-red-600 dark:text-red-400">{{ $previewError }}</p>
            <button type="button" wire:click="closePreview" class="mt-4 px-4 py-1.5 rounded-md border border-cod-gray-300 dark:border-cod-gray-600 text-xl">
                Close
            </button>
        </div>
        @else
        {{-- Modal header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
            <div>
                <h3 class="text-2xl font-semibold text-cod-gray-900 dark:text-cod-gray-100">Preview Backup</h3>
                <p class="text-base font-mono text-cod-gray-500 dark:text-cod-gray-400 mt-0.5">{{ $previewData['manifest']['filename'] ?? '' }}</p>
            </div>
            <button wire:click="closePreview" class="text-cod-gray-400 hover:text-cod-gray-600 dark:hover:text-cod-gray-200 smooth-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5 space-y-6 overflow-y-auto max-h-[75vh]">

            {{-- Manifest summary --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                @php $manifest = $previewData['manifest'] @endphp
                <div class="bg-cod-gray-100 dark:bg-cod-gray-800 rounded-lg p-3">
                    <div class="text-base text-cod-gray-500 dark:text-cod-gray-400">Created</div>
                    <div class="text-xl font-medium text-cod-gray-800 dark:text-cod-gray-200">
                        {{ $manifest['created_at'] ? \Illuminate\Support\Carbon::parse($manifest['created_at'])->format('M j, Y H:i') : '—' }}
                    </div>
                </div>
                <div class="bg-cod-gray-100 dark:bg-cod-gray-800 rounded-lg p-3">
                    <div class="text-base text-cod-gray-500 dark:text-cod-gray-400">Size</div>
                    <div class="text-xl font-medium text-cod-gray-800 dark:text-cod-gray-200">{{ $manifest['file_size'] }}</div>
                </div>
                <div class="bg-cod-gray-100 dark:bg-cod-gray-800 rounded-lg p-3">
                    <div class="text-base text-cod-gray-500 dark:text-cod-gray-400">Save states</div>
                    <div class="text-xl font-medium {{ $manifest['includes_savestates'] ? 'text-green-600 dark:text-green-400' : 'text-cod-gray-500 dark:text-cod-gray-400' }}">
                        {{ $manifest['includes_savestates'] ? 'Included' : 'Not included' }}
                    </div>
                </div>
                <div class="bg-cod-gray-100 dark:bg-cod-gray-800 rounded-lg p-3">
                    <div class="text-base text-cod-gray-500 dark:text-cod-gray-400">Schema</div>
                    <div class="text-xl font-medium text-cod-gray-800 dark:text-cod-gray-200">v{{ $manifest['version'] ?? '?' }}</div>
                </div>
            </div>

            {{-- DB comparison --}}
            <div>
                <h4 class="text-xl font-semibold text-cod-gray-800 dark:text-cod-gray-200 mb-3">Database Tables</h4>
                <div class="overflow-x-auto rounded-lg border border-cod-gray-200 dark:border-cod-gray-700">
                    <table class="min-w-full divide-y divide-cod-gray-200 dark:divide-cod-gray-700 text-xl">
                        <thead class="bg-cod-gray-100 dark:bg-cod-gray-800">
                            <tr class="text-left text-base uppercase tracking-wider text-cod-gray-500 dark:text-cod-gray-400">
                                <th class="px-4 py-2">Table</th>
                                <th class="px-4 py-2 text-right">In backup</th>
                                <th class="px-4 py-2 text-right">On server</th>
                                <th class="px-4 py-2 text-right">Diff</th>
                                <th class="px-4 py-2">Action on restore</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cod-gray-100 dark:divide-cod-gray-700 bg-white dark:bg-cod-gray-900">
                            @foreach($previewData['db'] as $table => $row)
                            <tr>
                                <td class="px-4 py-2 font-mono text-base text-cod-gray-700 dark:text-cod-gray-300">{{ $table }}</td>
                                <td class="px-4 py-2 text-right text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $row['in_backup'] ? number_format($row['backup_rows']) : '—' }}
                                </td>
                                <td class="px-4 py-2 text-right text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ number_format($row['current_rows']) }}
                                </td>
                                <td class="px-4 py-2 text-right font-medium">
                                    @if(! $row['in_backup'])
                                        <span class="text-cod-gray-400 dark:text-cod-gray-500">—</span>
                                    @elseif($row['diff'] > 0)
                                        <span class="text-green-600 dark:text-green-400">+{{ $row['diff'] }}</span>
                                    @elseif($row['diff'] < 0)
                                        <span class="text-red-500 dark:text-red-400">{{ $row['diff'] }}</span>
                                    @else
                                        <span class="text-cod-gray-400 dark:text-cod-gray-500">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-base">
                                    @if(! $row['in_backup'])
                                        <span class="text-cod-gray-400 dark:text-cod-gray-500">Unchanged</span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">Replaced</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- File diffs --}}
            @foreach([
                'migration-docs' => 'Migration Docs (storage/app/migration-docs/)',
                'chat'           => 'Chat Files (storage/data/chat/)',
                'cheats'         => 'Cheat Sheets (storage/data/cheats/)',
                'savestates'     => 'Save State Files (storage/app/savestates/)',
            ] as $key => $label)
            @php $section = $previewData['files'][$key] ?? null @endphp

            <div>
                <h4 class="text-xl font-semibold text-cod-gray-800 dark:text-cod-gray-200 mb-2">{{ $label }}</h4>
                @if($section === null)
                    <p class="text-base text-cod-gray-400 dark:text-cod-gray-500">Not included in this backup — will not be changed on restore.</p>
                @else
                    @if(! empty($section['truncated']))
                        <p class="text-base text-cod-gray-500 dark:text-cod-gray-400 mb-2">
                            Showing first 50 files per category.
                            Totals: {{ $section['totals']['only_in_backup'] ?? 0 }} only in backup,
                            {{ $section['totals']['in_both'] ?? 0 }} in both,
                            {{ $section['totals']['only_on_disk'] ?? 0 }} only on server.
                        </p>
                    @endif
                    <div class="grid sm:grid-cols-3 gap-3 text-base">
                        <div class="flex h-52 flex-col rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-950/30">
                            <div class="mb-1 shrink-0 font-medium text-green-700 dark:text-green-400">Only in backup ({{ $section['totals']['only_in_backup'] ?? count($section['only_in_backup']) }})</div>
                            <div class="min-h-0 flex-1 overflow-y-auto">
                                @if(empty($section['only_in_backup']))
                                    <span class="text-cod-gray-400 dark:text-cod-gray-500">None</span>
                                @else
                                    <ul class="space-y-0.5 text-green-800 dark:text-green-300">
                                        @foreach($section['only_in_backup'] as $f)<li class="font-mono truncate">{{ $f }}</li>@endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        <div class="flex h-52 flex-col rounded-lg border border-cod-gray-200 bg-cod-gray-100 p-3 dark:border-cod-gray-700 dark:bg-cod-gray-800">
                            <div class="mb-1 shrink-0 font-medium text-cod-gray-600 dark:text-cod-gray-400">In both ({{ $section['totals']['in_both'] ?? count($section['in_both']) }})</div>
                            <div class="min-h-0 flex-1 overflow-y-auto">
                                @if(empty($section['in_both']))
                                    <span class="text-cod-gray-400 dark:text-cod-gray-500">None</span>
                                @else
                                    <ul class="space-y-0.5 text-cod-gray-700 dark:text-cod-gray-300">
                                        @foreach($section['in_both'] as $f)<li class="font-mono truncate">{{ $f }}</li>@endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        <div class="flex h-52 flex-col rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-950/30">
                            <div class="mb-1 shrink-0 font-medium text-red-700 dark:text-red-400">Only on server ({{ $section['totals']['only_on_disk'] ?? count($section['only_on_disk']) }})</div>
                            <div class="min-h-0 flex-1 overflow-y-auto">
                                @if(empty($section['only_on_disk']))
                                    <span class="text-cod-gray-400 dark:text-cod-gray-500">None</span>
                                @else
                                    <ul class="space-y-0.5 text-red-800 dark:text-red-300">
                                        @foreach($section['only_on_disk'] as $f)<li class="font-mono truncate">{{ $f }}</li>@endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @endforeach

        </div>{{-- /scroll area --}}

        {{-- Modal footer --}}
        <div class="px-6 py-4 border-t border-cod-gray-200 dark:border-cod-gray-700 flex items-center justify-between gap-x-4">
            <button wire:click="closePreview" class="px-4 py-1.5 rounded-md text-xl border border-cod-gray-300 dark:border-cod-gray-600 text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-cod-gray-800 smooth-300">
                Close
            </button>
            <button
                type="button"
                wire:click="openRestoreModal(@js($previewingFile))"
                class="px-5 py-1.5 rounded-md text-xl font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 smooth-300"
            >
                Restore This Backup
            </button>
        </div>
        @endif

            </div>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Delete Confirmation Modal                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($showDeleteModal)
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
                <h3 class="text-2xl font-semibold text-cod-gray-900 dark:text-cod-gray-100">Delete Backup</h3>
                <p class="text-base font-mono text-cod-gray-500 dark:text-cod-gray-400 mt-0.5 truncate max-w-xs">{{ $deletingFile }}</p>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="rounded-md bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 px-4 py-3 text-base text-red-800 dark:text-red-300">
                This will permanently remove the backup file from the server. This action cannot be undone.
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

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Restore Confirmation Modal (password-gated)                                --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($showRestoreModal)
<div
    class="fixed inset-0 z-[100] overflow-y-auto"
    role="dialog"
    aria-modal="true"
    x-data
    x-init="document.body.style.overflow = 'hidden'"
    @keydown.escape.window="$wire.closeRestoreModal()"
>
    <div class="flex min-h-screen items-center justify-center px-4 pt-20 pb-8">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeRestoreModal" aria-hidden="true"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-cod-gray-50 shadow-2xl dark:bg-cod-gray-900" @click.stop>
        {{-- Header --}}
        <div class="flex items-center gap-x-3 px-6 py-4 border-b border-cod-gray-200 dark:border-cod-gray-700">
            <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40">
                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-cod-gray-900 dark:text-cod-gray-100">Confirm Restore</h3>
                <p class="text-base font-mono text-cod-gray-500 dark:text-cod-gray-400 mt-0.5 truncate max-w-xs">{{ $restoringFile }}</p>
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div
                wire:loading
                wire:target="confirmRestore"
                class="rounded-md border border-sky-200 bg-sky-50 px-4 py-3 text-base text-sky-800 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-300"
            >
                Restoring catalog and files… this can take a moment. The dialog will close when finished.
            </div>

            <div class="space-y-4" wire:loading.class="opacity-60 pointer-events-none" wire:target="confirmRestore">
                {{-- Warning --}}
                <div class="rounded-md bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 px-4 py-3 text-base text-amber-800 dark:text-amber-300">
                    <strong>This will overwrite current catalog, migration docs, chat, and cheat sheet data</strong> on this server with the contents of the backup.
                    User accounts will <strong>not</strong> be changed.
                    @if($restoringFile && str_contains($restoringFile, '_no-saves'))
                        Save states will <strong>not</strong> be touched.
                    @else
                        If this backup includes save states, they will be <strong>replaced</strong>.
                    @endif
                    All users will be notified.
                </div>

                {{-- Password field --}}
                <div>
                    <label for="restore-password" class="block text-xl font-medium text-cod-gray-700 dark:text-cod-gray-300 mb-1">
                        Your admin password
                    </label>
                    <input
                        id="restore-password"
                        type="password"
                        wire:model="restorePassword"
                        wire:keydown.enter="confirmRestore"
                        wire:loading.attr="disabled"
                        wire:target="confirmRestore"
                        autocomplete="current-password"
                        placeholder="Enter your password to confirm"
                        class="form-field w-full px-3 py-2 text-xl {{ $restorePasswordError ? 'border-red-500 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:focus:border-red-500 dark:focus:ring-red-500' : '' }}"
                    >
                    @if($restorePasswordError)
                        <p class="mt-1 text-base text-red-600 dark:text-red-400">{{ $restorePasswordError }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-cod-gray-200 dark:border-cod-gray-700 flex items-center justify-between gap-x-4">
            <button
                type="button"
                wire:click="closeRestoreModal"
                wire:loading.attr="disabled"
                wire:target="confirmRestore"
                class="px-4 py-1.5 rounded-md text-xl border border-cod-gray-300 dark:border-cod-gray-600 text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-cod-gray-800 smooth-300 disabled:opacity-60"
            >
                Cancel
            </button>
            <button
                type="button"
                wire:click="confirmRestore"
                wire:loading.attr="disabled"
                wire:target="confirmRestore"
                class="inline-flex items-center gap-x-2 px-5 py-1.5 rounded-md text-xl font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 smooth-300 disabled:opacity-60"
            >
                <svg wire:loading wire:target="confirmRestore" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span wire:loading.remove wire:target="confirmRestore">Restore Now</span>
                <span wire:loading wire:target="confirmRestore">Restoring…</span>
            </button>
        </div>
        </div>
    </div>
</div>
@endif

    </div>{{-- /max-w-7xl --}}
</x-container>
