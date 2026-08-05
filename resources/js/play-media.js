/**
 * YouTube media + custom PiP + cross-session resume positions.
 * Resume: localStorage always; authenticated users also sync via player-data API.
 */
const YT_API_SRC = 'https://www.youtube.com/iframe_api'
const STORAGE_PREFIX = 'vintage.yt.progress.'
const YT_API_TIMEOUT_MS = 15000

let ytApiPromise = null
let sharedPlayer = null
let sharedPlayerMountId = null
let progressTimer = null
let saveTimer = null
/** Re-apply mute until start settles (YouTube often unmutes after user-gesture play). */
let enforceMuteUntil = 0
let muteEnforceTimer = null
/** Once true, stop re-muting so the viewer can unmute via YT controls. */
let startMuteSettled = false

function stopMuteEnforcement() {
    if (muteEnforceTimer) {
        clearInterval(muteEnforceTimer)
        muteEnforceTimer = null
    }
    enforceMuteUntil = 0
}

function forceMute(player) {
    if (!player) return
    try {
        player.mute?.()
        if (typeof player.setVolume === 'function') {
            player.setVolume(0)
        }
    } catch {
        /* ignore */
    }
}

function playerReportsMuted(player) {
    try {
        if (typeof player?.isMuted === 'function' && player.isMuted()) {
            return true
        }
        if (typeof player?.getVolume === 'function' && player.getVolume() === 0) {
            return true
        }
    } catch {
        /* ignore */
    }
    return false
}

function beginMutedPlayback(player) {
    startMuteSettled = false
    forceMute(player)
    enforceMuteUntil = Date.now() + 5000

    if (muteEnforceTimer) {
        clearInterval(muteEnforceTimer)
    }
    muteEnforceTimer = setInterval(() => {
        if (startMuteSettled || Date.now() >= enforceMuteUntil || !player) {
            stopMuteEnforcement()
            return
        }
        forceMute(player)
    }, 80)

    try {
        player?.playVideo?.()
    } catch {
        /* ignore */
    }
    forceMute(player)
}

function ytReady() {
    return Boolean(window.YT && window.YT.Player)
}

function loadYoutubeApi() {
    if (ytReady()) {
        return Promise.resolve(window.YT)
    }
    if (ytApiPromise) {
        return ytApiPromise
    }

    ytApiPromise = new Promise((resolve, reject) => {
        let settled = false
        const finish = (err) => {
            if (settled) return
            settled = true
            if (err) {
                ytApiPromise = null
                reject(err)
                return
            }
            resolve(window.YT)
        }

        const previous = window.onYouTubeIframeAPIReady
        window.onYouTubeIframeAPIReady = () => {
            if (typeof previous === 'function') {
                previous()
            }
            finish()
        }

        const existing = [...document.scripts].find((s) => s.src.includes('youtube.com/iframe_api'))
        if (!existing) {
            const tag = document.createElement('script')
            tag.src = YT_API_SRC
            tag.async = true
            tag.onerror = () => finish(new Error('Failed to load YouTube IFrame API'))
            document.head.appendChild(tag)
        }

        const started = Date.now()
        const poll = () => {
            if (settled) return
            if (ytReady()) {
                finish()
                return
            }
            if (Date.now() - started > YT_API_TIMEOUT_MS) {
                finish(new Error('YouTube IFrame API timed out'))
                return
            }
            window.setTimeout(poll, 50)
        }
        poll()
    })

    return ytApiPromise
}

function storageKey(gameId, youtubeId) {
    return `${STORAGE_PREFIX}${gameId}.${youtubeId}`
}

function readLocalProgress(gameId, youtubeId) {
    try {
        const raw = localStorage.getItem(storageKey(gameId, youtubeId))
        if (!raw) return 0
        const parsed = JSON.parse(raw)
        return Math.max(0, Math.floor(Number(parsed?.position_seconds) || 0))
    } catch {
        return 0
    }
}

function writeLocalProgress(gameId, youtubeId, seconds) {
    try {
        localStorage.setItem(
            storageKey(gameId, youtubeId),
            JSON.stringify({ position_seconds: Math.floor(seconds), updated_at: Date.now() }),
        )
    } catch {
        /* ignore quota */
    }
}

