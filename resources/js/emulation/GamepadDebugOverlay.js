import { INPUTS } from './GamepadManager'

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}

export function shouldShowGamepadDebugOverlay() {
    const params = new URLSearchParams(window.location.search)

    return window.VintageGamepadDebug === true
        || params.get('gamepadDebug') === '1'
        || window.localStorage?.getItem('vintage.gamepad.debug') === '1'
}

export class GamepadDebugOverlay {
    constructor({ enabled = shouldShowGamepadDebugOverlay() } = {}) {
        this.enabled = enabled
        this.element = null
    }

    update({ gamepad, raw, state }) {
        if (!this.enabled) {
            return
        }

        this.ensureElement()

        const activeButtons = raw?.buttons
            .filter(button => button.pressed || button.value > 0.05)
            .map(button => `${button.index}:${button.value}`)
            .join(', ') || 'none'

        const axes = raw?.axes
            .map((axis, index) => `${index}:${axis.toFixed(2)}`)
            .join('  ') || 'none'

        const resolved = INPUTS
            .map(input => `<span class="${state[input] ? 'is-active' : ''}">${input}</span>`)
            .join(' ')

        this.element.innerHTML = `
            <div class="gamepad-debug-title">Gamepad Debug</div>
            <div><strong>ID:</strong> ${escapeHtml(gamepad?.id || 'none')}</div>
            <div><strong>Buttons:</strong> ${escapeHtml(activeButtons)}</div>
            <div><strong>Axes:</strong> ${escapeHtml(axes)}</div>
            <div><strong>Resolved:</strong> ${resolved}</div>
        `
    }

    destroy() {
        this.element?.remove()
        this.element = null
    }

    ensureElement() {
        if (this.element) {
            return
        }

        const style = document.createElement('style')
        style.textContent = `
            .gamepad-debug-overlay {
                position: fixed;
                right: 12px;
                bottom: 12px;
                z-index: 2147483647;
                width: min(420px, calc(100vw - 24px));
                padding: 12px;
                border-radius: 8px;
                background: rgba(0, 0, 0, 0.82);
                color: #fff;
                font: 12px/1.45 ui-monospace, SFMono-Regular, Consolas, monospace;
                pointer-events: none;
                white-space: normal;
            }

            .gamepad-debug-overlay .gamepad-debug-title {
                margin-bottom: 6px;
                font-weight: 700;
            }

            .gamepad-debug-overlay span {
                display: inline-block;
                margin: 3px 3px 0 0;
                padding: 2px 5px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.14);
            }

            .gamepad-debug-overlay span.is-active {
                background: #22c55e;
                color: #03140a;
            }
        `

        this.element = document.createElement('div')
        this.element.className = 'gamepad-debug-overlay'
        document.head.appendChild(style)
        document.body.appendChild(this.element)
    }
}
