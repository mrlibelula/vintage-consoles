const PANEL_ID = 'vintage-save-state-panel'
const STORAGE_PREFIXES = ['emulatorjs', 'EJS', 'vintage.gamepad']
const SAVE_STATE_CACHE_NAME = 'vintage-save-states-v1'
const ICONS = {
    close: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>',
    panelToggle:
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l3 3v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M8 3v4h6V3"/><path d="M8 14h8"/><path d="M8 17h5"/><path d="M12 18v2.5"/><path d="M10 19.5l2 2 2-2"/></svg>',
    save: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h11l3 3v13H5V4z"/><path d="M8 4v6h8V6"/><path d="M8 16h8v4"/></svg>',
    load: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h6l2 2h8v10a2 2 0 0 1-2 2H4V6z"/><path d="M12 11v6"/><path d="M9 14l3 3 3-3"/></svg>',
    clear: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M7 10v9h10v-9"/><path d="M10 12v5"/><path d="M14 12v5"/></svg>',
    sync: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h9l-2-2"/><path d="M16 7l-2 2"/><path d="M19 12a7 7 0 0 0-12-5"/><path d="M17 17H8l2 2"/><path d="M8 17l2-2"/><path d="M5 12a7 7 0 0 0 12 5"/></svg>',
    upload:
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
}

function toQuery(params) {
    return new URLSearchParams(params).toString()
}

function toBytes(data) {
    if (data instanceof Uint8Array) {
        return data
    }

    if (data instanceof ArrayBuffer) {
        return new Uint8Array(data)
    }

    return new Uint8Array(data || [])
}

function playerFetch(input, init = {}) {
    const fetcher = window.VintagePlayerFetch || window.fetch.bind(window)

    return fetcher(input, init)
}

function canUseCacheStorage() {
    return typeof window !== 'undefined'
        && typeof window.caches !== 'undefined'
        && typeof window.caches.open === 'function'
}

function cacheVersionForSave(save) {
    if (save?.checksum) {
        return `checksum:${String(save.checksum)}`
    }

    if (save?.updated_at) {
        return `updated_at:${String(save.updated_at)}`
    }

    return ''
}

function cacheKeyForSave(save) {
    const version = cacheVersionForSave(save)
    return `${save.download_url}#v=${encodeURIComponent(version)}`
}

function downloadUrlForSave(save) {
    const version = cacheVersionForSave(save)
    if (!save?.download_url) {
        return ''
    }

    try {
        const url = new URL(save.download_url, window.location.origin)
        if (version) {
            url.searchParams.set('v', version)
        }
        return url.toString()
    } catch {
        // If a non-URL slips through, just fall back.
        return save.download_url
    }
}

async function readSaveFromCache(save) {
    if (!canUseCacheStorage() || !save?.download_url) {
        return null
    }

    const cache = await window.caches.open(SAVE_STATE_CACHE_NAME)
    const match = await cache.match(cacheKeyForSave(save))
    if (!match) {
        return null
    }

    return new Uint8Array(await match.arrayBuffer())
}

async function writeSaveToCache(save, bytes) {
    if (!canUseCacheStorage() || !save?.download_url) {
        return
    }

    const cache = await window.caches.open(SAVE_STATE_CACHE_NAME)
    const response = new Response(new Blob([bytes], { type: 'application/octet-stream' }), {
        headers: {
            'Content-Type': 'application/octet-stream',
        },
    })
    await cache.put(cacheKeyForSave(save), response)
}

async function purgeOldCachedSaveVersions(save) {
    if (!canUseCacheStorage() || !save?.download_url) {
        return
    }

    const cache = await window.caches.open(SAVE_STATE_CACHE_NAME)
    const keys = await cache.keys()
    const prefix = `${save.download_url}#v=`
    const currentKey = cacheKeyForSave(save)

    await Promise.all(keys.map(async request => {
        const url = request?.url ? String(request.url) : ''
        if (url.startsWith(prefix) && url !== currentKey) {
            await cache.delete(request)
        }
    }))
}

async function purgeAllCachedSaveVersions(save) {
    if (!canUseCacheStorage() || !save?.download_url) {
        return
    }

    const cache = await window.caches.open(SAVE_STATE_CACHE_NAME)
    const keys = await cache.keys()
    const prefix = `${save.download_url}#v=`

    await Promise.all(keys.map(async request => {
        const url = request?.url ? String(request.url) : ''
        if (url.startsWith(prefix)) {
            await cache.delete(request)
        }
    }))
}

function getLocalStorageSnapshot(config) {
    const keys = Object.keys(window.localStorage || {})
    const markers = [
        config.gameId,
        config.gameTitle,
        config.console,
    ].filter(Boolean).map(value => String(value).toLowerCase())

    return keys.reduce((settings, key) => {
        const lowerKey = key.toLowerCase()
        const matchesPrefix = STORAGE_PREFIXES.some(prefix => lowerKey.startsWith(prefix.toLowerCase()))
        const matchesGame = markers.some(marker => lowerKey.includes(marker))

        if (matchesPrefix || matchesGame) {
            settings[key] = window.localStorage.getItem(key)
        }

        return settings
    }, {})
}

function applyLocalStorageSnapshot(settings) {
    let gamepadMappings = null

    Object.entries(settings || {}).forEach(([key, value]) => {
        if (value === null || typeof value === 'undefined') {
            window.localStorage.removeItem(key)
            return
        }

        window.localStorage.setItem(key, value)

        if (key === 'vintage.gamepad.mappings') {
            try {
                gamepadMappings = JSON.parse(value)
            } catch (error) {
                console.warn('Synced gamepad mappings could not be parsed.', error)
            }
        }
    })

    if (gamepadMappings) {
        window.dispatchEvent(new CustomEvent('vintage-gamepad:mappings-restored', {
            detail: gamepadMappings,
        }))
    }
}

export class SaveStateManager {
    constructor(config = {}, adapter = {}) {
        this.config = config
        this.adapter = adapter
        this.saves = []
        this.status = null
        this.panel = null
        this.toastContainer = null
        this.currentSlot = 1
        this.pendingUploadSlot = null
        this.stateDownloadIndicator = null
        this.keydownHandler = event => this.handleKeydown(event)
        this.pointerDownHandler = event => this.handlePointerDown(event)
        this.fullscreenChangeHandler = () => this.handleFullscreenChange()
        this.panelHome = null
        this.iframeKeyTarget = null
        this.iframeKeyRetryTimer = null
    }

    async init() {
        this.createPanel()
        document.addEventListener('keydown', this.keydownHandler, true)
        document.addEventListener('pointerdown', this.pointerDownHandler, true)
        document.addEventListener('fullscreenchange', this.fullscreenChangeHandler)
        document.addEventListener('webkitfullscreenchange', this.fullscreenChangeHandler)
        this.panelHome = this.panel?.parentNode || document.body
        this.createStateDownloadIndicator()
        this.handleFullscreenChange()
        this.attachIframeKeyListener()
        await Promise.all([
            this.refreshSaves(),
            this.restoreControlSettings(),
        ])
    }

    saveStateParams() {
        return {
            console: this.config.console,
            game_slug: this.config.gameSlug,
        }
    }

    controlSettingsParams() {
        return {
            console: this.config.console,
            game_id: this.config.gameId,
            emulator: this.config.emulator,
        }
    }

