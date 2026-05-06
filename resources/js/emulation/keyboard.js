const KEY_CODES = {
    ArrowUp: 38,
    ArrowDown: 40,
    ArrowLeft: 37,
    ArrowRight: 39,
    Enter: 13,
    ShiftLeft: 16,
    ShiftRight: 16,
    ControlLeft: 17,
    AltLeft: 18,
    KeyX: 88,
    KeyZ: 90,
}

const KEY_VALUES = {
    ArrowUp: 'ArrowUp',
    ArrowDown: 'ArrowDown',
    ArrowLeft: 'ArrowLeft',
    ArrowRight: 'ArrowRight',
    Enter: 'Enter',
    ShiftLeft: 'Shift',
    ShiftRight: 'Shift',
    ControlLeft: 'Control',
    AltLeft: 'Alt',
    KeyX: 'x',
    KeyZ: 'z',
}

export const EMULATOR_JS_KEYS = {
    up: 'ArrowUp',
    down: 'ArrowDown',
    left: 'ArrowLeft',
    right: 'ArrowRight',
    a: 'KeyX',
    b: 'KeyZ',
    start: 'Enter',
    select: 'ShiftRight',
}

export const JS_DOS_KEYS = {
    up: 'ArrowUp',
    down: 'ArrowDown',
    left: 'ArrowLeft',
    right: 'ArrowRight',
    a: 'ControlLeft',
    b: 'AltLeft',
    start: 'Enter',
    select: 'ShiftLeft',
}

export class KeyboardInputAdapter {
    constructor({ target = document, keyMap = EMULATOR_JS_KEYS } = {}) {
        this.target = typeof target === 'string' ? document.querySelector(target) : target
        this.keyMap = keyMap
        this.pressedInputs = new Set()

        if (this.target instanceof HTMLElement && !this.target.hasAttribute('tabindex')) {
            this.target.tabIndex = -1
        }
    }

    update(nextState, previousState = {}) {
        Object.entries(this.keyMap).forEach(([input, code]) => {
            const isPressed = Boolean(nextState[input])
            const wasPressed = Boolean(previousState[input])

            if (isPressed === wasPressed) {
                return
            }

            this.dispatch(input, code, isPressed ? 'keydown' : 'keyup')
        })
    }

    releaseAll() {
        this.pressedInputs.forEach(input => {
            const code = this.keyMap[input]

            if (code) {
                this.dispatch(input, code, 'keyup')
            }
        })

        this.pressedInputs.clear()
    }

    dispatch(input, code, type) {
        if (type === 'keydown') {
            this.pressedInputs.add(input)
        } else {
            this.pressedInputs.delete(input)
        }

        const targets = [this.target, document, window].filter(Boolean)

        if (this.target instanceof HTMLElement && type === 'keydown') {
            this.target.focus?.({ preventScroll: true })
        }

        targets.forEach(target => target.dispatchEvent(this.createKeyboardEvent(type, code)))
    }

    createKeyboardEvent(type, code) {
        const keyCode = KEY_CODES[code] || 0
        const event = new KeyboardEvent(type, {
            key: KEY_VALUES[code] || code,
            code,
            bubbles: true,
            cancelable: true,
            composed: true,
        })

        Object.defineProperties(event, {
            keyCode: { get: () => keyCode },
            which: { get: () => keyCode },
        })

        return event
    }
}
