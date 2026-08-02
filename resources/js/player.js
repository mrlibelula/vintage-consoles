import { GamepadManager } from './emulation/GamepadManager'
import { GamepadDebugOverlay } from './emulation/GamepadDebugOverlay'
import { SaveStateManager } from './emulation/SaveStateManager'
import { EMULATOR_JS_KEYS, JS_DOS_KEYS, KeyboardInputAdapter } from './emulation/keyboard'

let runningSession = null

function createAdapter(config = {}) {
    const target = config.target || document
    const keyMap = config.keyMap || (config.adapter === 'jsdos' ? JS_DOS_KEYS : EMULATOR_JS_KEYS)

    return new KeyboardInputAdapter({ target, keyMap })
}

export function startPlayerGamepad(config = {}) {
    stopPlayerGamepad()

    const manager = new GamepadManager({ mappings: config.mappings || {} })
    const adapter = createAdapter(config)
    const debugOverlay = new GamepadDebugOverlay({ enabled: config.debug })

    const unsubscribe = manager.subscribe(payload => {
        adapter.update(payload.state, payload.previousState)
        debugOverlay.update(payload)
    })

    manager.start()

    runningSession = {
        manager,
        adapter,
        debugOverlay,
        stop() {
            unsubscribe()
            manager.stop()
            adapter.releaseAll()
            debugOverlay.destroy()
        },
    }

    return runningSession
}

export function stopPlayerGamepad() {
    if (!runningSession) {
        return
    }

    runningSession.stop()
    runningSession = null
}

window.VintagePlayerGamepad = {
    start: startPlayerGamepad,
    stop: stopPlayerGamepad,
}

window.VintageSaveStateManager = SaveStateManager

window.addEventListener('vintage-gamepad:start', event => {
    startPlayerGamepad(event.detail || {})
})

window.addEventListener('vintage-gamepad:mappings-restored', event => {
    runningSession?.manager?.updateMappings(event.detail || {})
})

if (window.VintagePlayerGamepadConfig) {
    startPlayerGamepad(window.VintagePlayerGamepadConfig)
}

window.dispatchEvent(new CustomEvent('vintage-gamepad:ready', {
    detail: window.VintagePlayerGamepad,
}))

window.dispatchEvent(new CustomEvent('vintage-save-state:ready', {
    detail: window.VintageSaveStateManager,
}))

function vintageFullscreenTarget() {
    const stable = window.__vintageStableFullscreenRoot
    if (stable instanceof HTMLElement) {
        return stable
    }
    return document.getElementById('game') || document.getElementById('dosbox')
}

function vintageSaveOverlayOpen() {
    const help = document.querySelector('.vintage-save-help-dialog')
    const upload = document.querySelector('.vintage-save-upload-dialog')
    return Boolean(
        (help && !help.hasAttribute('hidden'))
        || (upload && !upload.hasAttribute('hidden')),
    )
}

async function requestFullscreenOn(el) {
    if (!el) {
        return false
    }
    const req = el.requestFullscreen?.bind(el) || el.webkitRequestFullscreen?.bind(el)
    if (!req) {
        return false
    }
    try {
        await req()
        return true
    } catch {
        return false
    }
}

/**
 * True when this window is embedded and the parent document has put our iframe in fullscreen.
 * In that case fullscreen survives in-iframe reloads (e.g. EmulatorJS stability reload on F4).
 */
function vintageEmbedderIframeIsFullscreen() {
    try {
        const fe = window.frameElement
        if (!(fe instanceof Element) || !fe.ownerDocument) {
            return false
        }
        const pdoc = fe.ownerDocument
        const pfs = pdoc.fullscreenElement || pdoc.webkitFullscreenElement
        return pfs === fe
    } catch {
        return false
    }
}

async function toggleVintagePlayerFullscreen() {
    const doc = document
    const fsEl = doc.fullscreenElement || doc.webkitFullscreenElement
    const embedderFs = vintageEmbedderIframeIsFullscreen()

    if (fsEl || embedderFs) {
        if (fsEl) {
            const exit = doc.exitFullscreen?.bind(doc) || doc.webkitExitFullscreen?.bind(doc)
            try {
                await exit?.()
            } catch {
                /* ignore */
            }
        }
        if (vintageEmbedderIframeIsFullscreen()) {
            const fe = window.frameElement
            const pdoc = fe.ownerDocument
            const exit = pdoc.exitFullscreen?.bind(pdoc) || pdoc.webkitExitFullscreen?.bind(pdoc)
            try {
                await exit?.()
            } catch {
                /* ignore */
            }
        }
        return
    }

    const fe = window.frameElement
    if (fe instanceof Element && (await requestFullscreenOn(fe))) {
        return
    }

    const gameEl = vintageFullscreenTarget()
    for (const node of [gameEl, doc.documentElement].filter(Boolean)) {
        if (await requestFullscreenOn(node)) {
            return
        }
    }

    if (window.parent !== window) {
        window.parent.postMessage({ type: 'vintage-player-toggle-fullscreen' }, window.location.origin)
    }
}

