/**
 * Main JavaScript File
 * Excellence Academy School Management System
 */

// Mobile Menu Toggle and Search
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.getElementById('mainNav');
    const searchButton = document.getElementById('searchButton');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchClose = document.getElementById('searchClose');
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const searchResults = document.getElementById('searchResults');
    
    function setMobileMenuOpen(isOpen) {
        mainNav.classList.toggle('active', isOpen);
        mobileMenuToggle.classList.toggle('active', isOpen);
        const menuIcon = mobileMenuToggle.querySelector('.menu-icon');
        const closeIcon = mobileMenuToggle.querySelector('.close-icon');
        if (menuIcon) menuIcon.style.display = isOpen ? 'none' : 'block';
        if (closeIcon) closeIcon.style.display = isOpen ? 'block' : 'none';
    }

    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            setMobileMenuOpen(!mainNav.classList.contains('active'));
        });

        document.addEventListener('click', function(e) {
            if (mainNav.classList.contains('active') &&
                !mainNav.contains(e.target) &&
                !mobileMenuToggle.contains(e.target)) {
                setMobileMenuOpen(false);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mainNav.classList.contains('active')) {
                setMobileMenuOpen(false);
            }
        });

        mainNav.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (!this.classList.contains('dropdown-trigger') &&
                    !this.closest('.dropdown-menu')) {
                    setTimeout(() => setMobileMenuOpen(false), 300);
                }
            });
        });

        mainNav.querySelectorAll('.dropdown-menu a').forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(() => setMobileMenuOpen(false), 300);
            });
        });
    }
    
    // Mobile dropdown toggle - prevent overlapping
    document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = this.closest('.nav-dropdown');
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns first
            document.querySelectorAll('.mobile-nav .nav-dropdown').forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('active');
                }
            });
            
            // Toggle the clicked dropdown
            if (isActive) {
                dropdown.classList.remove('active');
            } else {
                dropdown.classList.add('active');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            document.querySelectorAll('.mobile-nav .nav-dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Prevent navigation on dropdown parent links (hover only)
    document.querySelectorAll('.top-nav-dropdown > .top-nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
    
    // Search Overlay
    if (searchButton && searchOverlay) {
        searchButton.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 100);
        });
    }
    
    function closeSearch() {
        if (!searchOverlay) return;
        searchOverlay.classList.remove('active');
        document.body.style.overflow = '';
        if (searchInput) searchInput.value = '';
        updateSearchResults([]);
    }

    if (searchClose && searchOverlay) {
        searchClose.addEventListener('click', closeSearch);
    }

    if (searchOverlay) {
        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) closeSearch();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchOverlay && searchOverlay.classList.contains('active')) {
            closeSearch();
        }
    });
    
    // Search functionality
    function updateSearchResults(results) {
        if (!searchResults) return;
        
        if (results.length === 0) {
            if (searchInput && searchInput.value.trim()) {
                searchResults.innerHTML = `
                    <div class="search-placeholder">
                        <span class="search-placeholder-icon">🔍</span>
                        <p>No results found for "${searchInput.value}"</p>
                    </div>
                `;
            } else {
                searchResults.innerHTML = `
                    <div class="search-placeholder">
                        <span class="search-placeholder-icon">🔍</span>
                        <p>Start typing to search...</p>
                    </div>
                `;
            }
            return;
        }
        
        searchResults.innerHTML = results.map(result => `
            <div class="search-result-item" onclick="window.location.href='${result.url}'">
                <span class="search-result-type">${result.type}</span>
                <div class="search-result-title">${result.title}</div>
                ${result.description ? `<div class="search-result-description">${result.description}</div>` : ''}
            </div>
        `).join('');
    }
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim().toLowerCase();
            
            if (query.length < 2) {
                updateSearchResults([]);
                return;
            }
            
            searchTimeout = setTimeout(() => {
                // Simple search - you can enhance this with AJAX
                const results = [];
                
                // Search pages
                const pages = [
                    { title: 'Home', url: 'index.html', type: 'Page', description: 'Welcome to Excellence Academy' },
                    { title: 'About Us', url: 'about.html', type: 'Page', description: 'Learn about our school' },
                    { title: 'Teachers', url: 'teachers.html', type: 'Page', description: 'Meet our teaching staff' },
                    { title: 'Events', url: 'events.html', type: 'Page', description: 'Upcoming school events' },
                    { title: 'Clubs', url: 'clubs.html', type: 'Page', description: 'Student clubs and organizations' },
                    { title: 'Facilities', url: 'facilities.html', type: 'Page', description: 'Our school facilities' },
                    { title: 'Contact', url: 'contact.html', type: 'Page', description: 'Get in touch with us' },
                ];
                
                pages.forEach(page => {
                    if (page.title.toLowerCase().includes(query) || 
                        page.description.toLowerCase().includes(query)) {
                        results.push(page);
                    }
                });
                
                updateSearchResults(results);
            }, 300);
        });
    }
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput ? searchInput.value.trim() : '';
            if (query) {
                // Navigate to first result or search page
                const firstResult = searchResults.querySelector('.search-result-item');
                if (firstResult) {
                    firstResult.click();
                }
            }
        });
    }
    
    // Add fade-in animation to elements
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all cards and sections
    document.querySelectorAll('.card, .stat-card, .school-life-card, .sport-card, .value-card, .news-card, .facility-card, .teacher-card, .event-card, .testimonial-card').forEach(el => {
        observer.observe(el);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Back to Top Button
    const backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        // Smooth scroll to top when clicked
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add smooth animations on scroll
    const animatedElements = document.querySelectorAll('.school-life-card, .sport-card, .value-card, .news-card, .facility-card, .teacher-card, .event-card, .testimonial-card, .stat-card');
    animatedElements.forEach(el => {
        el.classList.add('animate-on-scroll');
    });
    
    const animationObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                animationObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        animationObserver.observe(el);
    });
    
    // Smooth page transitions
    document.querySelectorAll('a[href$=".php"]').forEach(link => {
        if (link.getAttribute('href').startsWith('http') || link.getAttribute('href').startsWith('#')) {
            return;
        }
        link.addEventListener('click', function(e) {
            // Add fade out effect for smooth transitions
            document.body.style.opacity = '0.95';
            document.body.style.transition = 'opacity 0.2s';
        });
    });
    
    // Form submissions with AJAX
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', handleAjaxFormSubmit);
    });
    
    // Smooth page load
    window.addEventListener('load', function() {
        document.body.style.opacity = '1';
        document.body.style.transition = 'opacity 0.5s';
    });
    
    // Create dynamic snowflakes
    function createSnowflakes() {
        const snowflakeContainers = document.querySelectorAll('.snowflakes, .snowflakes-light, .snowflakes-footer');
        
        snowflakeContainers.forEach(container => {
            const isLight = container.classList.contains('snowflakes-light');
            const isFooter = container.classList.contains('snowflakes-footer');
            const count = isLight || isFooter ? 15 : 30;
            const color = isLight ? 'rgba(128,0,32,0.2)' : isFooter ? 'rgba(255,255,255,0.4)' : 'rgba(255,255,255,0.6)';
            
            for (let i = 0; i < count; i++) {
                const snowflake = document.createElement('div');
                snowflake.innerHTML = '❄';
                snowflake.style.cssText = `
                    position: absolute;
                    font-size: ${Math.random() * 10 + 12}px;
                    color: ${color};
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                    animation: snowflake-float ${Math.random() * 10 + 10}s linear infinite;
                    animation-delay: ${Math.random() * 5}s;
                    pointer-events: none;
                    z-index: 1;
                `;
                container.appendChild(snowflake);
            }
        });
    }
    
    createSnowflakes();
    
    // Testimonials continuous slider
    const testimonialsSlider = document.getElementById('testimonialsSlider');
    if (testimonialsSlider) {
        // Pause animation on hover
        testimonialsSlider.addEventListener('mouseenter', function() {
            this.style.animationPlayState = 'paused';
        });
        
        testimonialsSlider.addEventListener('mouseleave', function() {
            this.style.animationPlayState = 'running';
        });
    }
    
    // Newsletter Modal
    const newsletterModal = document.getElementById('newsletterModal');
    const newsletterClose = document.getElementById('newsletterClose');
    const newsletterCancel = document.getElementById('newsletterCancel');
    const newsletterForm = document.getElementById('newsletterForm');
    const newsletterOverlay = newsletterModal ? newsletterModal.querySelector('.newsletter-modal-overlay') : null;
    
    function openNewsletterModal() {
        if (newsletterModal) {
            newsletterModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeNewsletterModal() {
        if (newsletterModal) {
            newsletterModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    // Make functions globally accessible
    window.openNewsletterModal = openNewsletterModal;
    window.closeNewsletterModal = closeNewsletterModal;
    
    if (newsletterClose) {
        newsletterClose.addEventListener('click', closeNewsletterModal);
    }
    
    if (newsletterCancel) {
        newsletterCancel.addEventListener('click', closeNewsletterModal);
    }
    
    if (newsletterOverlay) {
        newsletterOverlay.addEventListener('click', closeNewsletterModal);
    }
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('newsletterEmail').value;
            const name = document.getElementById('newsletterName').value;
            
            fetch('api/forms/newsletter-subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email, name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert('Successfully subscribed to newsletter!');
                    newsletterForm.reset();
                    closeNewsletterModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
    
    // Auto-open newsletter modal after 3 seconds (only once per session)
    if (!sessionStorage.getItem('newsletterShown')) {
        setTimeout(() => {
            openNewsletterModal();
            sessionStorage.setItem('newsletterShown', 'true');
        }, 3000);
    }
    
    // Give Back Accordion (only for .accordion-item, not .giveback-accordion-item)
    document.querySelectorAll('.accordion-item .accordion-header').forEach(header => {
        // Skip if it's part of giveback accordion
        if (header.closest('.giveback-accordion-item')) {
            return;
        }
        
        header.addEventListener('click', function() {
            const accordionItem = this.closest('.accordion-item');
            const isActive = accordionItem.classList.contains('active');
            
            // Close all accordions
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Toggle current accordion
            if (!isActive) {
                accordionItem.classList.add('active');
            }
        });
    });
});

/**
 * Handle AJAX form submission
 */
function handleAjaxFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const action = form.getAttribute('action') || form.dataset.action;
    const method = form.getAttribute('method') || 'POST';
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }
    
    // Convert FormData to JSON if needed
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch(action, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
        
        if (data.error) {
            showMessage(data.error, 'error');
        } else {
            showMessage(data.message || 'Form submitted successfully!', 'success');
            form.reset();
        }
    })
    .catch(error => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
        showMessage('An error occurred. Please try again.', 'error');
        console.error('Form submission error:', error);
    });
}

/**
 * Show message to user
 */
function showMessage(message, type = 'info') {
    // Suppress "Fatal error" messages globally - never show them to users
    if (type === 'error' && message && (message.toLowerCase().includes('fatal error') || message.toLowerCase().includes('server error: fatal'))) {
        console.error('Fatal error suppressed from message:', message);
        return; // Don't show the message
    }
    
    // Remove existing messages
    const existing = document.querySelector('.message-alert');
    if (existing) {
        existing.remove();
    }
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `message-alert message-${type}`;
    messageEl.textContent = message;
    messageEl.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#17a2b8'};
        color: white;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(messageEl);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        messageEl.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => messageEl.remove(), 300);
    }, 5000);
}

/**
 * API Helper Functions
 */
const API = {
    baseURL: '/api',
    
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API request error:', error);
            throw error;
        }
    },
    
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },
    
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

// Export for use in other scripts
window.API = API;
window.showMessage = showMessage;