    async request(url, options = {}) {
        const headers = {
            Accept: 'application/json',
            ...(options.headers || {}),
        }

        if (this.config.csrfToken && options.method && options.method !== 'GET') {
            headers['X-CSRF-TOKEN'] = this.config.csrfToken
        }

        const response = await playerFetch(url, {
            credentials: 'same-origin',
            ...options,
            headers,
        })

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`)
        }

        return response
    }

    async refreshSaves() {
        if (!this.config.authenticated) {
            this.saves = []
            this.renderSlots()
            return
        }

        const url = `${this.config.endpoints.saveStates}?${toQuery(this.saveStateParams())}`
        const response = await this.request(url)
        const payload = await response.json()
        this.saves = payload.data || []
        this.renderSlots()
    }

    async saveSlot(slot, { notify = false } = {}) {
        this.selectSlot(slot)
        const existingSave = this.saves.find(item => item.slot === slot) || null

        if (!this.config.authenticated) {
            this.setStatus('Log in to save to your account.')
            this.notify('Log in to save to your account.', 'warning', notify)
            return
        }

        if (!this.adapter.captureState) {
            this.setStatus('Save state is not ready yet.')
            this.notify('Save state is not ready yet.', 'warning', notify)
            return
        }

        try {
            this.setStatus(`Saving slot ${slot}...`)
            const bytes = toBytes(await this.adapter.captureState())
            const formData = new FormData()

            Object.entries(this.saveStateParams()).forEach(([key, value]) => formData.append(key, value))
            formData.append('slot', slot)
            formData.append('state', new Blob([bytes], { type: 'application/octet-stream' }), `slot-${slot}.state`)

            await this.request(this.config.endpoints.saveStates, {
                method: 'POST',
                body: formData,
            })
            await this.saveControlSettings({ silent: true })
            await this.refreshSaves()
            const latestSave = this.saves.find(item => item.slot === slot) || null
            await purgeAllCachedSaveVersions(existingSave || latestSave)
            this.setStatus(`Saved slot ${slot} to server.`)
            this.notify(`Saved slot ${slot}`, 'success', notify)
        } catch (error) {
            console.error(error)
            this.setStatus(`Could not save slot ${slot}.`)
            this.notify(`Could not save slot ${slot}`, 'error', notify)
        }
    }

    /**
     * Upload a `.state` file from the user's device into a cloud slot (same API as in-emulator save).
     */
    async uploadFileToSlot(slot, file, { notify = true } = {}) {
        this.selectSlot(slot)
        const existingSave = this.saves.find(item => item.slot === slot) || null

        if (!this.config.authenticated) {
            this.setStatus('Log in to upload saves.')
            this.notify('Log in to upload saves.', 'warning', notify)
            return
        }

        if (!(file instanceof File)) {
            return
        }

        const maxBytes = 102400 * 1024
        if (file.size > maxBytes) {
            this.setStatus('File is too large.')
            this.notify('That file is too large (max 100 MB).', 'error', notify)
            return
        }

        const save = this.saves.find(item => item.slot === slot)
        if (save) {
            const confirmed = window.confirm(
                `Replace the cloud save in slot ${slot}? This cannot be undone.`,
            )

            if (!confirmed) {
                return
            }
        }

        try {
            this.setStatus(`Uploading slot ${slot}...`)
            const formData = new FormData()

            Object.entries(this.saveStateParams()).forEach(([key, value]) => formData.append(key, value))
            formData.append('slot', slot)

            const name = /\.state$/i.test(file.name) ? file.name : `${file.name.replace(/[/\\\\]/g, '_')}.state`
            formData.append('state', file, name)

            await this.request(this.config.endpoints.saveStates, {
                method: 'POST',
                body: formData,
            })
            await this.saveControlSettings({ silent: true })
            await this.refreshSaves()
            const latestSave = this.saves.find(item => item.slot === slot) || null
            await purgeAllCachedSaveVersions(existingSave || latestSave)
            this.setStatus(`Uploaded slot ${slot} to server.`)
            this.notify(`Uploaded slot ${slot}`, 'success', notify)
        } catch (error) {
            console.error(error)
            this.setStatus(`Could not upload slot ${slot}.`)
            this.notify(`Could not upload slot ${slot}`, 'error', notify)
        }
    }

    promptUploadFromDisk(slot) {
        if (!this.config.authenticated) {
            this.setStatus('Log in to upload saves.')
            this.notify('Log in to upload saves.', 'warning', true)
            return
        }

        this.selectSlot(slot, { notify: false })
        this.pendingUploadSlot = slot
        const input = this.panel?.querySelector('.vintage-save-state-file-input')

        if (input) {
            input.value = ''
            input.click()
        }
    }

    async loadSlot(slot, { notify = false } = {}) {
        this.selectSlot(slot)
        const save = this.saves.find(item => item.slot === slot)

        if (!save) {
            this.setStatus(`Slot ${slot} is empty.`)
            this.notify(`Slot ${slot} is empty`, 'warning', notify)
            return
        }

        if (!this.adapter.restoreState) {
            this.setStatus('Load state is not ready yet.')
            this.notify('Load state is not ready yet.', 'warning', notify)
            return
        }

        try {
            this.setStatus(`Loading slot ${slot}...`)
            let bytes = await readSaveFromCache(save)
            const usedCache = Boolean(bytes)

            if (!bytes) {
                this.setStateDownloadIndicatorVisible(true)
                const response = await playerFetch(downloadUrlForSave(save), { credentials: 'same-origin' })

                if (!response.ok) {
                    throw new Error(`Download failed with status ${response.status}`)
                }

                bytes = new Uint8Array(await response.arrayBuffer())
                this.setStateDownloadIndicatorVisible(false)
                await writeSaveToCache(save, bytes)
            }

            await purgeOldCachedSaveVersions(save)
            await this.adapter.restoreState(bytes, save)
            await this.restoreControlSettings({ silent: true })
            this.setStatus(`Loaded slot ${slot}${usedCache ? ' (cached)' : ''}.`)
            this.notify(`Loaded slot ${slot}`, 'success', notify)
        } catch (error) {
            console.error(error)
            this.setStatus(`Could not load slot ${slot}.`)
            this.notify(`Could not load slot ${slot}`, 'error', notify)
            this.setStateDownloadIndicatorVisible(false)
        }
    }

    async deleteSlot(slot, { notify = false } = {}) {
        this.selectSlot(slot)
        const save = this.saves.find(item => item.slot === slot)

        if (!save) {
            this.setStatus(`Slot ${slot} is already empty.`)
            this.notify(`Slot ${slot} is already empty`, 'warning', notify)
            return
        }

        const confirmed = await this.confirm({
            title: `Clear slot ${slot}?`,
            message: 'This will permanently remove the cloud save from server storage. This cannot be undone.',
            confirmLabel: 'Clear slot',
            cancelLabel: 'Cancel',
            tone: 'danger',
        })

        if (!confirmed) {
            return
        }

        try {
            this.setStatus(`Clearing slot ${slot}...`)
            await this.request(save.delete_url, {
                method: 'DELETE',
            })
            await purgeAllCachedSaveVersions(save)
            await this.refreshSaves()
            this.setStatus(`Cleared slot ${slot}.`)
            this.notify(`Cleared slot ${slot}`, 'success', notify)
        } catch (error) {
            console.error(error)
            this.setStatus(`Could not clear slot ${slot}.`)
            this.notify(`Could not clear slot ${slot}`, 'error', notify)
        }
    }

    selectSlot(slot, { notify = false } = {}) {
        const slots = Number(this.config.slots || 5)
        this.currentSlot = Math.min(Math.max(Number(slot) || 1, 1), slots)
        this.renderSlots()
        this.notify(`Selected slot ${this.currentSlot}`, 'info', notify)
    }

    handleKeydown(event) {
        if (event.key === 'Escape') {
            this.closeHelpDialog()
            this.closeUploadDialog()
            this.closeConfirmDialog()
            return
        }

        if (this.isEditableTarget(event.target)) {
            return
        }

        const key = event.key.toLowerCase()

        if (event.ctrlKey && event.altKey && /^[1-5]$/.test(key)) {
            event.preventDefault()
            this.selectSlot(Number(key), { notify: true })
            this.setStatus(`Selected slot ${this.currentSlot}.`)
            return
        }

        if (event.ctrlKey && key === 's') {
            event.preventDefault()
            this.saveSlot(this.currentSlot, { notify: true })
            return
        }

        if (event.ctrlKey && key === 'l') {
            event.preventDefault()
            this.loadSlot(this.currentSlot, { notify: true })
            return
        }

        if (event.ctrlKey && (event.key === 'Delete' || event.key === 'Backspace')) {
            event.preventDefault()
            this.deleteSlot(this.currentSlot, { notify: true })
        }
    }

    openHelpDialog() {
        this.panel.querySelector('.vintage-save-help-dialog')?.removeAttribute('hidden')
    }

    closeHelpDialog() {
        this.panel?.querySelector('.vintage-save-help-dialog')?.setAttribute('hidden', '')
    }

    openUploadDialog() {
        if (!this.config.authenticated) {
            this.setStatus('Log in to upload saves.')
            this.notify('Log in to upload saves.', 'warning', true)
            return
        }

        this.pendingUploadSlot = this.currentSlot
        this.syncUploadDialog()
        this.panel.querySelector('.vintage-save-upload-dialog')?.removeAttribute('hidden')
    }

    closeUploadDialog() {
        this.panel?.querySelector('.vintage-save-upload-dialog')?.setAttribute('hidden', '')
    }

    closeConfirmDialog() {
        if (typeof this.activeConfirmCancel === 'function') {
            this.activeConfirmCancel()
        }
    }

    confirm({
        title = 'Are you sure?',
        message = '',
        confirmLabel = 'Confirm',
        cancelLabel = 'Cancel',
        tone = 'danger',
    } = {}) {
        return new Promise(resolve => {
            const dialog = this.panel?.querySelector('.vintage-save-confirm-dialog')

            if (!dialog) {
                resolve(window.confirm(`${title}\n\n${message}`))
                return
            }

            // If another confirm is already open, cancel it first.
            if (typeof this.activeConfirmCancel === 'function') {
                this.activeConfirmCancel()
            }

            const titleEl = dialog.querySelector('.vintage-save-confirm-title')
            const messageEl = dialog.querySelector('.vintage-save-confirm-message')
            const confirmBtn = dialog.querySelector('.vintage-save-confirm-confirm')
            const cancelBtn = dialog.querySelector('.vintage-save-confirm-cancel')
            const closeBtn = dialog.querySelector('.vintage-save-confirm-close')

            titleEl.textContent = title
            messageEl.textContent = message
            confirmBtn.textContent = confirmLabel
            cancelBtn.textContent = cancelLabel
            confirmBtn.dataset.tone = tone

            const cleanup = result => {
                dialog.setAttribute('hidden', '')
                confirmBtn.removeEventListener('click', onConfirm)
                cancelBtn.removeEventListener('click', onCancel)
                closeBtn.removeEventListener('click', onCancel)
                dialog.removeEventListener('click', onBackdrop)
                this.activeConfirmCancel = null
                resolve(result)
            }

            const onConfirm = () => cleanup(true)
            const onCancel = () => cleanup(false)
            const onBackdrop = event => {
                if (event.target === dialog) {
                    cleanup(false)
                }
            }

            confirmBtn.addEventListener('click', onConfirm)
            cancelBtn.addEventListener('click', onCancel)
            closeBtn.addEventListener('click', onCancel)
            dialog.addEventListener('click', onBackdrop)

            this.activeConfirmCancel = onCancel
            dialog.removeAttribute('hidden')
            window.requestAnimationFrame(() => confirmBtn.focus())
        })
    }

    syncUploadDialog() {
        if (!this.panel || !this.config.authenticated) {
            return
        }

        const slots = Number(this.config.slots || 5)
        const picker = this.panel.querySelector('.vintage-save-upload-slot-picker')
        const summary = this.panel.querySelector('.vintage-save-upload-summary')
        const pending = this.pendingUploadSlot ?? this.currentSlot
        const selectedSlot = Math.min(Math.max(Number(pending) || 1, 1), slots)
        this.pendingUploadSlot = selectedSlot

        if (picker) {
            picker.querySelectorAll('button[data-slot]').forEach(button => {
                const slot = Number(button.getAttribute('data-slot'))
                button.classList.toggle('is-selected', slot === selectedSlot)
                button.setAttribute('aria-pressed', String(slot === selectedSlot))
            })
        }

        if (summary) {
            const save = this.saves.find(item => item.slot === selectedSlot)
            summary.textContent = save
                ? `Uploading will replace the cloud save in slot ${selectedSlot}.`
                : `Uploading will create a new cloud save in slot ${selectedSlot}.`
        }
    }

    isEditableTarget(target) {
        if (!target) {
            return false
        }

        const tagName = target.tagName?.toLowerCase()

        return target.isContentEditable
            || ['input', 'select', 'textarea'].includes(tagName)
    }

    captureControlSettings() {
        if (this.adapter.captureControls) {
            return this.adapter.captureControls()
        }

        return {
            localStorage: getLocalStorageSnapshot(this.config),
        }
    }

    async restoreControlSettings({ silent = false } = {}) {
        if (!this.config.authenticated) {
            return
        }

        try {
            const params = {
                ...this.controlSettingsParams(),
                profile: 'default',
            }
            const response = await this.request(`${this.config.endpoints.controlSettings}?${toQuery(params)}`)
            const payload = await response.json()

            if (!payload.data?.settings) {
                return
            }

            if (this.adapter.restoreControls) {
                await this.adapter.restoreControls(payload.data.settings)
            } else {
                applyLocalStorageSnapshot(payload.data.settings.localStorage)
            }

            if (!silent) {
                this.setStatus('Control settings restored.')
            }
        } catch (error) {
            console.warn('Control settings could not be restored.', error)
        }
    }

    async saveControlSettings({ silent = false } = {}) {
        if (!this.config.authenticated) {
            this.setStatus('Log in to sync control settings.')
            return
        }

        try {
            await this.request(this.config.endpoints.controlSettings, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...this.controlSettingsParams(),
                    profile: 'default',
                    settings: this.captureControlSettings(),
                }),
            })

            if (!silent) {
                this.setStatus('Control settings synced.')
            }
        } catch (error) {
            console.error(error)
            this.setStatus('Could not sync control settings.')
        }
    }

    createPanel() {
        if (document.getElementById(PANEL_ID)) {
            this.panel = document.getElementById(PANEL_ID)
            return
        }

        const panel = document.createElement('div')
        panel.id = PANEL_ID
        panel.innerHTML = `
            <button type="button" class="vintage-save-state-toggle" aria-expanded="false" aria-controls="vintage-save-state-body" aria-label="Saves" title="Saves">
                <span class="vintage-save-toggle-when-closed">${ICONS.panelToggle}</span>
                <span class="vintage-save-toggle-when-open">${ICONS.close}</span>
            </button>
            <div class="vintage-save-state-backdrop" hidden></div>
            <div id="vintage-save-state-body" class="vintage-save-state-body" hidden>
                <div class="vintage-save-state-heading">
                    <div class="vintage-save-state-title">
                        <i class="fa fa-cloud-upload vintage-save-state-title-fa" aria-hidden="true"></i>
                        <span>Cloud Save Slots</span>
                    </div>
                    <div class="vintage-save-state-heading-actions">
                        <button type="button" class="vintage-save-state-close" aria-label="Close save menu" title="Close">${ICONS.close}</button>
                        ${this.config.authenticated ? `
                            <button type="button" class="vintage-upload-link" aria-label="Upload save file" title="Upload a .state file">${ICONS.upload}</button>
                            <button type="button" class="vintage-help-link">Hotkeys</button>
                        ` : ''}
                    </div>
                </div>
                <div class="vintage-save-state-message"></div>
                <div class="vintage-save-state-slots"></div>
                <button type="button" class="vintage-control-sync" aria-label="Sync controls" title="Sync controls">${ICONS.sync}<span>Sync Controls</span></button>
            </div>
            <div class="vintage-save-help-dialog" role="dialog" aria-modal="true" aria-label="Keyboard shortcuts" hidden>
                <div class="vintage-save-help-card">
                    <button type="button" class="vintage-save-help-close" aria-label="Close help">x</button>
                    <h2>Hotkeys</h2>
                    <dl>
                        <div><dt>Ctrl+S</dt><dd>Save slot</dd></div>
                        <div><dt>Ctrl+L</dt><dd>Load slot</dd></div>
                        <div><dt>Ctrl+Alt+1–5</dt><dd>Pick slot</dd></div>
                        <div><dt>Ctrl+Del</dt><dd>Clear slot</dd></div>
                        <div><dt>F</dt><dd>Fullscreen</dd></div>
                        <div><dt>P</dt><dd>Pause / play</dd></div>
                    </dl>
                    <p class="vintage-save-help-foot">Clear asks confirm · <strong>Upload</strong> <code>.state</code> · cloud</p>
                </div>
            </div>
            <div class="vintage-save-upload-dialog" role="dialog" aria-modal="true" aria-label="Upload a save state" hidden>
                <div class="vintage-save-upload-card">
                    <button type="button" class="vintage-save-upload-close" aria-label="Close upload">x</button>
                    <h2>Upload a save</h2>
                    <p>Select a target slot, then choose a <code>.state</code> file from your device.</p>
                    <div class="vintage-save-upload-slot-picker" role="group" aria-label="Upload target slot">
                        ${Array.from({ length: Number(this.config.slots || 5) }, (_, idx) => idx + 1).map(slot => `
                            <button type="button" class="vintage-save-upload-slot" data-slot="${slot}" aria-pressed="false">Slot ${slot}</button>
                        `).join('')}
                    </div>
                    <p class="vintage-save-upload-summary"></p>
                    <button type="button" class="vintage-save-upload-pick" aria-label="Choose .state file">${ICONS.upload}<span>Choose file</span></button>
                </div>
            </div>
            <div class="vintage-save-confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="vintage-save-confirm-title" aria-describedby="vintage-save-confirm-message" hidden>
                <div class="vintage-save-confirm-card">
                    <button type="button" class="vintage-save-confirm-close" aria-label="Close">x</button>
                    <h2 id="vintage-save-confirm-title" class="vintage-save-confirm-title">Are you sure?</h2>
                    <p id="vintage-save-confirm-message" class="vintage-save-confirm-message"></p>
                    <div class="vintage-save-confirm-actions">
                        <button type="button" class="vintage-save-confirm-cancel">Cancel</button>
                        <button type="button" class="vintage-save-confirm-confirm" data-tone="danger">Confirm</button>
                    </div>
                </div>
            </div>
            <div class="vintage-save-toasts" aria-live="polite" aria-atomic="true"></div>
            <input type="file" class="vintage-save-state-file-input" tabindex="-1" aria-hidden="true" accept=".state" />
        `

        this.addStyles()
        document.body.appendChild(panel)
        this.panel = panel
        this.status = panel.querySelector('.vintage-save-state-message')
        this.toastContainer = panel.querySelector('.vintage-save-toasts')
        panel.querySelector('.vintage-save-state-file-input').addEventListener('change', async event => {
            const picked = event.target.files?.[0]
            const pendingSlot = this.pendingUploadSlot
            event.target.value = ''
            this.pendingUploadSlot = null

            if (!picked || pendingSlot === null) {
                return
            }

            this.closeUploadDialog()
            await this.uploadFileToSlot(pendingSlot, picked)
        })
        panel.querySelector('.vintage-save-state-toggle').addEventListener('click', () => this.togglePanel())
        panel.querySelector('.vintage-save-state-close')?.addEventListener('click', () => this.closePanel())
        panel.querySelector('.vintage-save-state-backdrop')?.addEventListener('click', () => this.closePanel())
        panel.querySelector('.vintage-control-sync').addEventListener('click', () => this.saveControlSettings())
        panel.querySelector('.vintage-help-link')?.addEventListener('click', () => this.openHelpDialog())
        panel.querySelector('.vintage-upload-link')?.addEventListener('click', () => this.openUploadDialog())
        panel.querySelector('.vintage-save-help-close')?.addEventListener('click', () => this.closeHelpDialog())
        panel.querySelector('.vintage-save-help-dialog')?.addEventListener('click', event => {
            if (event.target.classList.contains('vintage-save-help-dialog')) {
                this.closeHelpDialog()
            }
        })
        panel.querySelector('.vintage-save-upload-close')?.addEventListener('click', () => this.closeUploadDialog())
        panel.querySelector('.vintage-save-upload-dialog')?.addEventListener('click', event => {
            if (event.target.classList.contains('vintage-save-upload-dialog')) {
                this.closeUploadDialog()
            }
        })
        panel.querySelector('.vintage-save-upload-pick')?.addEventListener('click', () => {
            const input = this.panel?.querySelector('.vintage-save-state-file-input')
            if (input) {
                input.value = ''
                input.click()
            }
        })
        panel.querySelectorAll('.vintage-save-upload-slot[data-slot]').forEach(button => {
            button.addEventListener('click', () => {
                const slot = Number(button.getAttribute('data-slot'))
                this.pendingUploadSlot = slot
                this.syncUploadDialog()
            })
        })
        this.renderSlots()
    }

    addStyles() {
        if (document.getElementById(`${PANEL_ID}-styles`)) {
            return
        }

        const style = document.createElement('style')
        style.id = `${PANEL_ID}-styles`
        style.textContent = `
            #${PANEL_ID} {
                bottom: 72px;
                color: #fff;
                font-family: system-ui, -apple-system, sans-serif;
                position: fixed;
                right: 16px;
                z-index: 1000000;
            }
            #vintage-state-download-indicator {
                align-items: center;
                background: rgba(0, 0, 0, 0.58);
                border: 1px solid rgba(255, 255, 255, 0.18);
                border-radius: 999px;
                box-shadow: 0 10px 28px rgba(0, 0, 0, 0.45);
                color: #fff;
                display: none;
                height: 36px;
                justify-content: center;
                width: 36px;
                pointer-events: none;
                position: fixed;
                right: max(12px, env(safe-area-inset-right, 0px));
                top: max(12px, env(safe-area-inset-top, 0px));
                z-index: 2147483646;
            }
            #vintage-state-download-indicator.is-visible {
                display: inline-flex;
            }
            #vintage-state-download-indicator .vintage-state-download-spinner {
                width: 18px;
                height: 18px;
                border: 3px solid rgba(255, 255, 255, 0.2);
                border-top: 3px solid #e60012;
                border-radius: 999px;
                animation: vintageStateDownloadSpin 900ms linear infinite;
                flex: 0 0 auto;
            }
            @keyframes vintageStateDownloadSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            #${PANEL_ID} button {
                align-items: center;
                background: #e60012;
                border: 0;
                border-radius: 6px;
                color: #fff;
                cursor: pointer;
                display: inline-flex;
                font-weight: 700;
                justify-content: center;
                padding: 8px 10px;
            }
            #${PANEL_ID} .vintage-save-state-toggle {
                background: rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
                color: #fff;
                font-weight: 400;
                min-height: 40px;
                min-width: 40px;
                padding: 8px;
            }
            #${PANEL_ID} .vintage-save-state-toggle:hover {
                background: rgba(0, 0, 0, 0.2);
            }
            #${PANEL_ID}.is-menu-open .vintage-save-state-toggle {
                background: rgba(0, 0, 0, 0.88);
                border-color: rgba(255, 255, 255, 0.18);
                box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45);
                font-size: 12px;
                font-weight: 600;
                gap: 6px;
                min-width: unset;
                padding: 8px 12px;
            }
            #${PANEL_ID}.is-menu-open .vintage-save-state-toggle:hover {
                background: rgba(0, 0, 0, 0.92);
            }
            #${PANEL_ID} .vintage-save-toggle-when-closed {
                display: inline-flex;
            }
            #${PANEL_ID} .vintage-save-toggle-when-open {
                align-items: center;
                display: none;
                gap: 6px;
            }
            #${PANEL_ID}.is-menu-open .vintage-save-toggle-when-closed {
                display: none;
            }
            #${PANEL_ID}.is-menu-open .vintage-save-toggle-when-open {
                display: inline-flex;
            }
            #${PANEL_ID} .vintage-save-state-toggle:focus-visible {
                outline: 2px solid rgba(255, 255, 255, 0.65);
                outline-offset: 2px;
            }
            #${PANEL_ID} .vintage-save-toggle-when-closed svg {
                filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.45));
                height: 22px;
                width: 22px;
            }
            #${PANEL_ID} .vintage-save-toggle-when-open svg {
                height: 14px;
                width: 14px;
            }
            #${PANEL_ID} .vintage-save-toggle-close-label {
                letter-spacing: 0.02em;
                white-space: nowrap;
            }
            #${PANEL_ID}.is-menu-open .vintage-save-state-toggle {
                display: none;
            }
            #${PANEL_ID} button svg {
                fill: none;
                height: 16px;
                stroke: currentColor;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 2;
                width: 16px;
            }
            #${PANEL_ID} .vintage-save-state-body {
                background: rgba(0, 0, 0, 0.88);
                border: 1px solid rgba(255, 255, 255, 0.18);
                border-radius: 10px;
                box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45);
                margin-top: 8px;
                max-width: 320px;
                padding: 12px;
                position: relative;
                z-index: 2;
            }
            #${PANEL_ID} .vintage-save-state-backdrop {
                display: none;
            }
            @media (min-width: 640px) {
                #${PANEL_ID} .vintage-save-state-backdrop {
                    background: transparent;
                    bottom: 0;
                    left: 0;
                    position: fixed;
                    right: 0;
                    top: 0;
                    z-index: 1;
                }
                #${PANEL_ID}.is-menu-open .vintage-save-state-backdrop {
                    display: block;
                }
            }
            #${PANEL_ID} .vintage-save-state-heading {
                align-items: center;
                display: flex;
                gap: 12px;
                justify-content: space-between;
                margin-bottom: 8px;
            }
            #${PANEL_ID} .vintage-save-state-heading-actions {
                align-items: center;
                display: inline-flex;
                gap: 10px;
            }
            #${PANEL_ID} .vintage-save-state-close {
                background: transparent;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 10px;
                color: rgba(255, 255, 255, 0.88);
                min-height: 30px;
                min-width: 30px;
                padding: 6px;
            }
            #${PANEL_ID} .vintage-save-state-close:hover {
                background: rgba(255, 255, 255, 0.08);
            }
            #${PANEL_ID} .vintage-save-state-title {
                align-items: center;
                display: inline-flex;
                font-size: 14px;
                font-weight: 800;
                gap: 6px;
            }
            #${PANEL_ID} .vintage-save-state-title-fa {
                flex-shrink: 0;
                font-size: 15px;
                line-height: 1;
                opacity: 0.92;
            }
            #${PANEL_ID} .vintage-help-link {
                background: transparent;
                color: #fda4af;
                font-size: 12px;
                padding: 0;
                text-decoration: underline;
            }
            #${PANEL_ID} .vintage-upload-link {
                background: rgba(217, 119, 6, 0.18);
                border: 1px solid rgba(217, 119, 6, 0.32);
                border-radius: 8px;
                color: #fbbf24;
                min-height: 30px;
                min-width: 30px;
                padding: 6px;
            }
            #${PANEL_ID} .vintage-upload-link:hover {
                background: rgba(217, 119, 6, 0.26);
            }
            #${PANEL_ID} .vintage-upload-link svg {
                height: 16px;
                width: 16px;
            }
            #${PANEL_ID} .vintage-save-state-message {
                color: #cbd5e1;
                font-size: 12px;
                margin-bottom: 8px;
                min-height: 16px;
            }
            #${PANEL_ID} .vintage-save-state-shortcuts {
                color: #94a3b8;
                font-size: 11px;
                line-height: 1.3;
                margin-bottom: 10px;
            }
            #${PANEL_ID} .vintage-save-slot {
                align-items: center;
                display: grid;
                gap: 6px;
                grid-template-columns: 1fr auto auto auto;
                margin-bottom: 6px;
            }
            #${PANEL_ID} .vintage-save-slot button:not(.vintage-save-slot-meta) {
                min-height: 34px;
                min-width: 34px;
                padding: 8px;
            }
            #${PANEL_ID} .vintage-save-slot button[data-action="save"] {
                background: #16a34a;
            }
            #${PANEL_ID} .vintage-save-slot button[data-action="save"]:hover:not(:disabled) {
                background: #15803d;
            }
            #${PANEL_ID} .vintage-save-slot button[data-action="load"] {
                background: #6366f1;
            }
            #${PANEL_ID} .vintage-save-slot button[data-action="load"]:hover:not(:disabled) {
                background: #4f46e5;
            }
            #${PANEL_ID} .vintage-save-slot button[data-action="delete"] {
                background: #e60012;
            }
            #${PANEL_ID} .vintage-save-slot button[data-action="delete"]:hover:not(:disabled) {
                background: #c90010;
            }
            #${PANEL_ID} .vintage-save-slot.is-selected {
                background: rgba(230, 0, 18, 0.18);
                border-radius: 8px;
                margin-left: -4px;
                margin-right: -4px;
                padding: 4px;
            }
            #${PANEL_ID} .vintage-save-slot-meta {
                background: transparent;
                color: #e2e8f0;
                font-size: 12px;
                font-weight: 600;
                overflow: hidden;
                padding-left: 0;
                text-align: left;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            #${PANEL_ID} button:disabled {
                cursor: not-allowed;
                opacity: 0.45;
            }
            #${PANEL_ID} .vintage-control-sync {
                gap: 8px;
                margin-top: 6px;
                width: 100%;
            }
            #${PANEL_ID} .vintage-save-help-dialog {
                align-items: center;
                background: rgba(0, 0, 0, 0.7);
                bottom: 0;
                display: flex;
                justify-content: center;
                left: 0;
                position: fixed;
                right: 0;
                top: 0;
                z-index: 1000001;
            }
            @media (max-width: 1279px) {
                #${PANEL_ID} .vintage-save-help-dialog {
                    align-items: flex-start;
                    justify-content: center;
                    overflow-y: auto;
                    padding: clamp(56px, 12vh, 120px) 12px 16px;
                }
            }
            #${PANEL_ID} .vintage-save-help-dialog[hidden] {
                display: none;
            }
            #${PANEL_ID} .vintage-save-upload-dialog {
                align-items: center;
                background: rgba(0, 0, 0, 0.7);
                bottom: 0;
                display: flex;
                justify-content: center;
                left: 0;
                position: fixed;
                right: 0;
                top: 0;
                z-index: 1000001;
            }
            #${PANEL_ID} .vintage-save-upload-dialog[hidden] {
                display: none;
            }
            #${PANEL_ID} .vintage-save-help-card {
                background: #101014;
                border: 1px solid rgba(255, 255, 255, 0.18);
                border-radius: 12px;
                box-shadow: 0 18px 54px rgba(0, 0, 0, 0.55);
                color: #f8fafc;
                max-width: min(320px, calc(100vw - 24px));
                padding: 12px 36px 10px 12px;
                position: relative;
                width: 100%;
            }
            #${PANEL_ID} .vintage-save-help-card h2 {
                font-size: 15px;
                font-weight: 800;
                line-height: 1.2;
                margin: 0 0 6px;
                padding-right: 4px;
            }
            #${PANEL_ID} .vintage-save-help-card .vintage-save-help-foot {
                color: #94a3b8;
                font-size: 11px;
                line-height: 1.35;
                margin: 6px 0 0;
            }
            #${PANEL_ID} .vintage-save-help-card dl {
                display: grid;
                gap: 4px;
                margin: 0 0 4px;
            }
            #${PANEL_ID} .vintage-save-help-card dl div {
                align-items: center;
                display: grid;
                gap: 6px;
                grid-template-columns: minmax(92px, 34%) 1fr;
            }
            #${PANEL_ID} .vintage-save-help-card dt {
                background: rgba(230, 0, 18, 0.18);
                border-radius: 5px;
                color: #fecdd3;
                font-size: 10px;
                font-weight: 800;
                line-height: 1.2;
                padding: 4px 6px;
                text-align: center;
                word-break: break-word;
            }
            #${PANEL_ID} .vintage-save-help-card dd {
                color: #e2e8f0;
                font-size: 12px;
                line-height: 1.25;
                margin: 0;
            }
            #${PANEL_ID} .vintage-save-help-card code {
                background: rgba(255, 255, 255, 0.06);
                border-radius: 3px;
                padding: 0 4px;
                font-size: 10px;
            }
            #${PANEL_ID} .vintage-save-upload-card {
                background: #101014;
                border: 1px solid rgba(255, 255, 255, 0.18);
                border-radius: 14px;
                box-shadow: 0 18px 54px rgba(0, 0, 0, 0.55);
                color: #f8fafc;
                max-width: 360px;
                padding: 18px;
                position: relative;
                width: calc(100vw - 40px);
            }
            #${PANEL_ID} .vintage-save-upload-card h2 {
                font-size: 18px;
                margin: 0 0 8px;
            }
            #${PANEL_ID} .vintage-save-upload-card p {
                color: #cbd5e1;
                font-size: 13px;
                line-height: 1.4;
                margin: 8px 0;
            }
            #${PANEL_ID} .vintage-save-upload-card code {
                background: rgba(255, 255, 255, 0.06);
                border-radius: 4px;
                padding: 1px 6px;
                font-size: 12px;
            }
            #${PANEL_ID} .vintage-save-upload-slot-picker {
                display: grid;
                gap: 10px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-top: 12px;
            }
            #${PANEL_ID} .vintage-save-upload-slot {
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 10px;
                color: #e2e8f0;
                font-size: 13px;
                font-weight: 800;
                justify-content: flex-start;
                padding: 10px 12px;
                width: 100%;
            }
            #${PANEL_ID} .vintage-save-upload-slot.is-selected {
                background: rgba(217, 119, 6, 0.18);
                border-color: rgba(217, 119, 6, 0.34);
                color: #fde68a;
            }
            #${PANEL_ID} .vintage-save-upload-summary {
                color: #94a3b8;
                font-size: 12px;
                margin-top: 10px;
                min-height: 16px;
            }
            #${PANEL_ID} .vintage-save-upload-pick {
                background: #d97706;
                gap: 8px;
                margin-top: 12px;
                width: 100%;
            }
            #${PANEL_ID} .vintage-save-upload-pick:hover:not(:disabled) {
                background: #b45309;
            }
            #${PANEL_ID} .vintage-save-confirm-dialog {
                align-items: center;
                background: rgba(0, 0, 0, 0.7);
                bottom: 0;
                display: flex;
                justify-content: center;
                left: 0;
                position: fixed;
                right: 0;
                top: 0;
                z-index: 1000002;
            }
            #${PANEL_ID} .vintage-save-confirm-dialog[hidden] {
                display: none;
            }
            #${PANEL_ID} .vintage-save-confirm-card {
                background: #101014;
                border: 1px solid rgba(255, 255, 255, 0.18);
                border-radius: 14px;
                box-shadow: 0 18px 54px rgba(0, 0, 0, 0.55);
                color: #f8fafc;
                max-width: 360px;
                padding: 18px 18px 16px;
                position: relative;
                width: calc(100vw - 40px);
            }
            #${PANEL_ID} .vintage-save-confirm-card h2 {
                font-size: 17px;
                font-weight: 800;
                line-height: 1.25;
                margin: 0 28px 8px 0;
                padding: 0;
            }
            #${PANEL_ID} .vintage-save-confirm-card p {
                color: #cbd5e1;
                font-size: 13px;
                line-height: 1.45;
                margin: 0 0 14px;
            }
            #${PANEL_ID} .vintage-save-confirm-close {
                background: transparent;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 8px;
                color: rgba(255, 255, 255, 0.85);
                min-height: 28px;
                min-width: 28px;
                padding: 4px;
                position: absolute;
                right: 10px;
                top: 10px;
            }
            #${PANEL_ID} .vintage-save-confirm-close:hover {
                background: rgba(255, 255, 255, 0.08);
            }
            #${PANEL_ID} .vintage-save-confirm-actions {
                display: flex;
                gap: 8px;
                justify-content: flex-end;
            }
            #${PANEL_ID} .vintage-save-confirm-cancel {
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.18);
                color: #e2e8f0;
                font-weight: 600;
                padding: 9px 14px;
            }
            #${PANEL_ID} .vintage-save-confirm-cancel:hover {
                background: rgba(255, 255, 255, 0.12);
            }
            #${PANEL_ID} .vintage-save-confirm-confirm {
                font-weight: 700;
                padding: 9px 14px;
            }
            #${PANEL_ID} .vintage-save-confirm-confirm[data-tone="danger"] {
                background: #e60012;
            }
            #${PANEL_ID} .vintage-save-confirm-confirm[data-tone="danger"]:hover {
                background: #c90010;
            }
            #${PANEL_ID} .vintage-save-confirm-confirm[data-tone="primary"] {
                background: #6366f1;
            }
            #${PANEL_ID} .vintage-save-confirm-confirm[data-tone="primary"]:hover {
                background: #4f46e5;
            }
            #${PANEL_ID} .vintage-save-confirm-confirm:focus-visible {
                outline: 2px solid rgba(255, 255, 255, 0.65);
                outline-offset: 2px;
            }
            #${PANEL_ID} .vintage-save-state-file-input {
                clip: rect(0 0 0 0);
                clip-path: inset(50%);
                height: 1px;
                overflow: hidden;
                position: absolute;
                white-space: nowrap;
                width: 1px;
            }
            #${PANEL_ID} .vintage-save-help-close {
                min-height: 26px;
                min-width: 26px;
                padding: 2px;
                position: absolute;
                right: 8px;
                top: 8px;
            }
            #${PANEL_ID} .vintage-save-upload-close {
                min-height: 28px;
                min-width: 28px;
                padding: 4px;
                position: absolute;
                right: 10px;
                top: 10px;
            }
            #${PANEL_ID} .vintage-save-toasts {
                bottom: 118px;
                display: grid;
                gap: 8px;
                pointer-events: none;
                position: fixed;
                right: 16px;
                justify-items: end;
                width: max-content;
                max-width: min(320px, calc(100vw - 32px));
                z-index: 1000002;
            }
            #${PANEL_ID} .vintage-save-toast {
                background: rgba(15, 23, 42, 0.98);
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-left: 4px solid #38bdf8;
                border-radius: 10px;
                box-shadow: 0 12px 34px rgba(0, 0, 0, 0.42);
                color: #f8fafc;
                font-size: 13px;
                font-weight: 800;
                opacity: 0;
                padding: 10px 12px;
                transform: translateY(8px) scale(0.98);
                transition: opacity 180ms ease, transform 180ms ease;
                width: fit-content;
                max-width: 100%;
            }
            #${PANEL_ID} .vintage-save-toast.is-visible {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            #${PANEL_ID} .vintage-save-toast.is-success {
                border-left-color: #22c55e;
            }
            #${PANEL_ID} .vintage-save-toast.is-warning {
                border-left-color: #f59e0b;
            }
            #${PANEL_ID} .vintage-save-toast.is-error {
                border-left-color: #ef4444;
            }
            @media (max-width: 639px) {
                #${PANEL_ID} {
                    align-items: stretch;
                    bottom: max(68px, calc(env(safe-area-inset-bottom, 0px) + 56px));
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                    left: 0;
                    right: 0;
                    width: 100%;
                }
                #${PANEL_ID}.is-menu-open {
                    bottom: 0;
                    top: 0;
                }
                #${PANEL_ID} .vintage-save-state-toggle {
                    align-self: flex-end;
                    border-radius: 10px;
                    flex-shrink: 0;
                    margin-right: max(12px, env(safe-area-inset-right, 0px));
                    min-height: 44px;
                    min-width: 44px;
                    -webkit-tap-highlight-color: transparent;
                }
                #${PANEL_ID}.is-menu-open .vintage-save-state-toggle {
                    display: none;
                }
                #${PANEL_ID}.is-menu-open .vintage-save-state-backdrop {
                    display: block;
                    flex: 0 0 max(56px, calc(env(safe-area-inset-top, 0px) + 24px));
                    height: max(56px, calc(env(safe-area-inset-top, 0px) + 24px));
                    width: 100%;
                }
                #${PANEL_ID} .vintage-save-state-body {
                    align-self: stretch;
                    border-radius: 14px 14px 0 0;
                    box-sizing: border-box;
                    margin-top: 0;
                    max-height: none;
                    max-width: none;
                    overflow-x: hidden;
                    overflow-y: auto;
                    -webkit-overflow-scrolling: touch;
                    padding-top: max(20px, calc(env(safe-area-inset-top, 0px) + 8px));
                    padding-right: max(16px, env(safe-area-inset-right, 0px));
                    padding-bottom: max(16px, env(safe-area-inset-bottom, 0px));
                    padding-left: max(16px, env(safe-area-inset-left, 0px));
                    scroll-padding-top: 12px;
                    scrollbar-color: rgba(255, 255, 255, 0.28) transparent;
                    scrollbar-width: thin;
                    width: 100%;
                }
                #${PANEL_ID}.is-menu-open .vintage-save-state-body {
                    flex: 1 1 auto;
                    min-height: 0;
                }
                #${PANEL_ID} .vintage-save-state-heading {
                    flex-wrap: wrap;
                    gap: 8px 12px;
                    margin-bottom: 10px;
                    margin-top: 2px;
                    padding-top: 2px;
                }
                #${PANEL_ID} .vintage-save-state-title {
                    align-items: center;
                    font-size: 15px;
                    letter-spacing: 0.01em;
                    line-height: 1.35;
                }
                #${PANEL_ID} .vintage-save-state-title-fa {
                    display: inline-block;
                    font-size: 16px;
                    line-height: 1;
                    padding-top: 3px;
                }
                #${PANEL_ID} .vintage-save-state-message {
                    font-size: 13px;
                    line-height: 1.35;
                    margin-bottom: 10px;
                }
                #${PANEL_ID} .vintage-save-slot {
                    align-items: stretch;
                    background: rgba(255, 255, 255, 0.04);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 12px;
                    gap: 10px 8px;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    grid-template-rows: auto auto;
                    margin-bottom: 10px;
                    padding: 10px 10px 12px;
                }
                #${PANEL_ID} .vintage-save-slot-meta {
                    font-size: 14px;
                    font-weight: 700;
                    grid-column: 1 / -1;
                    grid-row: 1;
                    line-height: 1.35;
                    min-height: 44px;
                    padding: 6px 2px 2px;
                    text-align: left;
                    white-space: normal;
                    -webkit-tap-highlight-color: transparent;
                }
                #${PANEL_ID} .vintage-save-slot button[data-action="save"] {
                    grid-column: 1;
                    grid-row: 2;
                }
                #${PANEL_ID} .vintage-save-slot button[data-action="load"] {
                    grid-column: 2;
                    grid-row: 2;
                }
                #${PANEL_ID} .vintage-save-slot button[data-action="delete"] {
                    grid-column: 3;
                    grid-row: 2;
                }
                #${PANEL_ID} .vintage-save-slot button:not(.vintage-save-slot-meta) {
                    border-radius: 10px;
                    min-height: 48px;
                    min-width: 0;
                    padding: 10px 6px;
                    -webkit-tap-highlight-color: transparent;
                }
                #${PANEL_ID} .vintage-save-slot button:not(.vintage-save-slot-meta) svg {
                    height: 20px;
                    width: 20px;
                }
                #${PANEL_ID} .vintage-save-slot.is-selected {
                    background: rgba(230, 0, 18, 0.22);
                    border-color: rgba(254, 202, 202, 0.35);
                    box-shadow: inset 0 0 0 1px rgba(254, 202, 202, 0.12);
                    margin-left: 0;
                    margin-right: 0;
                    padding: 10px 10px 12px;
                }
                #${PANEL_ID} .vintage-control-sync {
                    background: rgba(255, 255, 255, 0.06);
                    border: 1px solid rgba(255, 255, 255, 0.22);
                    color: #f1f5f9;
                    font-size: 13px;
                    font-weight: 600;
                    gap: 8px;
                    margin-top: 4px;
                    min-height: 46px;
                    padding: 10px 14px;
                    -webkit-tap-highlight-color: transparent;
                }
                #${PANEL_ID} .vintage-control-sync svg {
                    height: 18px;
                    opacity: 0.95;
                    width: 18px;
                }
                #${PANEL_ID} .vintage-save-toasts {
                    bottom: max(120px, calc(env(safe-area-inset-bottom, 0px) + 108px));
                    left: auto;
                    right: max(12px, env(safe-area-inset-right, 0px));
                    width: max-content;
                    max-width: calc(100vw - max(24px, env(safe-area-inset-right, 0px) + env(safe-area-inset-left, 0px)));
                }
            }
        `
        document.head.appendChild(style)
    }

    createStateDownloadIndicator() {
        const existing = document.getElementById('vintage-state-download-indicator')
        if (existing) {
            if (existing.isConnected) {
                this.stateDownloadIndicator = existing
                return
            }
            existing.remove()
        }

        const indicator = document.createElement('div')
        indicator.id = 'vintage-state-download-indicator'
        indicator.setAttribute('aria-hidden', 'true')
        indicator.innerHTML = `
            <span class="vintage-state-download-spinner"></span>
        `

        // EmulatorJS reparents / rebuilds #game after init; a node under #game can be detached.
        // Fixed to the player document viewport (same visual frame as the emulator when #game is full-viewport).
        document.body.appendChild(indicator)
        this.stateDownloadIndicator = indicator
    }

    setStateDownloadIndicatorVisible(visible) {
        if (this.stateDownloadIndicator && !this.stateDownloadIndicator.isConnected) {
            this.stateDownloadIndicator = null
        }

        if (!this.stateDownloadIndicator) {
            this.createStateDownloadIndicator()
        }

        this.stateDownloadIndicator?.classList.toggle('is-visible', Boolean(visible))
    }

    isDesktopViewport() {
        return typeof window !== 'undefined'
            && typeof window.matchMedia === 'function'
            && window.matchMedia('(min-width: 640px)').matches
    }

    setPanelOpen(isOpen) {
        const body = this.panel?.querySelector('.vintage-save-state-body')
        const toggle = this.panel?.querySelector('.vintage-save-state-toggle')
        const backdrop = this.panel?.querySelector('.vintage-save-state-backdrop')

        if (!body || !toggle) {
            return
        }

        body.hidden = !isOpen
        if (backdrop) {
            backdrop.hidden = !isOpen
        }

        toggle.setAttribute('aria-expanded', String(isOpen))
        this.panel.classList.toggle('is-menu-open', isOpen)
        toggle.setAttribute('aria-label', isOpen ? 'Close save menu' : 'Saves')
        toggle.setAttribute('title', isOpen ? 'Close menu' : 'Saves')
    }

    handlePointerDown(event) {
        if (!this.panel || !this.isDesktopViewport()) {
            return
        }

        const body = this.panel.querySelector('.vintage-save-state-body')
        if (!body || body.hidden) {
            return
        }

        const target = event.target
        if (!(target instanceof Node)) {
            return
        }

        if (this.panel.contains(target)) {
            return
        }

        this.closePanel()
    }

    togglePanel() {
        const body = this.panel?.querySelector('.vintage-save-state-body')
        const nextIsOpen = Boolean(body?.hidden)
        this.setPanelOpen(nextIsOpen)
    }

    closePanel() {
        this.setPanelOpen(false)
    }

    renderSlots() {
        if (!this.panel) {
            return
        }

        const container = this.panel.querySelector('.vintage-save-state-slots')
        const slots = Number(this.config.slots || 5)
        container.innerHTML = ''

        const guest = !this.config.authenticated

        for (let slot = 1; slot <= slots; slot += 1) {
            const save = this.saves.find(item => item.slot === slot)
            const row = document.createElement('div')
            row.className = 'vintage-save-slot'
            row.classList.toggle('is-selected', slot === this.currentSlot)
            const selectDisabled = guest ? ' disabled' : ''
            const saveDisabled = guest ? ' disabled' : ''
            const loadDisabled = guest || !save ? ' disabled' : ''
            const deleteDisabled = guest || !save ? ' disabled' : ''
            row.innerHTML = `
                <button type="button" class="vintage-save-slot-meta" data-action="select"${selectDisabled}>Slot ${slot}${save ? ` - ${new Date(save.updated_at).toLocaleString()}` : ' - empty'}</button>
                <button type="button" data-action="save" aria-label="Save slot ${slot}" title="Capture save to slot ${slot}"${saveDisabled}>${ICONS.save}</button>
                <button type="button" data-action="load" aria-label="Load slot ${slot}" title="Load slot ${slot}"${loadDisabled}>${ICONS.load}</button>
                <button type="button" data-action="delete" aria-label="Clear slot ${slot}" title="Clear slot ${slot}"${deleteDisabled}>${ICONS.clear}</button>
            `
            row.querySelector('[data-action="select"]').addEventListener('click', () => this.selectSlot(slot))
            row.querySelector('[data-action="save"]').addEventListener('click', () => this.saveSlot(slot))
            row.querySelector('[data-action="load"]').addEventListener('click', () => this.loadSlot(slot))
            row.querySelector('[data-action="delete"]').addEventListener('click', () => this.deleteSlot(slot))
            container.appendChild(row)
        }

        const syncBtn = this.panel.querySelector('.vintage-control-sync')
        if (syncBtn) {
            syncBtn.disabled = guest
        }

        if (guest) {
            this.setStatus('Log in to save slots to your cloud account.')
        }

        this.syncUploadDialog()
    }

    setStatus(message) {
        if (this.status) {
            this.status.textContent = message
        }
    }

    notify(message, type = 'info', enabled = true) {
        if (!enabled || !this.toastContainer) {
            return
        }

        const toast = document.createElement('div')
        toast.className = `vintage-save-toast is-${type}`
        toast.setAttribute('role', 'status')
        toast.textContent = message
        this.toastContainer.appendChild(toast)

        window.requestAnimationFrame(() => toast.classList.add('is-visible'))
        window.setTimeout(() => {
            toast.classList.remove('is-visible')
            window.setTimeout(() => toast.remove(), 240)
        }, 2400)
    }

    handleFullscreenChange() {
        if (!this.panel) {
            return
        }

        const doc = document
        const fullscreenEl = doc.fullscreenElement || doc.webkitFullscreenElement

        if (fullscreenEl) {
            fullscreenEl.appendChild(this.panel)
        } else if (this.panelHome) {
            this.panelHome.appendChild(this.panel)
        } else {
            document.body.appendChild(this.panel)
        }
        // Save-state download ring uses position:fixed on document.body; no reparent on fullscreen.
    }

    attachIframeKeyListener() {
        if (this.iframeKeyTarget) {
            return
        }

        const tryAttach = () => {
            const frame = document.querySelector('#game iframe')
            if (!frame) {
                return false
            }

            try {
                const win = frame.contentWindow
                if (!win) {
                    return false
                }

                win.addEventListener('keydown', this.keydownHandler, true)
                this.iframeKeyTarget = win
                return true
            } catch {
                // Likely cross-origin; cannot access.
                return false
            }
        }

        if (tryAttach()) {
            return
        }

        // EmulatorJS may create the iframe later; retry briefly.
        let attempts = 0
        this.iframeKeyRetryTimer = window.setInterval(() => {
            attempts += 1
            if (tryAttach() || attempts >= 20) {
                window.clearInterval(this.iframeKeyRetryTimer)
                this.iframeKeyRetryTimer = null
            }
        }, 500)
    }
}
