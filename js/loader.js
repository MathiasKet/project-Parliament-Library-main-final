// Loader functionality
document.addEventListener('DOMContentLoaded', function() {
    // Show loader when page starts loading
    const loaderContainer = document.querySelector('.loader-container');
    
    // Hide loader when page is fully loaded
    window.addEventListener('load', function() {
        // Add a small delay for smoother transition
        setTimeout(function() {
            if (loaderContainer) {
                loaderContainer.classList.add('hidden');
                
                // Remove loader from DOM after animation completes
                loaderContainer.addEventListener('transitionend', function() {
                    loaderContainer.style.display = 'none';
                }, { once: true });
            }
        }, 500); // Adjust timing as needed
    });
    
    // Handle page transitions
    document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't intercept if it's a hash link or external link
            if (this.href.includes('#')) return;
            if (this.href.startsWith('javascript:')) return;
            if (this.target === '_blank') return;
            
            // Don't intercept if it's the same page
            if (this.href === window.location.href.split('#')[0]) return;
            
            // Show loader when navigating to a new page
            if (loaderContainer) {
                loaderContainer.style.display = 'flex';
                loaderContainer.classList.remove('hidden');
            }
        });
    });
});
