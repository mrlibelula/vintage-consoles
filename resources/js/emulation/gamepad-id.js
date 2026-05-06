export function normalizeGamepadId(id) {
    return String(id || 'unknown')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim()
}

export function getConfiguredMapping(gamepad, mappings = {}) {
    const normalizedId = normalizeGamepadId(gamepad?.id)

    if (mappings[normalizedId]) {
        return mappings[normalizedId]
    }

    return Object.entries(mappings).find(([id]) => normalizedId.includes(normalizeGamepadId(id)))?.[1] || {}
}
