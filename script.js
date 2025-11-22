// DOM Elements
const searchInput = document.getElementById('searchInput');
const categorySelect = document.getElementById('categorySelect');
const searchBtn = document.querySelector('.search-btn');
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const mobileMenu = document.querySelector('.mobile-menu');
const mobileDropdownBtns = document.querySelectorAll('.mobile-dropdown-btn');
const mainDropdownBtn = document.querySelector('.dropdown-btn');
const mainDropdownMenu = document.querySelector('.dropdown-menu');

// Toggle mobile menu
if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        mobileMenuBtn.setAttribute('aria-expanded', mobileMenu.classList.contains('active'));
    });
}

// Handle mobile dropdowns
mobileDropdownBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const isExpanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', !isExpanded);
        const menu = btn.nextElementSibling;
        if (menu) {
            menu.classList.toggle('active');
        }
    });
});

// Handle main dropdown
if (mainDropdownBtn && mainDropdownMenu) {
    mainDropdownBtn.addEventListener('click', () => {
        const isExpanded = mainDropdownBtn.getAttribute('aria-expanded') === 'true';
        mainDropdownBtn.setAttribute('aria-expanded', !isExpanded);
        mainDropdownMenu.classList.toggle('active');
    });
}

// Close mobile menu when clicking outside
if (mobileMenu && mobileMenuBtn) {
    document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            mobileMenu.classList.remove('active');
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// Handle keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (mobileMenu) mobileMenu.classList.remove('active');
        if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
        if (mainDropdownMenu) mainDropdownMenu.classList.remove('active');
        if (mainDropdownBtn) mainDropdownBtn.setAttribute('aria-expanded', 'false');
    }
});

// Search functionality with loading state
if (searchBtn && searchInput && categorySelect) {
    searchBtn.addEventListener('click', async () => {
        const searchQuery = searchInput.value.trim();
        const category = categorySelect.value;
        
        if (searchQuery) {
            // Add loading state
            searchBtn.classList.add('loading');
            searchBtn.disabled = true;
            
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                console.log('Searching for:', searchQuery, 'in category:', category);
                alert(`Searching for "${searchQuery}" in ${category} category`);
            } catch (error) {
                console.error('Search failed:', error);
                alert('Search failed. Please try again.');
            } finally {
                // Remove loading state
                searchBtn.classList.remove('loading');
                searchBtn.disabled = false;
            }
        } else {
            alert('Please enter a search term');
        }
    });

    // Enter key functionality for search
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });
}

// Dropdown functionality
const dropdownBtn = document.querySelector('.dropdown-btn');
const dropdown = document.querySelector('.dropdown');
const dropdownMenu = document.querySelector('.dropdown-menu');

if (dropdownBtn && dropdown && dropdownMenu) {
dropdownBtn.addEventListener('click', (e) => {
    e.stopPropagation();
        dropdown.classList.toggle('open');
});
    document.addEventListener('click', () => {
        dropdown.classList.remove('open');
});
dropdownMenu.addEventListener('click', (e) => {
    e.stopPropagation();
});
}

// Add smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
            // Close mobile menu if open
            if (mobileMenu) {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });
});

// Slideshow functionality
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');

if (slides.length > 0 && dots.length > 0 && prevBtn && nextBtn) {
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => {
            dot.classList.remove('active');
            dot.setAttribute('aria-selected', 'false');
        });
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        dots[index].setAttribute('aria-selected', 'true');
        currentSlide = index;
    }

    function nextSlide() {
        showSlide((currentSlide + 1) % slides.length);
    }

    function prevSlide() {
        showSlide((currentSlide - 1 + slides.length) % slides.length);
    }

    // Start slideshow
    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 3000);
    }

    // Stop slideshow
    function stopSlideshow() {
        clearInterval(slideInterval);
    }

    // Event listeners for slideshow controls
    prevBtn.addEventListener('click', () => {
        prevSlide();
        stopSlideshow();
        startSlideshow();
    });

    nextBtn.addEventListener('click', () => {
        nextSlide();
        stopSlideshow();
        startSlideshow();
    });

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopSlideshow();
            startSlideshow();
        });
    });

    // Pause slideshow when hovering over controls
    [prevBtn, nextBtn, ...dots].forEach(element => {
        element.addEventListener('mouseenter', stopSlideshow);
        element.addEventListener('mouseleave', startSlideshow);
    });

    // Start slideshow
    startSlideshow();
} 

