import './bootstrap';

/**
 * Lazy load all css background images
 */
document.addEventListener('DOMContentLoaded', function() {
    let ribbonSkeletonClearTimer = null

    /** Debounced: hide Swiper ribbon skeletons after lazy-load runs (matches Alpine ribbon view). */
    function scheduleRibbonSkeletonClear(container) {
        const host = container.closest('[data-ribbon-view]')
        const mode = host?.getAttribute?.('data-ribbon-view')
        if (mode !== 'group' && mode !== 'squares') {
            return
        }
        clearTimeout(ribbonSkeletonClearTimer)
        ribbonSkeletonClearTimer = setTimeout(() => {
            window.dispatchEvent(new CustomEvent('ribbon-skeleton-clear', { detail: { mode } }))
            ribbonSkeletonClearTimer = null
        }, 280)
    }

    function initializeLazyLoad() {
        const lazyLoadContainers = document.querySelectorAll('.lazy-load-container')

        const lazyLoadObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const container = entry.target

                    // Load background images (GIF / static)
                    const lazyBackgrounds = container.querySelectorAll('.lazy-load-bg')
                    lazyBackgrounds.forEach(bg => {
                        const bgUrl = bg.getAttribute('data-bg-url')
                        if (bgUrl) {
                            bg.style.backgroundImage = `url('${bgUrl}')`
                            bg.removeAttribute('data-bg-url')
                        }
                    })

                    // Load video backgrounds (webm / mp4)
                    const lazyVideos = container.querySelectorAll('.lazy-load-video')
                    lazyVideos.forEach(videoWrap => {
                        const video = videoWrap.querySelector('video')
                        if (!video) return
                        video.querySelectorAll('source[data-src]').forEach(source => {
                            source.src = source.getAttribute('data-src')
                            source.removeAttribute('data-src')
                        })
                        video.load()
                        video.play().catch(() => {})
                    })

                    container.setAttribute('data-loaded', 'true')
                    scheduleRibbonSkeletonClear(container)
                    observer.unobserve(container)
                }
            })
        })

        lazyLoadContainers.forEach(container => {
            if (container.getAttribute('data-loaded') === 'false') {
                lazyLoadObserver.observe(container)
            }
        })
    }

    // Initialize on page load
    initializeLazyLoad()

    // Re-initialize after Livewire updates or navigation events
    window.addEventListener('livewire:navigated', initializeLazyLoad)

    // Re-initialize after any interaction on the page
    document.addEventListener('click', function() {
        setTimeout(initializeLazyLoad, 200)
        setTimeout(initializeLazyLoad, 500)
        setTimeout(initializeLazyLoad, 1000)
        setTimeout(initializeLazyLoad, 2000)
    })

    // General MutationObserver on a higher-level container
    const mainContainer = document.querySelector('.main-container')
    const mutationConfig = { attributes: true, childList: true, subtree: true }

    const mutationCallback = function(mutationsList) {
        mutationsList.forEach(mutation => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target
                if (!target.classList.contains('hidden')) {
                    initializeLazyLoad()
                }
            }

            // Reinitialize lazy loading if new nodes are added (e.g., when switching views)
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                initializeLazyLoad()
            }
        })
    }

    const mainMutationObserver = new MutationObserver(mutationCallback)
    
    if (mainContainer) {
        mainMutationObserver.observe(mainContainer, mutationConfig)
    }
})

/**
 * My Saves (/my/saves): highlight desktop jump-list links from main-column scroll position.
 */
;(function initMySavesJumpHighlight() {
    let rafId = null

    const GAME_ACTIVE = ['text-rose-500', 'dark:text-rose-400', 'font-semibold']
    const GAME_INACTIVE = ['text-cod-gray-600', 'dark:text-cod-gray-400', 'font-normal']
    const CONSOLE_ACTIVE = ['text-rose-500', 'dark:text-rose-400', 'font-semibold']
    const CONSOLE_INACTIVE = ['text-cod-gray-700', 'dark:text-cod-gray-300', 'font-normal']

    function setJumpLinkState(anchor, isActive, type) {
        const active = type === 'game' ? GAME_ACTIVE : CONSOLE_ACTIVE
        const inactive = type === 'game' ? GAME_INACTIVE : CONSOLE_INACTIVE
        ;[...active, ...inactive].forEach((c) => anchor.classList.remove(c))
        ;(isActive ? active : inactive).forEach((c) => anchor.classList.add(c))

        if (type === 'game') {
            if (isActive) {
                anchor.setAttribute('aria-current', 'location')
            } else {
                anchor.removeAttribute('aria-current')
            }
        }
    }

    function tickMySavesJumpHighlight() {
        rafId = null

        const root = document.getElementById('my-saves-scroll-spy')
        if (!root || !window.matchMedia('(min-width: 1024px)').matches) {
            return
        }

        const aside = root.querySelector('aside')
        if (!aside) {
            return
        }

        const sections = root.querySelectorAll('section[id^="console-"]')
        if (!sections.length) {
            return
        }

        const offset = 130

        // Active console = last section whose top has crossed the spy line (not “last game on the page”).
        let activeSection = null
        sections.forEach((sec) => {
            if (sec.getBoundingClientRect().top <= offset) {
                activeSection = sec
            }
        })
        if (!activeSection) {
            activeSection = sections[0]
        }

        const activeConsoleId = activeSection.id || ''

        const gameCardsInSection = activeSection.querySelectorAll('[data-my-saves-game]')
        let activeGameId = ''
        gameCardsInSection.forEach((el) => {
            if (el.getBoundingClientRect().top <= offset) {
                activeGameId = el.id
            }
        })
        if (!activeGameId && gameCardsInSection.length) {
            const nearBottom =
                window.innerHeight + window.scrollY >=
                document.documentElement.scrollHeight - 4
            activeGameId = nearBottom
                ? gameCardsInSection[gameCardsInSection.length - 1].id
                : gameCardsInSection[0].id
        }

        aside.querySelectorAll('a[href^="#"]').forEach((a) => {
            const href = a.getAttribute('href') || ''
            const id = href.slice(1)
            if (id.startsWith('game-')) {
                setJumpLinkState(a, id === activeGameId, 'game')
            } else if (id.startsWith('console-')) {
                setJumpLinkState(a, id === activeConsoleId, 'console')
            }
        })
    }

    function scheduleMySavesJumpHighlight() {
        if (rafId !== null) {
            return
        }
        rafId = requestAnimationFrame(tickMySavesJumpHighlight)
    }

    window.addEventListener('scroll', scheduleMySavesJumpHighlight, { passive: true })
    window.addEventListener('resize', scheduleMySavesJumpHighlight, { passive: true })
    window.addEventListener('hashchange', scheduleMySavesJumpHighlight, { passive: true })

    document.addEventListener('DOMContentLoaded', tickMySavesJumpHighlight)
    document.addEventListener('livewire:navigated', tickMySavesJumpHighlight)
    document.addEventListener('livewire:updated', scheduleMySavesJumpHighlight)
})()