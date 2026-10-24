/**
 * Main JavaScript File
 * Excellence Academy School Management System
 */

function escapeSearchText(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

const SEARCH_PAGES = [
    { title: 'Home', url: 'index.html', type: 'Page', description: 'Welcome to the school homepage' },
    { title: 'About Us', url: 'about.html', type: 'Page', description: 'Learn about our school' },
    { title: 'School Background', url: 'school-background.html', type: 'Page', description: 'Our history and background' },
    { title: 'Teachers', url: 'teachers.html', type: 'Page', description: 'Meet our teaching staff' },
    { title: 'Testimonials', url: 'testimonials.html', type: 'Page', description: 'What families say about us' },
    { title: 'Gallery', url: 'gallery.html', type: 'Page', description: 'School gallery and photos' },
    { title: 'Events', url: 'events.html', type: 'Page', description: 'Upcoming school events' },
    { title: 'Clubs', url: 'clubs.html', type: 'Page', description: 'Student clubs and organizations' },
    { title: 'Activities', url: 'activities.html', type: 'Page', description: 'Student activities' },
    { title: 'Facilities', url: 'facilities.html', type: 'Page', description: 'Our school facilities' },
    { title: 'Curriculum', url: 'curriculum.html', type: 'Page', description: 'Academic curriculum' },
    { title: 'Admission', url: 'admission.html', type: 'Page', description: 'Apply for admission' },
    { title: 'News', url: 'news.html', type: 'Page', description: 'School news and updates' },
    { title: 'Announcements', url: 'announcements.html', type: 'Page', description: 'Important announcements' },
    { title: 'Sponsorship', url: 'sponsorship.html', type: 'Page', description: 'Support a student' },
    { title: 'Give Back', url: 'give-back.html', type: 'Page', description: 'Give back to the school' },
    { title: 'Parents', url: 'parents.html', type: 'Page', description: 'Parent portal' },
    { title: 'Resources', url: 'resources.html', type: 'Page', description: 'Learning resources' },
    { title: 'Contact', url: 'contact.html', type: 'Page', description: 'Get in touch with us' }
];

function closeSiteSearch() {
    const searchOverlay = document.getElementById('searchOverlay');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    if (searchOverlay) searchOverlay.classList.remove('active');
    document.body.style.overflow = '';
    if (searchInput) searchInput.value = '';
    if (searchResults) {
        searchResults.innerHTML = `
            <div class="search-placeholder">
                <p>Start typing to search...</p>
            </div>
        `;
    }
}

function setMobileMenuOpen(isOpen) {
    const mainNav = document.getElementById('mainNav');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    if (!mainNav || !mobileMenuToggle) return;
    mainNav.classList.toggle('active', isOpen);
    mobileMenuToggle.classList.toggle('active', isOpen);
    mobileMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    const menuIcon = mobileMenuToggle.querySelector('.menu-icon');
    const closeIcon = mobileMenuToggle.querySelector('.close-icon');
    if (menuIcon) menuIcon.style.display = isOpen ? 'none' : 'flex';
    if (closeIcon) closeIcon.style.display = isOpen ? 'flex' : 'none';
    document.body.classList.toggle('mobile-menu-open', isOpen);
}

window.initHeaderChrome = function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.getElementById('mainNav');
    const searchButton = document.getElementById('searchButton');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchClose = document.getElementById('searchClose');
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const searchResults = document.getElementById('searchResults');

    if (!window.__mobileNavDelegated) {
        window.__mobileNavDelegated = true;
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('#mobileMenuToggle');
            if (toggle) {
                e.preventDefault();
                e.stopPropagation();
                const nav = document.getElementById('mainNav');
                if (nav) setMobileMenuOpen(!nav.classList.contains('active'));
                return;
            }

            const trigger = e.target.closest('.mobile-nav .dropdown-trigger, .mobile-nav .nav-dropdown > a');
            const dropdown = trigger ? trigger.closest('.nav-dropdown') : null;
            if (trigger && dropdown && dropdown.querySelector('.dropdown-menu')) {
                e.preventDefault();
                e.stopPropagation();
                const willOpen = !dropdown.classList.contains('active');
                document.querySelectorAll('.mobile-nav .nav-dropdown.active').forEach(otherDropdown => {
                    otherDropdown.classList.remove('active');
                });
                if (willOpen) dropdown.classList.add('active');
                return;
            }

            const item = e.target.closest('.mobile-nav a');
            if (item) setTimeout(() => setMobileMenuOpen(false), 180);
        }, true);
    }

    document.querySelectorAll('.top-nav-dropdown > .top-nav-link').forEach(link => {
        if (link.dataset.chromeBound) return;
        link.dataset.chromeBound = '1';
        link.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });

    function updateSearchResults(results) {
        if (!searchResults) return;

        if (results.length === 0) {
            if (searchInput && searchInput.value.trim()) {
                searchResults.innerHTML = `
                    <div class="search-placeholder">
                        <p>No results found for "${escapeSearchText(searchInput.value)}"</p>
                    </div>
                `;
            } else {
                searchResults.innerHTML = `
                    <div class="search-placeholder">
                        <p>Start typing to search...</p>
                    </div>
                `;
            }
            return;
        }

        searchResults.innerHTML = results.map(result => `
            <a class="search-result-item" href="${escapeSearchText(result.url)}">
                <span class="search-result-type">${escapeSearchText(result.type)}</span>
                <div class="search-result-title">${escapeSearchText(result.title)}</div>
                ${result.description ? `<div class="search-result-description">${escapeSearchText(result.description)}</div>` : ''}
            </a>
        `).join('');
    }

    if (searchButton && searchOverlay && !searchButton.dataset.chromeBound) {
        searchButton.dataset.chromeBound = '1';
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            setMobileMenuOpen(false);
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 50);
        });
    }

    if (searchClose && !searchClose.dataset.chromeBound) {
        searchClose.dataset.chromeBound = '1';
        searchClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeSiteSearch();
        });
    }

    if (searchOverlay && !searchOverlay.dataset.chromeBound) {
        searchOverlay.dataset.chromeBound = '1';
        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) closeSiteSearch();
        });
    }

    if (searchInput && !searchInput.dataset.chromeBound) {
        searchInput.dataset.chromeBound = '1';
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim().toLowerCase();
            if (query.length < 2) {
                updateSearchResults([]);
                return;
            }
            searchTimeout = setTimeout(() => {
                const results = SEARCH_PAGES.filter(page =>
                    page.title.toLowerCase().includes(query) ||
                    page.description.toLowerCase().includes(query)
                );
                updateSearchResults(results);
            }, 200);
        });
    }

    if (searchForm && !searchForm.dataset.chromeBound) {
        searchForm.dataset.chromeBound = '1';
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const firstResult = searchResults ? searchResults.querySelector('.search-result-item') : null;
            if (firstResult) firstResult.click();
        });
    }

    if (!window.__headerDocBound) {
        window.__headerDocBound = true;
        document.addEventListener('click', function(e) {
            const nav = document.getElementById('mainNav');
            const toggle = document.getElementById('mobileMenuToggle');
            if (nav && toggle && nav.classList.contains('active') &&
                !nav.contains(e.target) && !toggle.contains(e.target)) {
                setMobileMenuOpen(false);
            }
            if (!e.target.closest('.nav-dropdown')) {
                document.querySelectorAll('.mobile-nav .nav-dropdown.active').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            const overlay = document.getElementById('searchOverlay');
            if (overlay && overlay.classList.contains('active')) {
                closeSiteSearch();
                return;
            }
            const nav = document.getElementById('mainNav');
            if (nav && nav.classList.contains('active')) {
                setMobileMenuOpen(false);
            }
        });
    }
};