function isVintageFullscreenKeyTargetEditable(target) {
    if (!target || !(target instanceof HTMLElement)) {
        return false
    }

    if (target.closest('[contenteditable="true"]')) {
        return true
    }

    const tag = target.tagName
    return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT'
}

function vintagePlayerIsFKey(event) {
    return !event.repeat && (event.code === 'KeyF' || event.key === 'f' || event.key === 'F')
}

function vintagePlayerIsPKey(event) {
    return !event.repeat && (event.code === 'KeyP' || event.key === 'p' || event.key === 'P')
}

function toggleVintageEmulatorPlayPause() {
    const ejs = window.EJS_emulator
    if (ejs && typeof ejs.togglePlaying === 'function') {
        ejs.togglePlaying()
    }
}

function vintagePlayerFullscreenKeydown(event) {
    if (event.defaultPrevented) {
        return
    }

    if (event.ctrlKey || event.metaKey || event.altKey) {
        return
    }

    if (!vintagePlayerIsFKey(event)) {
        return
    }

    if (isVintageFullscreenKeyTargetEditable(event.target)) {
        return
    }

    if (vintageSaveOverlayOpen()) {
        return
    }

    event.preventDefault()
    void toggleVintagePlayerFullscreen()
}

function vintagePlayerPlayPauseKeydown(event) {
    if (event.defaultPrevented) {
        return
    }

    if (event.ctrlKey || event.metaKey || event.altKey) {
        return
    }

    if (!vintagePlayerIsPKey(event)) {
        return
    }

    if (isVintageFullscreenKeyTargetEditable(event.target)) {
        return
    }

    if (vintageSaveOverlayOpen()) {
        return
    }

    if (!window.EJS_emulator || typeof window.EJS_emulator.togglePlaying !== 'function') {
        return
    }

    event.preventDefault()
    toggleVintageEmulatorPlayPause()
}

window.addEventListener('keydown', vintagePlayerFullscreenKeydown, true)
window.addEventListener('keydown', vintagePlayerPlayPauseKeydown, true)

const KEY_CODE_FALLBACKS = {
    KeyX: 88,
    KeyZ: 90,
    KeyV: 86,
    Enter: 13,
    ShiftRight: 16,
}

// EmulatorJS / RetroPad: select (2) = coin, start (3) = P1 start.
const ARCADE_SIMULATE_BUTTONS = {
    coin: 2,
    start: 3,
}

// EmulatorJS defaults bind Digit1/2/3 to quick-save/load/slot — never synthesize those.
// Fall back to EmulatorJS's own select/start keys (v / Enter) only when simulateInput is unavailable.
const ARCADE_KEYBOARD_FALLBACKS = {
    coin: { code: 'KeyV', key: 'v' },
    start: { code: 'Enter', key: 'Enter' },
}

function createArcadeKeyboardEvent(type, code, key) {
    const keyCode = KEY_CODE_FALLBACKS[code] || 0
    const event = new KeyboardEvent(type, {
        key: key || code,
        code,
        bubbles: true,
        cancelable: true,
        composed: true,
    })

    // KeyboardEvent constructor ignores keyCode/which; EmulatorJS still reads them.
    Object.defineProperties(event, {
        keyCode: { get: () => keyCode },
        which: { get: () => keyCode },
    })

    return event
}

function dispatchArcadeKey({ action, code, key, down }) {
    const value = down ? 1 : 0
    const button = action ? ARCADE_SIMULATE_BUTTONS[action] : undefined
    const simulate = window.EJS_emulator?.gameManager?.simulateInput

    if (typeof button === 'number' && typeof simulate === 'function') {
        try {
            simulate.call(window.EJS_emulator.gameManager, 0, button, value)
            return
        } catch (error) {
            console.warn('[Vintage] arcade simulateInput failed', error)
        }
    }

    const mapped = (action && ARCADE_KEYBOARD_FALLBACKS[action])
        || (code ? { code, key } : null)

    // Refuse Digit1–3: EmulatorJS maps them to quick save/load/slot change.
    if (!mapped || /^Digit[123]$/.test(mapped.code)) {
        return
    }

    const eventType = down ? 'keydown' : 'keyup'
    const canvas = document.querySelector('#game canvas, canvas')

    if (canvas instanceof HTMLElement && down) {
        canvas.focus?.({ preventScroll: true })
    }

    ;[canvas, document, window].filter(Boolean).forEach(target => {
        target.dispatchEvent(createArcadeKeyboardEvent(eventType, mapped.code, mapped.key))
    })
}

window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) {
        return
    }
    if (!event.data || event.data.type !== 'vintage-arcade-key') {
        return
    }
    dispatchArcadeKey(event.data)
})
