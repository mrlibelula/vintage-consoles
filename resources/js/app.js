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
        console.log(1)
        if (!container) return;
        console.log(2)

        let isDragging = false;
        let startX, scrollLeft;

        function startDrag(e) {
            e.preventDefault();
            isDragging = true;
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
            // Add a threshold to avoid too much sensitivity
            if (Math.abs(x - startX) > 5) { // Change to a different value as needed
                container.scrollLeft = scrollLeft - walk;
            }
        }

        function endDrag() {
            isDragging = false;
            container.classList.add('cursor-grab');
            container.classList.remove('cursor-grabbing');
        }

        container.addEventListener('mousedown', startDrag);
        container.addEventListener('mousemove', drag);
        container.addEventListener('mouseup', endDrag);
        container.addEventListener('mouseleave', endDrag);

        // Adding touch event listeners
        container.addEventListener('touchstart', (e) => {
            e.preventDefault(); // Prevent scrolling on touch
            startDrag(e.touches[0]);
        });

        container.addEventListener('touchmove', (e) => {
            e.preventDefault(); // Prevent scrolling on touch
            drag(e.touches[0]);
        });

        container.addEventListener('touchend', endDrag);

        // Set initial cursor state
        container.classList.add('cursor-grab');
    }

    // Initialize scroll functionality on page load
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
        console.log('update')
        document.querySelectorAll('.ribbon-container').forEach(container => {
            initializeScroll(container);
        });
    });

    document.addEventListener('dom-updated', () => {
        console.log('dom-updated')
        document.querySelectorAll('.ribbon-container').forEach(container => {
            initializeScroll(container);
        });
    });

    // Livewire.hook('message.processed', (message, component) => {
    //     document.querySelectorAll('.ribbon-container').forEach(container => {
    //         initializeScroll(container);
    //     });
    // });    

    document.addEventListener('livewire:navigated', () => {
        console.log('navigated')
        document.querySelectorAll('.ribbon-container').forEach(container => {
            initializeScroll(container);
        });
    });

    // Keyboard navigation with focus check
    window.addEventListener('keydown', (e) => {
        document.querySelectorAll('.ribbon-container').forEach(container => {
            if (document.activeElement === container) { // Check if the container is focused
                if (e.key === 'ArrowLeft') {
                    container.scrollBy({
                        left: -200,
                        behavior: 'smooth'
                    });
                } else if (e.key === 'ArrowRight') {
                    container.scrollBy({
                        left: 200,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});

// document.addEventListener('DOMContentLoaded', function() {
//     let throttleTimeout;

//     function initDraggableRibbon() {
//         console.log('Initializing draggable ribbons');
//         document.querySelectorAll('.draggable-ribbon').forEach(ribbon => {
//             console.log('Attaching event listeners');

//             // Remove previous event listeners
//             ribbon.removeEventListener('mousedown', handleMouseDown);
//             ribbon.removeEventListener('mouseleave', handleMouseLeave);
//             ribbon.removeEventListener('mouseup', handleMouseUp);
//             ribbon.removeEventListener('mousemove', handleMouseMove);

//             let isDown = false;
//             let startX;
//             let scrollLeft;
//             let isDragging = false;
//             let startTime;

//             function handleMouseDown(e) {
//                 isDown = true;
//                 ribbon.classList.add('active');
//                 startX = e.pageX - ribbon.offsetLeft;
//                 scrollLeft = ribbon.scrollLeft;
//                 startTime = new Date().getTime();
//                 isDragging = false;
//             }

//             function handleMouseLeave() {
//                 isDown = false;
//                 ribbon.classList.remove('active');
//             }

//             function handleMouseUp(e) {
//                 isDown = false;
//                 ribbon.classList.remove('active');
//                 const endTime = new Date().getTime();
//                 const timeDiff = endTime - startTime;

//                 if (isDragging || timeDiff > 200) {
//                     e.preventDefault();
//                     e.stopPropagation();
//                 }
//             }

//             function handleMouseMove(e) {
//                 if (!isDown) return;
//                 e.preventDefault();
//                 const x = e.pageX - ribbon.offsetLeft;
//                 const walk = (x - startX) * 2;
//                 ribbon.scrollLeft = scrollLeft - walk;
//                 isDragging = true;
//             }

//             function handleClick(e) {
//                 if (isDragging) {
//                     e.preventDefault();
//                     e.stopPropagation();
//                 }
//             }

//             ribbon.addEventListener('mousedown', handleMouseDown);
//             ribbon.addEventListener('mouseleave', handleMouseLeave);
//             ribbon.addEventListener('mouseup', handleMouseUp);
//             ribbon.addEventListener('mousemove', handleMouseMove);

//             ribbon.querySelectorAll('a').forEach(anchor => {
//                 anchor.addEventListener('click', handleClick);
//             });
//         });
//     }

//     function initDraggableRibbonThrottled() {
//         if (throttleTimeout) return;

//         throttleTimeout = setTimeout(() => {
//             initDraggableRibbon();
//             throttleTimeout = null;
//         }, 100); // Adjust the delay as needed
//     }

//     // Initial call
//     initDraggableRibbon();

//     // Set up a MutationObserver to watch for changes in the document
//     const observer = new MutationObserver(mutations => {
//         let needsReinitialize = false;
//         mutations.forEach(mutation => {
//             if (mutation.type === 'childList' || mutation.type === 'attributes') {
//                 needsReinitialize = true;
//             }
//         });

//         if (needsReinitialize) {
//             initDraggableRibbonThrottled(); // Use the throttled function
//         }
//     });

//     // Start observing the document body for changes
//     observer.observe(document.body, {
//         childList: true,
//         subtree: true,
//         attributes: true
//     });

//     // Livewire events to reinitialize
//     document.addEventListener('livewire:load', initDraggableRibbon);
//     document.addEventListener('livewire:update', initDraggableRibbonThrottled);
//     document.addEventListener('livewire:render', initDraggableRibbonThrottled);
//     document.addEventListener('livewire:navigated', initDraggableRibbonThrottled);
// });
