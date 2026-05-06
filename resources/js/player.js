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