function formatClock(seconds) {
    const s = Math.max(0, Math.floor(Number(seconds) || 0))
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const rem = s % 60
    if (h > 0) {
        return `${h}:${String(m).padStart(2, '0')}:${String(rem).padStart(2, '0')}`
    }
    return `${m}:${String(rem).padStart(2, '0')}`
}

function formatResume(seconds) {
    const s = Math.floor(seconds)
    if (s < 3) return ''
    return formatClock(s)
}

function durationStorageKey(youtubeId) {
    return `vintage.yt.duration.${youtubeId}`
}

function readCachedDuration(youtubeId) {
    try {
        const raw = localStorage.getItem(durationStorageKey(youtubeId))
        const n = Math.floor(Number(raw) || 0)
        return n > 0 ? n : 0
    } catch {
        return 0
    }
}

function writeCachedDuration(youtubeId, seconds) {
    const n = Math.floor(Number(seconds) || 0)
    if (n <= 0) return
    try {
        localStorage.setItem(durationStorageKey(youtubeId), String(n))
    } catch {
        /* ignore quota */
    }
}

function destroySharedPlayer() {
    if (progressTimer) {
        clearInterval(progressTimer)
        progressTimer = null
    }
    if (saveTimer) {
        clearTimeout(saveTimer)
        saveTimer = null
    }
    stopMuteEnforcement()
    startMuteSettled = false
    try {
        sharedPlayer?.destroy?.()
    } catch {
        /* ignore */
    }
    sharedPlayer = null
    sharedPlayerMountId = null
}

function playerIsAlive() {
    if (!sharedPlayer || typeof sharedPlayer.getIframe !== 'function') {
        return false
    }
    try {
        const iframe = sharedPlayer.getIframe()
        return Boolean(iframe && iframe.isConnected)
    } catch {
        return false
    }
}

