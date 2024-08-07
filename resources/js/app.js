import './bootstrap';

/**
 * Lazy load all css background images
 */
document.addEventListener('DOMContentLoaded', function() {
    function initializeLazyLoad() {
        const lazyLoadContainers = document.querySelectorAll('.lazy-load-container')

        const lazyLoadObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const container = entry.target

                    // Load background images
                    const lazyBackgrounds = container.querySelectorAll('.lazy-load-bg')
                    lazyBackgrounds.forEach(bg => {
                        const bgUrl = bg.getAttribute('data-bg-url')
                        if (bgUrl) {
                            bg.style.backgroundImage = `url('${bgUrl}')`
                            bg.removeAttribute('data-bg-url')
                        }
                    })

                    container.setAttribute('data-loaded', 'true')
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