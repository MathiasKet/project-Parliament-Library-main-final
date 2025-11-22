// Main Admin Dashboard Script
document.addEventListener('DOMContentLoaded', function() {
    // Initialize components if they exist on the page
    initCharts();
    initDateRangePicker();
    initTooltips();
    initModals();
    initFormValidation();
    initFileUpload();
    initSelect2();
    initSummernote();
    
    // Add active class to current nav item
    highlightActiveNav();
    
    // Initialize any other components
    initOtherComponents();
});

// Initialize charts
function initCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') return;
    
    // Sample data for the charts
    const chartData = {
        visitors: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Visitors',
                data: [650, 590, 800, 810, 1256, 1555],
                backgroundColor: 'rgba(0, 112, 60, 0.1)',
                borderColor: '#00703C',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        resourceTypes: {
            labels: ['Books', 'Journals', 'Articles', 'Reports', 'Others'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#00703C',
                    '#00A65A',
                    '#3D9970',
                    '#2E8B57',
                    '#228B22'
                ],
                borderWidth: 0
            }]
        },
        userActivity: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Active Users',
                    data: [45, 60, 55, 70, 65, 90, 40],
                    backgroundColor: 'rgba(0, 112, 60, 0.8)',
                    borderColor: '#00703C',
                    borderWidth: 1
                },
                {
                    label: 'New Users',
                    data: [15, 25, 20, 30, 25, 40, 20],
                    backgroundColor: 'rgba(255, 214, 0, 0.8)',
                    borderColor: '#FFD600',
                    borderWidth: 1
                }
            ]
        }
    };

    // Visitors Line Chart
    const visitorsCtx = document.getElementById('visitorsChart');
    if (visitorsCtx) {
        new Chart(visitorsCtx, {
            type: 'line',
            data: chartData.visitors,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 500
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Resource Types Doughnut Chart
    const resourceTypesCtx = document.getElementById('resourceTypesChart');
    if (resourceTypesCtx) {
        new Chart(resourceTypesCtx, {
            type: 'doughnut',
            data: chartData.resourceTypes,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // User Activity Bar Chart
    const userActivityCtx = document.getElementById('userActivityChart');
    if (userActivityCtx) {
        new Chart(userActivityCtx, {
            type: 'bar',
            data: chartData.userActivity,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        padding: 10,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 20
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
}

// Initialize date range picker
function initDateRangePicker() {
    const dateRangePicker = document.getElementById('dateRangePicker');
    
    if (dateRangePicker) {
        // This is a simplified version - in a real app, you might use a library like daterangepicker.js
        dateRangePicker.addEventListener('click', function(e) {
            e.preventDefault();
            // In a real implementation, this would open a date range picker
            console.log('Date range picker clicked');
        });
    }
}

// Initialize tooltips
function initTooltips() {
    // Check if tooltip library is loaded
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        // Fallback to native title attribute
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            el.setAttribute('title', el.getAttribute('data-bs-original-title') || '');
        });
    }
}

// Initialize modals
function initModals() {
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    });
    
    // Close modal with close button
    document.querySelectorAll('.modal-close, [data-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Open modal with data-target
    document.querySelectorAll('[data-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            if (target) {
                openModal(target.replace('#', ''));
            }
        });
    });
}

// Open modal by ID
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        modal.classList.add('show');
        
        // Focus on first input if exists
        const input = modal.querySelector('input, select, textarea');
        if (input) input.focus();
    }
}

// Close modal by ID
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }
}

// Initialize form validation
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

// Initialize file upload preview
function initFileUpload() {
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Choose file';
            const label = this.nextElementSibling;
            if (label && label.classList.contains('custom-file-label')) {
                label.textContent = fileName;
            }
            
            // Preview image if it's an image file
            if (this.files && this.files[0] && this.files[0].type.startsWith('image/')) {
                const preview = this.closest('.file-upload-wrapper').querySelector('.image-preview');
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" alt="Preview">`;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });
}

// Initialize Select2 if available
function initSelect2() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
}

// Initialize Summernote if available
function initSummernote() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.summernote) {
        $('.summernote').summernote({
            height: 300,
            minHeight: null,
            maxHeight: null,
            focus: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }
}

// Highlight active navigation item
function highlightActiveNav() {
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.html';
    
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'dashboard.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// Initialize other components
function initOtherComponents() {
    // Toggle sidebar submenus
    document.querySelectorAll('.nav-link.has-submenu').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                e.preventDefault();
                this.classList.toggle('expanded');
                const submenu = this.nextElementSibling;
                if (submenu && submenu.classList.contains('submenu')) {
                    submenu.style.maxHeight = submenu.style.maxHeight ? null : submenu.scrollHeight + 'px';
                }
            }
        });
    });
    
    // Initialize tooltips
    if (typeof tippy === 'function') {
        tippy('[data-tippy-content]', {
            animation: 'fade',
            arrow: true,
            theme: 'light'
        });
    }
    
    // Initialize sortable tables
    if (typeof Sortable !== 'undefined') {
        document.querySelectorAll('.sortable-table tbody').forEach(tbody => {
            new Sortable(tbody, {
                animation: 150,
                handle: '.sortable-handle',
                onEnd: function() {
                    // Handle reorder event
                    console.log('Items reordered');
                }
            });
        });
    }
    
    // Initialize password strength meter
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('password-strength');
    
    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrengthMeter(strength);
        });
    }
}

// Check password strength
function checkPasswordStrength(password) {
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength++;
    
    // Contains lowercase
    if (/[a-z]/.test(password)) strength++;
    
    // Contains uppercase
    if (/[A-Z]/.test(password)) strength++;
    
    // Contains number
    if (/[0-9]/.test(password)) strength++;
    
    // Contains special char
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

// Update password strength meter
function updatePasswordStrengthMeter(strength) {
    const strengthMeter = document.getElementById('password-strength');
    if (!strengthMeter) return;
    
    let strengthText = '';
    let strengthClass = '';
    
    switch(strength) {
        case 0:
        case 1:
            strengthText = 'Very Weak';
            strengthClass = 'very-weak';
            break;
        case 2:
            strengthText = 'Weak';
            strengthClass = 'weak';
            break;
        case 3:
            strengthText = 'Moderate';
            strengthClass = 'moderate';
            break;
        case 4:
            strengthText = 'Strong';
            strengthClass = 'strong';
            break;
        case 5:
            strengthText = 'Very Strong';
            strengthClass = 'very-strong';
            break;
        default:
            strengthText = '';
            strengthClass = '';
    }
    
    strengthMeter.textContent = strengthText;
    strengthMeter.className = 'password-strength ' + strengthClass;
    
    // Update width of strength bar if it exists
    const strengthBar = document.querySelector('.strength-bar');
    if (strengthBar) {
        const width = (strength / 5) * 100;
        strengthBar.style.width = width + '%';
    }
}

// Export functions that might be needed globally
window.AdminDashboard = {
    openModal: openModal,
    closeModal: closeModal,
    showAlert: window.showAlert || function() { console.log('Alert function not available'); }
};
