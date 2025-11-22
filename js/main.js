document.addEventListener('DOMContentLoaded', function() {
    // Initialize Green Library functionality
    initGreenLibrary();
    
    // Initialize other main page functionality
    initMainPage();
});

function initGreenLibrary() {
    // Get the "Learn More" button
    const learnMoreBtn = document.querySelector('.initiative-btn');
    
    // Add click event listener to the button
    if (learnMoreBtn) {
        learnMoreBtn.addEventListener('click', function(e) {
            // If this is a direct link to green-library.html, allow default navigation
            if (
                learnMoreBtn.tagName === 'A' &&
                learnMoreBtn.getAttribute('href') === 'green-library.html'
            ) {
                return; // Allow default link behavior
            }
            // Otherwise, prevent default and run custom logic
            e.preventDefault();
            
            // If there's a dedicated green library page, navigate to it
            // Otherwise, show more content in an expandable section
            const greenLibrarySection = document.getElementById('green-library-details');
            
            if (greenLibrarySection) {
                // Toggle the visibility of additional content
                greenLibrarySection.classList.toggle('expanded');
                
                // Change button text based on state
                if (greenLibrarySection.classList.contains('expanded')) {
                    learnMoreBtn.textContent = 'Show Less';
                    
                    // Smooth scroll to the expanded section
                    greenLibrarySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    learnMoreBtn.textContent = 'Learn More About Our Green Initiatives';
                }
            } else {
                // If no dedicated section exists, you could:
                // 1. Navigate to a dedicated page
                // window.location.href = 'green-library.html';
                
                // OR 2. Show a modal with more information
                showGreenLibraryModal();
            }
        });
    }
    
    // Add hover effects to feature cards
    const features = document.querySelectorAll('.feature');
    features.forEach(feature => {
        feature.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 6px 12px rgba(0, 0, 0, 0.15)';
        });
        
        feature.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
        });
        
        // Make the entire feature card clickable
        feature.style.cursor = 'pointer';
        feature.addEventListener('click', function() {
            // You could add specific functionality when a feature is clicked
            const title = this.querySelector('h5').textContent;
            console.log(`Feature clicked: ${title}`);
            // Example: Show more details about this specific feature
            // showFeatureDetails(title);
        });
    });
}

function showGreenLibraryModal() {
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Our Green Library Initiative</h3>
            <div class="modal-body">
                <p>Our Green Library Initiative is a comprehensive program designed to minimize our environmental impact while maintaining excellent library services. Key aspects include:</p>
                <ul>
                    <li>Energy-efficient lighting and climate control systems</li>
                    <li>Comprehensive recycling and waste reduction programs</li>
                    <li>Digital resources to reduce paper consumption</li>
                    <li>Sustainable building materials and practices</li>
                    <li>Community education on environmental responsibility</li>
                </ul>
                <p>We're committed to creating a sustainable future for our community and the planet.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-close-btn">Close</button>
                <button class="modal-contact-btn">Contact Us</button>
            </div>
        </div>
    `;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Show the modal
    modal.style.display = 'block';
    
    // Close modal functionality
    const closeBtn = modal.querySelector('.close-btn');
    const closeModalBtn = modal.querySelector('.modal-close-btn');
    const contactBtn = modal.querySelector('.modal-contact-btn');
    
    const closeModal = () => {
        modal.style.display = 'none';
        // Remove modal from DOM after animation
        setTimeout(() => {
            document.body.removeChild(modal);
        }, 300);
    };
    
    closeBtn.addEventListener('click', closeModal);
    closeModalBtn.addEventListener('click', closeModal);
    
    contactBtn.addEventListener('click', () => {
        // Navigate to contact page or show contact form
        window.location.href = 'contact.html';
    });
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
}

function initMainPage() {
    // Initialize any other main page functionality here
    // For example, smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Export functions if using modules
// export { initGreenLibrary, showGreenLibraryModal };
