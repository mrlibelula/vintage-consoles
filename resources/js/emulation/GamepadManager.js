import { getConfiguredMapping } from './gamepad-id'

export const INPUTS = ['up', 'down', 'left', 'right', 'a', 'b', 'start', 'select']

const EMPTY_STATE = Object.freeze({
    up: false,
    down: false,
    left: false,
    right: false,
    a: false,
    b: false,
    start: false,
    select: false,
})

const DEFAULT_MAPPING = {
    threshold: 0.5,
    buttons: {
        up: [12],
        down: [13],
        left: [14],
        right: [15],
        a: [0],
        b: [1],
        start: [9],
        select: [8],
    },
    axes: {
        left: [
            { index: 0, direction: 'negative' },
            { index: 2, direction: 'negative' },
            { index: 4, direction: 'negative' },
            { index: 6, direction: 'negative' },
        ],
        right: [
            { index: 0, direction: 'positive' },
            { index: 2, direction: 'positive' },
            { index: 4, direction: 'positive' },
            { index: 6, direction: 'positive' },
        ],
        up: [
            { index: 1, direction: 'negative' },
            { index: 3, direction: 'negative' },
            { index: 5, direction: 'negative' },
            { index: 7, direction: 'negative' },
        ],
        down: [
            { index: 1, direction: 'positive' },
            { index: 3, direction: 'positive' },
            { index: 5, direction: 'positive' },
            { index: 7, direction: 'positive' },
        ],
    },
    hatAxes: {
        up: [{ index: 9, values: [-1, -0.714, 1] }],
        right: [{ index: 9, values: [-0.714, -0.429, -0.143] }],
        down: [{ index: 9, values: [-0.143, 0.143, 0.429] }],
        left: [{ index: 9, values: [0.429, 0.714, 1] }],
    },
}

function mergeMapping(base, override) {
    return {
        ...base,
        ...override,
        buttons: {
            ...base.buttons,
            ...override.buttons,
        },
        axes: {
            ...base.axes,
            ...override.axes,
        },
        hatAxes: {
            ...base.hatAxes,
            ...override.hatAxes,
        },
    }
}

function pressedButton(buttons, indexes = []) {
    return indexes.some(index => Boolean(buttons[index]?.pressed || buttons[index]?.value > 0.5))
}

function pressedAxis(axes, configs = [], threshold) {
    return configs.some(config => {
        const axis = axes[config.index]

        if (typeof axis !== 'number') {
            return false
        }

        return config.direction === 'negative'
            ? axis < -Math.abs(config.threshold || threshold)
            : axis > Math.abs(config.threshold || threshold)
    })
}

function pressedHatAxis(axes, configs = []) {
    return configs.some(config => {
        const axis = axes[config.index]

        if (typeof axis !== 'number') {
            return false
        }

        return config.values.some(value => Math.abs(axis - value) < 0.08)
    })
}

function hasActivity(gamepad) {
    return gamepad.buttons.some(button => button.pressed || button.value > 0.5)
        || gamepad.axes.some(axis => Math.abs(axis) > 0.5)
}

function loadLocalMappings() {
    try {
        return JSON.parse(window.localStorage?.getItem('vintage.gamepad.mappings') || '{}')
    } catch (error) {
        console.warn('Gamepad mappings could not be parsed from localStorage.', error)

        return {}
    }
}

export class GamepadManager {
    constructor({
        mappings = {},
        poller = callback => window.requestAnimationFrame(callback),
        cancelPoller = animationFrame => window.cancelAnimationFrame(animationFrame),
    } = {}) {
        this.mappings = {
            ...loadLocalMappings(),
            ...window.VintageGamepadMappings,
            ...mappings,
        }
        this.poller = poller
        this.cancelPoller = cancelPoller
        this.listeners = new Set()
        this.animationFrame = null
        this.activeGamepadIndex = null
        this.previousState = { ...EMPTY_STATE }
        this.previousRaw = null
    }

    start() {
        if (!('getGamepads' in navigator) || this.animationFrame) {
            return
        }

        this.tick()
    }

    stop() {
        if (this.animationFrame) {
            this.cancelPoller(this.animationFrame)
            this.animationFrame = null
        }

        this.emit({
            state: { ...EMPTY_STATE },
            previousState: this.previousState,
            gamepad: null,
            raw: null,
            mapping: DEFAULT_MAPPING,
        })
        this.previousState = { ...EMPTY_STATE }
        this.previousRaw = null
    }

    subscribe(listener) {
        this.listeners.add(listener)

        return () => this.listeners.delete(listener)
    }

    updateMappings(mappings) {
        this.mappings = {
            ...this.mappings,
            ...mappings,
        }
    }

    tick() {
        this.poll()
        this.animationFrame = this.poller(() => this.tick())
    }

    poll() {
        const gamepad = this.getActiveGamepad()
        const mapping = mergeMapping(DEFAULT_MAPPING, getConfiguredMapping(gamepad, this.mappings))
        const raw = gamepad ? this.getRawState(gamepad) : null
        const state = gamepad ? this.getNormalizedState(gamepad, mapping) : { ...EMPTY_STATE }

        if (!this.sameState(state, this.previousState) || this.rawChanged(raw, this.previousRaw)) {
            this.emit({
                state,
                previousState: this.previousState,
                gamepad,
                raw,
                mapping,
            })
        }

        this.previousState = state
        this.previousRaw = raw
    }

    getActiveGamepad() {
        const gamepads = Array.from(navigator.getGamepads()).filter(Boolean)

        if (!gamepads.length) {
            this.activeGamepadIndex = null

            return null
        }

        const current = gamepads.find(gamepad => gamepad.index === this.activeGamepadIndex)

        if (current?.connected) {
            return current
        }

        const active = gamepads.find(hasActivity) || gamepads[0]
        this.activeGamepadIndex = active.index

        return active
    }

    getRawState(gamepad) {
        return {
            buttons: gamepad.buttons.map((button, index) => ({
                index,
                pressed: button.pressed,
                value: Number(button.value.toFixed(3)),
            })),
            axes: gamepad.axes.map(value => Number(value.toFixed(3))),
        }
    }

    getNormalizedState(gamepad, mapping) {
        return INPUTS.reduce((state, input) => {
            state[input] = pressedButton(gamepad.buttons, mapping.buttons[input])
                || pressedAxis(gamepad.axes, mapping.axes[input], mapping.threshold)
                || pressedHatAxis(gamepad.axes, mapping.hatAxes[input])

            return state
        }, {})
    }

    sameState(nextState, previousState) {
        return INPUTS.every(input => nextState[input] === previousState[input])
    }

    rawChanged(raw, previousRaw) {
        if (!raw || !previousRaw) {
            return raw !== previousRaw
        }

        return JSON.stringify(raw) !== JSON.stringify(previousRaw)
    }

    emit(payload) {
        this.listeners.forEach(listener => listener(payload))
    }
}
