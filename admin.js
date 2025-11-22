document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminMain = document.querySelector('.admin-main');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                adminSidebar.classList.toggle('active');
                adminMain.classList.toggle('active');
        }
    });
}

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = adminSidebar.contains(event.target);
        const isClickOnMenuToggle = menuToggle.contains(event.target);

        if (!isClickInsideSidebar && !isClickOnMenuToggle && window.innerWidth <= 1024) {
            adminSidebar.classList.remove('active');
            adminMain.classList.remove('active');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            adminSidebar.classList.remove('active');
            adminMain.classList.remove('active');
        }
    });

    // Active link handling
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only prevent default if it's a mobile view
            if (window.innerWidth <= 1024) {
      e.preventDefault();
                navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
      }
    });
  });

    // Notification handling
    const notificationBtn = document.querySelector('.btn-outline:first-child');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Add notification functionality here
            console.log('Notifications clicked');
        });
    }

    // Profile handling
    const profileBtn = document.querySelector('.btn-outline:last-child');
    if (profileBtn) {
        profileBtn.addEventListener('click', function() {
            // Add profile functionality here
            console.log('Profile clicked');
        });
    }

    // Quick action buttons
    const quickActionBtns = document.querySelectorAll('.card-body .btn');
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent.trim();
            console.log(`${action} clicked`);
            // Add specific action handling here
        });
    });

    // Table row hover effect
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            // Add row click handling here
            console.log('Row clicked:', this.cells[0].textContent);
        });
    });

    // Initialize any charts or data visualizations here
    function initializeCharts() {
        // Add chart initialization code here
        console.log('Charts initialized');
    }

    // Call initialization functions
    initializeCharts();
}); 