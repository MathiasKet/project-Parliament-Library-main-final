// Admin Authentication Script
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the login page
    if (document.querySelector('.admin-login')) {
        initLoginPage();
    }
    
    // Check if we're on the dashboard page
    if (document.querySelector('.admin-dashboard')) {
        initDashboardPage();
    }
});

// Initialize login page functionality
function initLoginPage() {
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.querySelector('.eye-icon');
    
    // Toggle password visibility
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            if (eyeIcon) {
                if (type === 'password') {
                    eyeIcon.innerHTML = `
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    `;
                } else {
                    eyeIcon.innerHTML = `
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    `;
                }
            }
        });
    }
    
    // Handle form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('remember').checked;
            
            // Simple validation
            if (!username || !password) {
                showAlert('Please enter both username and password', 'error');
                return;
            }
            
            // Show loading state
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            submitButton.disabled = true;
            
            // Simulate API call (replace with actual authentication)
            setTimeout(() => {
                // This is a demo - in a real app, validate against your backend
                if (username === 'admin' && password === 'admin123') {
                    // Store authentication state (in a real app, use secure tokens)
                    sessionStorage.setItem('isAuthenticated', 'true');
                    sessionStorage.setItem('username', username);
                    
                    // Store in localStorage if "Remember me" is checked
                    if (rememberMe) {
                        localStorage.setItem('rememberedUser', username);
                    } else {
                        localStorage.removeItem('rememberedUser');
                    }
                    
                    // Redirect to dashboard
                    window.location.href = 'dashboard.html';
                } else {
                    // Show error message
                    showAlert('Invalid username or password', 'error');
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                }
            }, 1000);
        });
    }
    
    // Check for remembered user
    const rememberedUser = localStorage.getItem('rememberedUser');
    if (rememberedUser) {
        document.getElementById('username').value = rememberedUser;
        document.getElementById('remember').checked = true;
    }
}

// Initialize dashboard page functionality
function initDashboardPage() {
    // Check authentication
    const isAuthenticated = sessionStorage.getItem('isAuthenticated');
    
    if (!isAuthenticated) {
        // Redirect to login if not authenticated
        window.location.href = 'login.html';
        return;
    }
    
    // Set username in the sidebar
    const username = sessionStorage.getItem('username') || 'Admin';
    const usernameElements = document.querySelectorAll('.profile-info h4');
    usernameElements.forEach(el => {
        el.textContent = username;
    });
    
    // Handle logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear session
            sessionStorage.removeItem('isAuthenticated');
            sessionStorage.removeItem('username');
            
            // Redirect to login
            window.location.href = 'login.html';
        });
    }
    
    // Initialize other dashboard functionality
    initSidebarToggle();
    initThemeToggle();
    initMobileMenu();
    initDataTable();
}

// Toggle sidebar
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const sidebarOverlay = document.createElement('div');
    sidebarOverlay.className = 'sidebar-overlay';
    
    if (sidebarToggle && adminSidebar) {
        // Add overlay after sidebar
        adminSidebar.parentNode.insertBefore(sidebarOverlay, adminSidebar.nextSibling);
        
        // Toggle sidebar
        const toggleSidebar = () => {
            adminSidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
        };
        
        // Toggle on button click
        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            toggleSidebar();
        });
        
        // Close sidebar when clicking outside on mobile
        sidebarOverlay.addEventListener('click', () => {
            if (window.innerWidth < 1200) {
                adminSidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1200) {
                adminSidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
    }
}

// Toggle dark/light theme
function initThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    
    // Check for saved theme preference or use system preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        themeToggle.checked = savedTheme === 'dark';
    } else if (prefersDark) {
        document.documentElement.setAttribute('data-theme', 'dark');
        themeToggle.checked = true;
    }
    
    // Toggle theme
    themeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
        }
    });
}

// Initialize mobile menu
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });
    }
}

// Initialize data table functionality
function initDataTable() {
    // This is a simplified version - in a real app, you might use a library like DataTables
    const dataTable = document.querySelector('.data-table');
    
    if (dataTable) {
        // Add sortable functionality to table headers
        const headers = dataTable.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            
            header.addEventListener('click', () => {
                const columnIndex = header.cellIndex;
                const isNumeric = header.getAttribute('data-type') === 'number';
                const isAscending = !header.classList.contains('asc');
                const tbody = dataTable.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                // Remove sort classes from all headers
                headers.forEach(h => {
                    h.classList.remove('asc', 'desc');
                });
                
                // Set sort direction for current header
                header.classList.add(isAscending ? 'asc' : 'desc');
                
                // Sort rows
                rows.sort((a, b) => {
                    const aValue = a.cells[columnIndex].textContent.trim();
                    const bValue = b.cells[columnIndex].textContent.trim();
                    
                    if (isNumeric) {
                        return isAscending 
                            ? parseFloat(aValue) - parseFloat(bValue)
                            : parseFloat(bValue) - parseFloat(aValue);
                    } else {
                        return isAscending
                            ? aValue.localeCompare(bValue)
                            : bValue.localeCompare(aValue);
                    }
                });
                
                // Re-append sorted rows
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    }
}

// Show alert message
function showAlert(message, type = 'info') {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.alert-message');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert-message alert-${type} fade-in`;
    alert.innerHTML = `
        <div class="alert-content">
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="alert-close">&times;</button>
    `;
    
    // Add to page
    document.body.appendChild(alert);
    
    // Auto-remove after 5 seconds
    const removeAlert = () => {
        alert.classList.remove('fade-in');
        alert.classList.add('fade-out');
        setTimeout(() => alert.remove(), 300);
    };
    
    // Close button
    const closeBtn = alert.querySelector('.alert-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', removeAlert);
    }
    
    setTimeout(removeAlert, 5000);
}

// Add global click handler for dropdowns
document.addEventListener('click', function(e) {
    // Close dropdowns when clicking outside
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
    
    // Toggle dropdowns
    const dropdownToggle = e.target.closest('.dropdown-toggle');
    if (dropdownToggle) {
        e.preventDefault();
        const dropdown = dropdownToggle.closest('.dropdown');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== menu) m.classList.remove('show');
        });
        
        // Toggle current dropdown
        menu.classList.toggle('show');
    }
});