window.initHeaderChrome();

window.refreshScrollReveal = function () {
    document.documentElement.classList.add('js-reveal');
    const heroSelector = '.hero-section, #home, .page-hero-section, .page-hero, .admission-hero-section';
    const pieceSelector = [
        '.three-column-item', '.news-card', '.academics-text', '.admissions-banner-content', '.card',
        '.teacher-card', '.facility-card', '.facility-main-card', '.facility-feature-card', '.event-card',
        '.testimonial-card', '.testimonial-card-slide', '.value-card', '.school-life-card', '.sport-card',
        '.gallery-item', '.about-combined-column', '.point-item', '.curriculum-program-card',
        '.curriculum-benefit-card', '.about-achievement-item', '.section-header',
        '.school-bg-history-section', '.school-bg-journey-section', '.why-choose-image',
        '.sponsorship-level-card', '.sponsorship-step', '.success-story-card', '.sponsorship-cta-section',
        '.giveback-accordion-item', '.need-card', '.contact-card', '.admission-section',
        '.admission-sidebar-box', '.admission-contact-box', '.admission-events-box', '.activity-card',
        '.resource-card', '.resource-detail-section', '.person-card', '.club-item', '.content-reveal-card'
    ].join(', ');
    const nodes = [];

    function pushUnique(list, el) {
        if (!el || el.nodeType !== 1) return;
        if (el.closest(heroSelector + ', .gallery-lightbox, #imageModal')) return;
        for (let i = 0; i < list.length; i++) {
            if (list[i] === el) return;
        }
        list.push(el);
    }

    document.querySelectorAll('main > section').forEach(function (section) {
        if (section.matches(heroSelector)) return;
        const found = [];
        section.querySelectorAll(pieceSelector).forEach(function (piece) {
            pushUnique(found, piece);
        });
        section.querySelectorAll('[class]').forEach(function (el) {
            const isCard = Array.prototype.some.call(el.classList, function (name) {
                return /(^|-)card$/.test(name);
            });
            if (isCard) pushUnique(found, el);
        });
        section.querySelectorAll('div').forEach(function (parent) {
            if (parent.children.length < 1) return;
            if (found.some(function (piece) { return piece === parent; })) return;
            const inlineGrid = /display\s*:\s*grid/i.test(parent.getAttribute('style') || '');
            const gridClass = typeof parent.className === 'string' && Array.prototype.some.call(parent.classList, function (name) {
                return /grid/i.test(name);
            });
            if (!inlineGrid && !gridClass) return;
            Array.prototype.forEach.call(parent.children, function (child) {
                if (!child.tagName || child.tagName === 'SCRIPT' || child.tagName === 'STYLE') return;
                if (found.some(function (piece) { return piece.contains(child) || child.contains(piece); })) return;
                pushUnique(found, child);
            });
        });
        const root = section.querySelector(':scope > .container') || section;
        Array.prototype.forEach.call(root.children, function (child) {
            if (!child.tagName || child.tagName === 'SCRIPT' || child.tagName === 'STYLE') return;
            if (found.some(function (piece) { return piece === child || child.contains(piece) || piece.contains(child); })) return;
            const text = (child.textContent || '').replace(/\s+/g, ' ').trim();
            if (text.length < 12 && !child.querySelector('img, form, video, iframe')) return;
            pushUnique(found, child);
        });
        if (!found.length) pushUnique(found, root);
        found.forEach(function (el, index) { nodes.push([el, index]); });
    });
    if (!window.__revealObserver) {
        window.__revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                entry.target.dataset.revealed = '1';
                window.__revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.05, rootMargin: '0px 0px -24px 0px' });
        const main = document.querySelector('main');
        if (main && window.MutationObserver) {
            let timer = 0;
            new MutationObserver(function () {
                clearTimeout(timer);
                timer = setTimeout(window.refreshScrollReveal, 60);
            }).observe(main, { childList: true, subtree: true });
        }
    }
    nodes.forEach(function (pair) {
        const el = pair[0];
        const index = pair[1];
        if (!el || el.dataset.revealed === '1') return;
        if (!el.classList.contains('reveal-on-scroll')) {
            el.classList.add('reveal-on-scroll');
            el.style.transitionDelay = Math.min(index, 3) * 45 + 'ms';
        }
        const rect = el.getBoundingClientRect();
        const alreadyOnScreen = rect.height > 0 && rect.top < window.innerHeight * 0.72 && rect.bottom > 40;
        if (alreadyOnScreen) {
            el.classList.add('is-visible');
            el.dataset.revealed = '1';
            window.__revealObserver.unobserve(el);
        } else {
            window.__revealObserver.observe(el);
        }
    });
    document.querySelectorAll('main img').forEach(function (img) {
        if (img.closest('.hero-section, .page-hero-section, .page-hero, .admission-hero-section, #home')) return;
        if (!img.getAttribute('loading')) img.setAttribute('loading', 'lazy');
        img.setAttribute('decoding', 'async');
    });
};