// Ask a Librarian Chat Functionality

document.addEventListener('DOMContentLoaded', function () {
  const chatBtn = document.getElementById('chatBtn');
  const chatWindow = document.getElementById('chatWindow');
  const chatClose = document.getElementById('chatClose');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');
  const chatMessages = document.getElementById('chatMessages');

  // 1. Toggle chat window
  if (chatBtn && chatWindow) {
    chatBtn.addEventListener('click', function () {
      chatWindow.classList.toggle('active');
      if (chatWindow.classList.contains('active')) {
        chatInput.focus();
      }
    });
  }

  // 2. Close chat window
  if (chatClose && chatWindow) {
    chatClose.addEventListener('click', function () {
      chatWindow.classList.remove('active');
    });
    window.addEventListener('click', function (e) {
      if (chatWindow.classList.contains('active') && !chatWindow.contains(e.target) && e.target !== chatBtn) {
        chatWindow.classList.remove('active');
      }
    });
  }

  // 3. Send message
  if (chatForm && chatInput && chatMessages) {
    chatForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const msg = chatInput.value.trim();
      if (!msg) return;
      // Add user message
      const userMsg = document.createElement('div');
      userMsg.className = 'chat-message';
      userMsg.textContent = msg;
      chatMessages.appendChild(userMsg);
      chatInput.value = '';
      chatMessages.scrollTop = chatMessages.scrollHeight;
      // Simulate librarian reply
      setTimeout(() => {
        const reply = document.createElement('div');
        reply.className = 'chat-message librarian';
        reply.textContent = 'Thank you for your question! A librarian will assist you shortly.';
        chatMessages.appendChild(reply);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }, 1200);
    });
  }

  // Hide Digital Assets dropdown by default
  var digitalDropdown = document.querySelector('.dropdown-menu#digital-assets-menu');
  var digitalBtn = document.querySelector('.dropdown-btn');
  if (digitalDropdown && digitalBtn) {
    digitalDropdown.style.display = 'none';
    digitalBtn.setAttribute('aria-expanded', 'false');
    digitalBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = digitalDropdown.style.display === 'block';
      digitalDropdown.style.display = isOpen ? 'none' : 'block';
      digitalBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });
    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
      if (!digitalDropdown.contains(e.target) && e.target !== digitalBtn) {
        digitalDropdown.style.display = 'none';
        digitalBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Hide Mobile Digital Assets dropdown by default
  var mobileDropdown = document.querySelector('.mobile-dropdown-menu#mobile-digital-assets-menu');
  var mobileBtn = document.querySelector('.mobile-dropdown-btn');
  if (mobileDropdown && mobileBtn) {
    mobileDropdown.style.display = 'none';
    mobileBtn.setAttribute('aria-expanded', 'false');
    mobileBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = mobileDropdown.style.display === 'block';
      mobileDropdown.style.display = isOpen ? 'none' : 'block';
      mobileBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });
    document.addEventListener('click', function (e) {
      if (!mobileDropdown.contains(e.target) && e.target !== mobileBtn) {
        mobileDropdown.style.display = 'none';
        mobileBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }
});

// Newsletter form
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = newsletterForm.querySelector('input[type="email"]').value;
        
        // Add loading state
        const submitBtn = newsletterForm.querySelector('button[type="submit"]');
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Simulate subscription (replace with actual subscription functionality)
        setTimeout(() => {
            console.log(`Subscribing email: ${email}`);
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            newsletterForm.reset();
        }, 1000);
    });
} 