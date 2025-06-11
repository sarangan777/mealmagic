// DOM Elements and Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (themeToggleBtn) {
        // Check for saved dark mode preference or default to light mode
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.classList.add('dark-mode');
        }
        
        themeToggleBtn.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-mode');
            
            // Save preference to localStorage
            if (document.documentElement.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        });
    }
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            document.body.classList.toggle('menu-open');
            
            // Toggle aria-expanded attribute
            const isExpanded = navLinks.classList.contains('active');
            mobileMenuToggle.setAttribute('aria-expanded', isExpanded);
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenuToggle.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('active');
                document.body.classList.remove('menu-open');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // Language selector
    const langSelect = document.getElementById('lang-select');
    if (langSelect) {
        langSelect.addEventListener('change', function() {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('lang', this.value);
            window.location.href = currentUrl.toString();
        });
    }
    
    // Quantity controls
    const quantityControls = document.querySelectorAll('.quantity-control');
    
    quantityControls.forEach(control => {
        const minusBtn = control.querySelector('.quantity-btn:first-child');
        const plusBtn = control.querySelector('.quantity-btn:last-child');
        const input = control.querySelector('input');
        
        if (minusBtn && plusBtn && input) {
            const min = parseInt(input.getAttribute('min')) || 1;
            const max = parseInt(input.getAttribute('max')) || 99;
            
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                value = Math.max(min, value - 1);
                input.value = value;
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                value = Math.min(max, value + 1);
                input.value = value;
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            });
            
            // Validate input on change
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < min) {
                    this.value = min;
                } else if (value > max) {
                    this.value = max;
                }
            });
        }
    });
    
    // Search functionality
    const searchForm = document.querySelector('.search-bar form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
    
    // Menu filtering
    const categoryFilters = document.querySelectorAll('.category-filter');
    const foodCards = document.querySelectorAll('.food-card');
    
    if (categoryFilters.length > 0 && foodCards.length > 0) {
        categoryFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all filters
                categoryFilters.forEach(f => f.classList.remove('active'));
                
                // Add active class to clicked filter
                this.classList.add('active');
                
                const categoryId = this.getAttribute('data-category');
                
                // Filter food cards
                foodCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    
                    if (!categoryId || categoryId === 'all' || cardCategory === categoryId) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeIn 0.3s ease';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // Cart functionality
    const cartForm = document.getElementById('cart-form');
    if (cartForm) {
        const quantityInputs = cartForm.querySelectorAll('input[name^="quantity"]');
        
        quantityInputs.forEach(input => {
            input.addEventListener('change', updateCartTotals);
        });
        
        function updateCartTotals() {
            let subtotal = 0;
            const cartItems = cartForm.querySelectorAll('.cart-item');
            
            cartItems.forEach(item => {
                const priceEl = item.querySelector('.item-price');
                const quantityEl = item.querySelector('input[name^="quantity"]');
                const totalEl = item.querySelector('.item-total');
                
                if (priceEl && quantityEl && totalEl) {
                    const price = parseFloat(priceEl.textContent.replace(/[^0-9.-]+/g, ''));
                    const quantity = parseInt(quantityEl.value);
                    const itemTotal = price * quantity;
                    
                    totalEl.textContent = formatCurrency(itemTotal);
                    subtotal += itemTotal;
                }
            });
            
            // Update cart summary
            const subtotalEl = document.querySelector('.cart-summary .summary-item:first-child span:last-child');
            const totalEl = document.querySelector('.summary-total span:last-child');
            
            if (subtotalEl) {
                subtotalEl.textContent = formatCurrency(subtotal);
            }
            
            // Calculate delivery fee
            const deliveryFee = subtotal >= 2000 ? 0 : 150;
            const finalTotal = subtotal + deliveryFee;
            
            if (totalEl) {
                totalEl.textContent = formatCurrency(finalTotal);
            }
        }
    }
    
    // Star rating functionality
    const starRatings = document.querySelectorAll('.star-rating');
    
    starRatings.forEach(rating => {
        const stars = rating.querySelectorAll('input[type="radio"]');
        const labels = rating.querySelectorAll('label');
        
        labels.forEach((label, index) => {
            label.addEventListener('mouseenter', function() {
                highlightStars(rating, index + 1);
            });
            
            label.addEventListener('mouseleave', function() {
                const checkedStar = rating.querySelector('input[type="radio"]:checked');
                const checkedValue = checkedStar ? parseInt(checkedStar.value) : 0;
                highlightStars(rating, checkedValue);
            });
            
            label.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('for').replace('star', ''));
                highlightStars(rating, value);
            });
        });
        
        function highlightStars(ratingEl, count) {
            const labels = ratingEl.querySelectorAll('label');
            labels.forEach((label, index) => {
                if (index < count) {
                    label.style.color = '#FFC107';
                } else {
                    label.style.color = '#E0E0E0';
                }
            });
        }
    });
    
    // Dropdown menus
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close all other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.querySelector('.dropdown-menu').style.display = 'none';
                    }
                });
                
                // Toggle current dropdown
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            
            if (href !== '#') {
                e.preventDefault();
                
                const targetElement = document.querySelector(href);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                const formGroup = field.closest('.form-group');
                
                if (!field.value.trim()) {
                    isValid = false;
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                    }
                    field.focus();
                } else {
                    if (formGroup) {
                        formGroup.classList.remove('has-error');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Loading states for buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                this.disabled = true;
                this.textContent = 'Loading...';
                
                // Re-enable after 3 seconds (fallback)
                setTimeout(() => {
                    this.disabled = false;
                    this.textContent = this.getAttribute('data-original-text') || 'Submit';
                }, 3000);
            }
        });
    });
    
    // Image lazy loading
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for browsers without IntersectionObserver
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
    
    // Notification system
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert ${type}`;
        notification.innerHTML = `<p>${message}</p>`;
        
        // Insert at the top of main content
        const mainContent = document.querySelector('.main-content .container');
        if (mainContent) {
            mainContent.insertBefore(notification, mainContent.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }
    };
});

// Utility functions
function formatCurrency(amount) {
    return 'Rs. ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideIn {
        from { transform: translateX(-100%); }
        to { transform: translateX(0); }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
    
    .slide-in {
        animation: slideIn 0.3s ease;
    }
    
    .pulse {
        animation: pulse 0.3s ease;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;
document.head.appendChild(style);