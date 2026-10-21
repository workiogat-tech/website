/**
 * Header Loader - Loads header dynamically for HTML pages
 */

const HeaderLoader = {
    async load() {
        try {
            const response = await fetch('/api/public/header-data.php', { cache: 'no-store' });
            const payload = await response.json();
            if (response.ok && payload && payload.success && payload.data) {
                this.render(payload.data);
                this.init();
                return;
            }
            this.renderDefault();
        } catch (error) {
            console.error('Error loading header:', error);
            this.renderDefault();
        }
    },
    
    async loadNavigation() {
        // NO PHP - navigation loaded from localStorage via load() function
        // This function kept for compatibility but does nothing
    },
    
    render(data) {
        const headerPlaceholder = document.getElementById('header-placeholder');
        if (!headerPlaceholder) return;
        
        const branding = data.branding || {};
        const contactInfo = data.contactInfo || {};
        const navigation = data.navigation || [];
        const navChildren = data.navigationChildren || {};
        
        const email = contactInfo.email || 'info@sjs.com';
        const phone = contactInfo.phone || '0703574113';
        const schoolName = branding.school_name || 'Excellence Academy';
        const logo = branding.logo || '';
        
        let html = `
            <header class="site-header" id="mainHeader">
                <div class="header-top">
                    <div class="container">
                        <div class="header-top-content">
                            <div class="contact-info">
                                <a href="mailto:${this.escapeHtml(email)}" class="contact-item">
                                    <span class="contact-icon">
                                        <svg class="icon-mail" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </span>
                                    <span>${this.escapeHtml(email)}</span>
                                </a>
                                <a href="tel:${this.escapeHtml(phone)}" class="contact-item">
                                    <span class="contact-icon">
                                        <svg class="icon-phone" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </span>
                                    <span>${this.escapeHtml(phone)}</span>
                                </a>
                            </div>
                            <nav class="top-nav-desktop">
                                ${this.renderNavigation(navigation, navChildren, false)}
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="header-main">
                    <div class="container">
                        <div class="header-content">
                            <div class="logo">
                                <a href="index.html" class="logo-link">
                                    ${logo ? `<img src="${this.escapeHtml(logo)}" alt="${this.escapeHtml(schoolName)}" class="logo-image">` : 
                                      `<div class="logo-placeholder"><span>${this.escapeHtml(schoolName.charAt(0))}</span></div>`}
                                </a>
                            </div>
                            <div class="header-actions">
                                <button type="button" class="search-button" id="searchButton" aria-label="Search">
                                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                </button>
                                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu" aria-expanded="false">
                                    <span class="menu-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg></span>
                                    <span class="close-icon" style="display: none;" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></span>
                                </button>
                            </div>
                        </div>
                        <nav class="main-nav mobile-nav" id="mainNav">
                            ${this.renderNavigation(navigation, navChildren, true)}
                        </nav>
                    </div>
                </div>
                <div class="search-overlay" id="searchOverlay">
                    <div class="search-overlay-content">
                        <form class="search-form" id="searchForm">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search pages..." autocomplete="off">
                            <button type="submit" class="search-submit" aria-label="Search">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                            <button type="button" class="search-close" id="searchClose" aria-label="Close search">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </form>
                        <div class="search-results" id="searchResults">
                            <div class="search-placeholder"><p>Start typing to search...</p></div>
                        </div>
                    </div>
                </div>
            </header>
        `;
        
        headerPlaceholder.innerHTML = html;
    },
    
    renderNavigation(items, children, isMobile) {
        if (items.length === 0) {
            return this.renderDefaultNavigation(isMobile);
        }
        
        return items.map(item => {
            const hasChildren = children[item.id] && children[item.id].length > 0;
            const url = item.url || '#';
            const label = this.escapeHtml(item.label);
            const icon = item.icon ? this.escapeHtml(item.icon) + ' ' : '';
            
            if (hasChildren) {
                const childHtml = children[item.id].map(child => 
                    `<a href="${this.escapeHtml(child.url || '#')}">${this.escapeHtml(child.icon || '')} ${this.escapeHtml(child.label)}</a>`
                ).join('');
                
                return `
                    <div class="${isMobile ? 'nav-dropdown' : 'top-nav-dropdown'}">
                        <a href="${this.escapeHtml(url)}" class="${isMobile ? 'nav-link dropdown-trigger' : 'top-nav-link'}">
                            ${icon}${label}
                            <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </a>
                        <div class="${isMobile ? 'dropdown-menu' : 'top-dropdown-menu'}">
                            ${childHtml}
                        </div>
                    </div>
                `;
            } else {
                return `<a href="${this.escapeHtml(url)}" class="${isMobile ? 'nav-link' : 'top-nav-link'}">${icon}${label}</a>`;
            }
        }).join('');
    },
    
    renderDefaultNavigation(isMobile) {
        const prefix = isMobile ? '' : 'top-';
        return `
            <a href="index.html" class="${prefix}nav-link">Home</a>
            <div class="${prefix}nav-dropdown">
                <a href="#" class="${prefix}nav-link">Who We Are
                    <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <div class="${prefix}dropdown-menu">
                    <a href="about.html">About Us</a>
                    <a href="school-background.html">School Background</a>
                    <a href="teachers.html">Meet Our Staff</a>
                    <a href="testimonials.html">Testimonials</a>
                    <a href="gallery.html">School Gallery</a>
                </div>
            </div>
            <div class="${prefix}nav-dropdown">
                <a href="#" class="${prefix}nav-link">School Life
                    <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <div class="${prefix}dropdown-menu">
                    <a href="events.html">Events</a>
                    <a href="clubs.html">Clubs</a>
                    <a href="facilities.html">School Facilities</a>
                </div>
            </div>
            <a href="contact.html" class="${prefix}nav-link">Contact</a>
        `;
    },
    
    renderDefault() {
        const headerPlaceholder = document.getElementById('header-placeholder');
        if (headerPlaceholder) {
            const defaultData = {
                branding: { school_name: 'Excellence Academy', logo: '' },
                contactInfo: { email: 'info@sjs.com', phone: '0703574113' },
                navigation: [],
                navigationChildren: {}
            };
            this.render(defaultData);
            this.init();
        }
    },
    
    init() {
        if (typeof window.initHeaderChrome === 'function') {
            window.initHeaderChrome();
        }
    },
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Auto-load on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => HeaderLoader.load());
} else {
    HeaderLoader.load();
}
