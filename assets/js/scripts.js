// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
  // Theme toggle
  const themeToggleBtn = document.getElementById('theme-toggle-btn');
  if (themeToggleBtn) {
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
  }
  
  // Quantity controls
  const quantityControls = document.querySelectorAll('.quantity-control');
  
  quantityControls.forEach(control => {
    const minusBtn = control.querySelector('.minus');
    const plusBtn = control.querySelector('.plus');
    const input = control.querySelector('input');
    
    if (minusBtn && plusBtn && input) {
      const min = parseInt(input.getAttribute('min')) || 1;
      const max = parseInt(input.getAttribute('max')) || 99;
      
      minusBtn.addEventListener('click', function() {
        let value = parseInt(input.value);
        value = Math.max(min, value - 1);
        input.value = value;
        
        // Trigger change event for forms that need to update totals
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
      });
      
      plusBtn.addEventListener('click', function() {
        let value = parseInt(input.value);
        value = Math.min(max, value + 1);
        input.value = value;
        
        // Trigger change event for forms that need to update totals
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
      });
    }
  });
  
  // Testimonial slider (simple version)
  const testimonialSlider = document.querySelector('.testimonial-slider');
  
  if (testimonialSlider) {
    const testimonials = testimonialSlider.querySelectorAll('.testimonial');
    
    if (testimonials.length > 1) {
      let currentIndex = 0;
      
      // Only implement automatic sliding if there are multiple testimonials
      setInterval(() => {
        testimonials.forEach(testimonial => {
          testimonial.style.opacity = '0';
          testimonial.style.transform = 'translateX(20px)';
          testimonial.style.display = 'none';
        });
        
        currentIndex = (currentIndex + 1) % testimonials.length;
        
        testimonials[currentIndex].style.display = 'block';
        
        // Trigger reflow
        testimonials[currentIndex].offsetHeight;
        
        testimonials[currentIndex].style.opacity = '1';
        testimonials[currentIndex].style.transform = 'translateX(0)';
      }, 5000);
    }
  }
  
  // Cart item total update
  const cartForm = document.getElementById('cart-form');
  
  if (cartForm) {
    const quantityInputs = cartForm.querySelectorAll('input[name^="quantity"]');
    
    quantityInputs.forEach(input => {
      input.addEventListener('change', updateCartItemTotal);
    });
    
    function updateCartItemTotal() {
      const cartItem = this.closest('.cart-item');
      if (!cartItem) return;
      
      const priceEl = cartItem.querySelector('.item-price');
      const totalEl = cartItem.querySelector('.item-total');
      
      if (!priceEl || !totalEl) return;
      
      const price = parseFloat(priceEl.textContent.replace(/[^0-9.-]+/g, ''));
      const quantity = parseInt(this.value);
      
      const total = price * quantity;
      totalEl.textContent = formatCurrency(total);
    }
    
    function formatCurrency(amount) {
      return 'Rs. ' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
  }
  
  // Star rating interactive behavior
  const starRating = document.querySelector('.star-rating');
  
  if (starRating) {
    const stars = starRating.querySelectorAll('input');
    const labels = starRating.querySelectorAll('label');
    
    stars.forEach(star => {
      star.addEventListener('change', function() {
        const value = this.value;
        highlightStars(value);
      });
    });
    
    function highlightStars(value) {
      labels.forEach(label => {
        label.classList.remove('active');
      });
      
      for (let i = 0; i < value; i++) {
        labels[4 - i].classList.add('active');
      }
    }
  }
  
  // Order status timeline animation
  const timeline = document.querySelector('.timeline');
  
  if (timeline) {
    const steps = timeline.querySelectorAll('.timeline-step');
    const connectors = timeline.querySelectorAll('.timeline-connector');
    
    // Add animation delay to each step
    steps.forEach((step, index) => {
      if (step.classList.contains('completed')) {
        step.style.transitionDelay = (index * 0.2) + 's';
        
        // Add animation class with delay
        setTimeout(() => {
          step.classList.add('animate');
        }, index * 200);
      }
    });
    
    // Add animation delay to each connector
    connectors.forEach((connector, index) => {
      if (connector.classList.contains('completed')) {
        connector.style.transitionDelay = ((index + 1) * 0.2) + 's';
        
        // Add animation class with delay
        setTimeout(() => {
          connector.classList.add('animate');
        }, (index + 1) * 200);
      }
    });
  }
  
  // Search form validation
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
  
  // Add smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      
      if (href !== '#') {
        e.preventDefault();
        
        const targetElement = document.querySelector(href);
        
        if (targetElement) {
          targetElement.scrollIntoView({
            behavior: 'smooth'
          });
        }
      }
    });
  });
  
  // Dropdown menus
  const dropdowns = document.querySelectorAll('.dropdown');
  
  dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.dropdown-toggle');
    const menu = dropdown.querySelector('.dropdown-menu');
    
    if (toggle && menu) {
      // Close all other dropdowns when one is opened
      toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Close all other dropdowns
        dropdowns.forEach(otherDropdown => {
          if (otherDropdown !== dropdown) {
            otherDropdown.querySelector('.dropdown-menu').classList.remove('active');
          }
        });
        
        menu.classList.toggle('active');
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
          menu.classList.remove('active');
        }
      });
    }
  });
});