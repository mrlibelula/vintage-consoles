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

async function toggleVintagePlayerFullscreen() {
    const doc = document
    const fsEl = doc.fullscreenElement || doc.webkitFullscreenElement

    if (fsEl) {
        const exit = doc.exitFullscreen?.bind(doc) || doc.webkitExitFullscreen?.bind(doc)
        try {
            await exit?.()
        } catch {
            /* ignore */
        }
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

window.addEventListener('keydown', vintagePlayerFullscreenKeydown, true)