function applySavedAppearance() {
    const pageSections = {
        'index.html': 'index',
        'about.html': 'about',
        'activities.html': 'activities',
        'events.html': 'events',
        'admission.html': 'admission',
        'announcements.html': 'announcements',
        'clubs.html': 'clubs',
        'contact.html': 'contact',
        'curriculum.html': 'curriculum',
        'facilities.html': 'facilities',
        'gallery.html': 'gallery',
        'give-back.html': 'give-back',
        'news.html': 'news',
        'parents.html': 'parents',
        'people-behind-school.html': 'people-behind-school',
        'resources.html': 'resources',
        'school-background.html': 'school-background',
        'sponsorship.html': 'sponsorship',
        'teachers.html': 'teachers',
        'testimonials.html': 'testimonials'
    };

    function paint(record) {
        if (!record || !record.primary_color) return;
        const root = document.documentElement;
        root.style.setProperty('--primary-color', record.primary_color);
        if (record.secondary_color) root.style.setProperty('--secondary-color', record.secondary_color);
        if (record.text_color) root.style.setProperty('--text-color', record.text_color);
        if (record.background_color && String(record.background_color).toLowerCase() !== '#ffffff') {
            document.body.style.backgroundColor = record.background_color;
        }
    }

    const file = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
    fetch('/api/content/website-colors.php', { credentials: 'same-origin' })
        .then(function (response) { return response.ok ? response.json() : null; })
        .then(function (payload) {
            const rows = payload && Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : []);
            const byName = {};
            rows.forEach(function (row) {
                if (row && row.section_name) byName[row.section_name] = row;
            });
            paint(byName[pageSections[file] || '']);
            const header = byName.header;
            if (header && header.background_color) {
                document.querySelectorAll('.site-header').forEach(function (el) {
                    el.style.backgroundColor = header.background_color;
                    if (header.text_color) el.style.color = header.text_color;
                });
            }
            const buttons = byName.buttons;
            if (buttons && buttons.primary_color) {
                document.querySelectorAll('.btn-primary').forEach(function (el) {
                    el.style.background = buttons.primary_color;
                    el.style.borderColor = buttons.primary_color;
                });
            }
        })
        .catch(function () {});

    fetch('/api/content/button-settings.php', { credentials: 'same-origin' })
        .then(function (response) { return response.ok ? response.json() : null; })
        .then(function (payload) {
            const settings = payload && payload.settings;
            if (!settings || !settings.primary_button_color) return;
            document.querySelectorAll('.btn-primary').forEach(function (el) {
                el.style.background = settings.primary_button_color;
                el.style.borderColor = settings.primary_button_color;
            });
        })
        .catch(function () {});
}

// Mobile Menu Toggle and Search
document.addEventListener('DOMContentLoaded', function() {
    window.initHeaderChrome();
    applySavedAppearance();
    setTimeout(applySavedAppearance, 1200);

    window.refreshScrollReveal();

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
