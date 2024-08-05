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

/**
 * Multiple ribbon logic
 */
document.addEventListener('DOMContentLoaded', function () {
    function initializeScroll(container) {
        if (!container) return;

        let isDragging = false;
        let startX, scrollLeft;
        let dragThreshold = 5; // Adjust as needed to control sensitivity
        let dragDistance = 0;

        function startDrag(e) {
            e.preventDefault();
            isDragging = true;
            dragDistance = 0;
            container.classList.add('cursor-grabbing');
            container.classList.remove('cursor-grab');
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        }

        function drag(e) {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed multiplier
            dragDistance = Math.abs(x - startX); // Track drag distance

            if (dragDistance > dragThreshold) {
                container.scrollLeft = scrollLeft - walk;
            }
        }

        function endDrag() {
            isDragging = false;
            container.classList.add('cursor-grab');
            container.classList.remove('cursor-grabbing');
        }

        function handleClick(e) {
            if (dragDistance > dragThreshold) {
                e.preventDefault(); // Prevent click if drag threshold is exceeded
            } else {
                const target = e.target.closest('[data-url]');
                if (target) {
                    // Navigate to the URL
                    window.location.href = target.getAttribute('data-url');
                }
            }
        }

        container.addEventListener('mousedown', startDrag);
        container.addEventListener('mousemove', drag);
        container.addEventListener('mouseup', endDrag);
        container.addEventListener('mouseleave', endDrag);
        container.addEventListener('click', handleClick);

        // Adding touch event listeners
        container.addEventListener('touchstart', (e) => {
            e.preventDefault(); 
            startDrag(e.touches[0]);
        });

        container.addEventListener('touchmove', (e) => {
            e.preventDefault();
            drag(e.touches[0]);
        });

        container.addEventListener('touchend', endDrag);

        // Set initial cursor state
        container.classList.add('cursor-grab');
    }

    document.querySelectorAll('.ribbon-container').forEach(container => {
        initializeScroll(container);
    });

    // Reinitialize scroll functionality on Livewire updates
    document.addEventListener('livewire:load', () => {
        document.querySelectorAll('.ribbon-container').forEach(container => {
            initializeScroll(container);
        });
    });

    document.addEventListener('livewire:update', () => {
        document.querySelectorAll('.ribbon-container').forEach(container => {
            initializeScroll(container);
        });
    });

    document.addEventListener('livewire:navigated', () => {
        document.querySelectorAll('.ribbon-container').forEach(container => {
            initializeScroll(container);
        });
    });
});