export function bootVintageYoutubeMedia() {
    const register = () => {
        const Alpine = window.Alpine
        if (!Alpine) {
            return
        }

        Alpine.data('vintageYoutubeMedia', (config = {}) => ({
            videos: config.videos || [],
            gameId: config.gameId,
            progressUrl: config.progressUrl || null,
            serverProgress: config.serverProgress || {},
            csrf: config.csrf || '',
            canSync: Boolean(config.canSync),
            compact: Boolean(config.compact),
            activeIndex: -1,
            activeId: null,
            activeTitle: '',
            playerError: '',
            playerLoading: false,
            pipOpen: false,
            ownsPip: !config.compact,
            pipX: 16,
            pipY: Math.max(96, (typeof window !== 'undefined' ? window.innerHeight : 800) - 280),
            drag: null,
            localProgress: {},
            durations: {},
            _durationProbe: null,

            init() {
                const durations = { ...this.durations }
                this.videos.forEach((v) => {
                    const serverRaw = this.serverProgress?.[v.youtube_id]
                    const server = Number(
                        (serverRaw && typeof serverRaw === 'object'
                            ? serverRaw.position_seconds
                            : serverRaw) || 0,
                    )
                    const local = readLocalProgress(this.gameId, v.youtube_id)
                    this.localProgress[v.youtube_id] = Math.max(server, local)
                    const cached = readCachedDuration(v.youtube_id)
                    if (cached > 0) {
                        durations[v.youtube_id] = cached
                    }
                })
                this.durations = durations

                if (this.ownsPip) {
                    this.prefetchDurations()
                }

                this._onOpen = (event) => {
                    if (!this.ownsPip) return
                    const youtubeId = event.detail?.youtubeId
                    const index = this.videos.findIndex((v) => v.youtube_id === youtubeId)
                    const idx = index >= 0 ? index : Number(event.detail?.index ?? 0)
                    if (!this.videos[idx]) return
                    this.activeIndex = idx
                    this.ensurePlayer({ pip: Boolean(event.detail?.pip ?? true) })
                }
                this._onProgress = (event) => {
                    const id = event.detail?.youtubeId
                    const seconds = Number(event.detail?.position_seconds || 0)
                    if (id) {
                        this.localProgress[id] = seconds
                    }
                }
                this._onNavigate = () => {
                    if (this.ownsPip) {
                        destroySharedPlayer()
                    }
                }

                window.addEventListener('vintage-yt-open', this._onOpen)
                window.addEventListener('vintage-yt-progress', this._onProgress)
                document.addEventListener('livewire:navigating', this._onNavigate)
            },

            destroy() {
                window.removeEventListener('vintage-yt-open', this._onOpen)
                window.removeEventListener('vintage-yt-progress', this._onProgress)
                document.removeEventListener('livewire:navigating', this._onNavigate)
                this.destroyDurationProbe()
                if (this.ownsPip) {
                    destroySharedPlayer()
                }
            },

            resumeSeconds(youtubeId) {
                return Math.max(0, Math.floor(Number(this.localProgress[youtubeId]) || 0))
            },

            resumeLabel(youtubeId) {
                return formatResume(this.resumeSeconds(youtubeId))
            },

            durationSeconds(youtubeId) {
                return Math.max(0, Math.floor(Number(this.durations[youtubeId]) || 0))
            },

            durationLabel(youtubeId) {
                const seconds = this.durationSeconds(youtubeId)
                return seconds > 0 ? formatClock(seconds) : ''
            },

            rememberDuration(youtubeId, seconds) {
                const n = Math.floor(Number(seconds) || 0)
                if (!youtubeId || n <= 0) return
                if (this.durations[youtubeId] === n) return
                this.durations = { ...this.durations, [youtubeId]: n }
                writeCachedDuration(youtubeId, n)
            },

            capturePlayerDuration(youtubeId) {
                if (!sharedPlayer || typeof sharedPlayer.getDuration !== 'function') {
                    return
                }
                try {
                    this.rememberDuration(youtubeId, sharedPlayer.getDuration())
                } catch {
                    /* ignore */
                }
            },

            destroyDurationProbe() {
                try {
                    this._durationProbe?.destroy?.()
                } catch {
                    /* ignore */
                }
                this._durationProbe = null
                this.$refs.durationProbe?.replaceChildren?.()
            },

            async prefetchDurations() {
                const missing = this.videos
                    .map((v) => v.youtube_id)
                    .filter((id) => id && !this.durationSeconds(id))
                if (missing.length === 0) {
                    return
                }

                let YT
                try {
                    YT = await loadYoutubeApi()
                } catch {
                    return
                }

                await this.$nextTick()
                const host = this.$refs.durationProbe
                if (!host) {
                    return
                }

                host.innerHTML = ''
                const mount = document.createElement('div')
                host.appendChild(mount)

                const queue = [...missing]
                const self = this
                let currentId = null

                const cueNext = () => {
                    currentId = queue.shift() || null
                    if (!currentId || !self._durationProbe) {
                        self.destroyDurationProbe()
                        return
                    }
                    try {
                        self._durationProbe.cueVideoById({ videoId: currentId })
                    } catch {
                        cueNext()
                    }
                }

                this._durationProbe = new YT.Player(mount, {
                    width: 1,
                    height: 1,
                    playerVars: {
                        playsinline: 1,
                        rel: 0,
                        modestbranding: 1,
                        origin: window.location.origin,
                        enablejsapi: 1,
                    },
                    events: {
                        onReady: () => cueNext(),
                        onStateChange: (event) => {
                            if (
                                event.data === YT.PlayerState.CUED
                                || event.data === YT.PlayerState.PLAYING
                                || event.data === YT.PlayerState.PAUSED
                            ) {
                                try {
                                    const d = event.target.getDuration?.() || 0
                                    if (currentId && d > 0) {
                                        self.rememberDuration(currentId, d)
                                    }
                                } catch {
                                    /* ignore */
                                }
                                if (event.data === YT.PlayerState.CUED) {
                                    cueNext()
                                }
                            }
                        },
                        onError: () => cueNext(),
                    },
                })
            },

            get activeMetaLabel() {
                const title = this.activeTitle || ''
                if (!title) return ''
                const duration = this.activeId ? this.durationLabel(this.activeId) : ''
                return duration ? `${title} · ${duration}` : title
            },

            scrollToMedia() {
                document.getElementById('play-media')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
            },

            openPip(index) {
                if (this.compact) {
                    window.dispatchEvent(new CustomEvent('vintage-yt-open', {
                        detail: { index, youtubeId: this.videos[index]?.youtube_id, pip: true },
                    }))
                    return
                }
                this.activeIndex = index
                this.ensurePlayer({ pip: true })
            },

            playInline(index) {
                if (this.compact) {
                    this.openPip(index)
                    return
                }
                this.activeIndex = index
                this.ensurePlayer({ pip: false })
            },

            dockInline() {
                this.ensurePlayer({ pip: false })
                // Media panel may be CSS-hidden while on Info; reveal it so the inline player is visible.
                try {
                    this.$wire?.changeTab?.('media')
                } catch {
                    /* ignore */
                }
            },

            closePip() {
                this.pipOpen = false
                this.playerLoading = false
                this.playerError = ''
                destroySharedPlayer()
                this.activeId = null
                this.activeIndex = -1
            },

            async ensurePlayer({ pip }) {
                const video = this.videos[this.activeIndex]
                if (!video || !this.ownsPip) {
                    return
                }

                this.activeId = video.youtube_id
                this.activeTitle = video.title
                this.pipOpen = pip
                this.playerError = ''
                this.playerLoading = true

                await this.$nextTick()

                const host = pip ? this.$refs.pipHost : this.$refs.inlineHost
                if (!host) {
                    this.playerLoading = false
                    this.playerError = 'Player container missing'
                    return
                }

                const start = this.resumeSeconds(video.youtube_id)
                const mountKey = pip ? 'pip' : 'inline'

                let YT
                try {
                    YT = await loadYoutubeApi()
                } catch (err) {
                    this.playerLoading = false
                    this.playerError = 'Could not load YouTube'
                    console.warn('[vintage-yt]', err)
                    return
                }

                if (
                    playerIsAlive()
                    && sharedPlayerMountId === mountKey
                    && host.contains(sharedPlayer.getIframe())
                    && typeof sharedPlayer.loadVideoById === 'function'
                ) {
                    try {
                        forceMute(sharedPlayer)
                        sharedPlayer.loadVideoById({
                            videoId: video.youtube_id,
                            startSeconds: start >= 3 ? start : 0,
                        })
                        beginMutedPlayback(sharedPlayer)
                        this.capturePlayerDuration(video.youtube_id)
                        this.startProgressWatch(video.youtube_id)
                        this.playerLoading = false
                        return
                    } catch {
                        /* recreate below */
                    }
                }

                destroySharedPlayer()
                host.innerHTML = ''
                const mount = document.createElement('div')
                mount.style.width = '100%'
                mount.style.height = '100%'
                host.appendChild(mount)

                const self = this
                sharedPlayerMountId = mountKey
                // No autoplay in playerVars: user-gesture clicks let YT start unmuted
                // before onReady can mute. We mute first, then playVideo(), then poll.
                sharedPlayer = new YT.Player(mount, {
                    width: '100%',
                    height: '100%',
                    videoId: video.youtube_id,
                    playerVars: {
                        autoplay: 0,
                        mute: 1,
                        playsinline: 1,
                        rel: 0,
                        modestbranding: 1,
                        start: start >= 3 ? start : 0,
                        origin: window.location.origin,
                        enablejsapi: 1,
                    },
                    events: {
                        onReady: (event) => {
                            self.playerLoading = false
                            try {
                                const iframe = event.target.getIframe?.()
                                if (iframe) {
                                    iframe.style.width = '100%'
                                    iframe.style.height = '100%'
                                    iframe.style.border = '0'
                                    const allow = iframe.getAttribute('allow') || ''
                                    if (!/\bautoplay\b/i.test(allow)) {
                                        iframe.setAttribute(
                                            'allow',
                                            allow
                                                ? `${allow}; autoplay; encrypted-media`
                                                : 'autoplay; encrypted-media; picture-in-picture',
                                        )
                                    }
                                }
                                forceMute(event.target)
                                if (start >= 3) {
                                    event.target.seekTo(start, true)
                                }
                                beginMutedPlayback(event.target)
                            } catch { /* ignore */ }
                            self.capturePlayerDuration(video.youtube_id)
                            self.startProgressWatch(video.youtube_id)
                        },
                        onError: () => {
                            self.playerLoading = false
                            self.playerError = 'This video cannot be played'
                        },
                        onStateChange: (event) => {
                            const starting = !startMuteSettled && Date.now() < enforceMuteUntil
                            if (
                                starting
                                && (
                                    event.data === YT.PlayerState.UNSTARTED
                                    || event.data === YT.PlayerState.BUFFERING
                                    || event.data === YT.PlayerState.PLAYING
                                )
                            ) {
                                forceMute(event.target)
                            }
                            if (event.data === YT.PlayerState.PLAYING) {
                                forceMute(event.target)
                                // Drop enforcement once mute has stuck through PLAYING so the user can unmute.
                                if (!startMuteSettled && playerReportsMuted(event.target)) {
                                    window.setTimeout(() => {
                                        if (playerReportsMuted(event.target)) {
                                            startMuteSettled = true
                                            stopMuteEnforcement()
                                        }
                                    }, 400)
                                }
                                self.playerLoading = false
                                self.capturePlayerDuration(video.youtube_id)
                                self.startProgressWatch(video.youtube_id)
                            }
                            if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                                self.flushProgress(video.youtube_id)
                            }
                        },
                    },
                })
            },

            startProgressWatch(youtubeId) {
                if (progressTimer) {
                    clearInterval(progressTimer)
                }
                const self = this
                progressTimer = setInterval(() => {
                    if (!sharedPlayer || typeof sharedPlayer.getCurrentTime !== 'function') {
                        return
                    }
                    let t = 0
                    try {
                        t = sharedPlayer.getCurrentTime() || 0
                    } catch {
                        return
                    }
                    self.persistProgress(youtubeId, t)
                }, 2000)
            },

            persistProgress(youtubeId, seconds) {
                const value = Math.floor(seconds)
                if (value < 3) {
                    return
                }
                this.localProgress[youtubeId] = value
                writeLocalProgress(this.gameId, youtubeId, value)
                window.dispatchEvent(new CustomEvent('vintage-yt-progress', {
                    detail: { youtubeId, position_seconds: value },
                }))

                if (!this.canSync || !this.progressUrl) {
                    return
                }

                if (saveTimer) {
                    clearTimeout(saveTimer)
                }
                const self = this
                saveTimer = setTimeout(() => {
                    fetch(self.progressUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': self.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            youtube_id: youtubeId,
                            position_seconds: value,
                        }),
                    }).catch(() => {})
                }, 800)
            },

            flushProgress(youtubeId) {
                if (!sharedPlayer || typeof sharedPlayer.getCurrentTime !== 'function') {
                    return
                }
                try {
                    this.persistProgress(youtubeId, sharedPlayer.getCurrentTime() || 0)
                } catch {
                    /* ignore */
                }
            },

            startDrag(event) {
                if (event.button != null && event.button !== 0) {
                    return
                }
                if (event.target.closest('button, a, input, textarea, select')) {
                    return
                }
                event.preventDefault()
                window.getSelection()?.removeAllRanges()

                const root = document.documentElement
                const previousUserSelect = root.style.userSelect
                const previousWebkitUserSelect = root.style.webkitUserSelect
                root.style.userSelect = 'none'
                root.style.webkitUserSelect = 'none'

                this.drag = {
                    ox: event.clientX - this.pipX,
                    oy: event.clientY - this.pipY,
                }
                const preventSelect = (e) => e.preventDefault()
                const move = (e) => {
                    if (!this.drag) return
                    e.preventDefault()
                    this.pipX = Math.min(window.innerWidth - 80, Math.max(0, e.clientX - this.drag.ox))
                    this.pipY = Math.min(window.innerHeight - 80, Math.max(0, e.clientY - this.drag.oy))
                }
                const up = () => {
                    this.drag = null
                    root.style.userSelect = previousUserSelect
                    root.style.webkitUserSelect = previousWebkitUserSelect
                    window.getSelection()?.removeAllRanges()
                    window.removeEventListener('pointermove', move)
                    window.removeEventListener('pointerup', up)
                    window.removeEventListener('pointercancel', up)
                    window.removeEventListener('selectstart', preventSelect)
                }
                window.addEventListener('pointermove', move)
                window.addEventListener('pointerup', up)
                window.addEventListener('pointercancel', up)
                window.addEventListener('selectstart', preventSelect)
            },
        }))
    }

    if (!window.__vintageYtBooted) {
        window.__vintageYtBooted = true
        document.addEventListener('alpine:init', register)
    }
    if (window.Alpine) {
        register()
    }
}

bootVintageYoutubeMedia()
