/**
 * Static Pages Helper
 * Provides default data and utilities for HTML pages
 */

const StaticPages = {
    // Default branding data
    branding: {
        school_name: 'Excellence Academy',
        logo: '',
        favicon: ''
    },
    
    // Default contact info
    contactInfo: {
        email: 'info@sjs.com',
        phone: '0703574113',
        address: '123 Education Street, Learning City'
    },
    
    // Default navigation - EXACT match to original header.php
    navigation: [
        { label: 'Home', url: 'index.html' },
        { 
            label: 'Who We Are', 
            url: '#',
            children: [
                { label: 'About Us', url: 'about.html' },
                { label: 'School Background', url: 'school-background.html' },
                { label: 'Meet Our Staff', url: 'teachers.html' },
                { label: 'Testimonials', url: 'testimonials.html' },
                { label: 'School Gallery', url: 'gallery.html' },
                { label: 'Newsletter', url: '#', onclick: 'openNewsletterModal' }
            ]
        },
        {
            label: 'School Life',
            url: '#',
            children: [
                { label: 'Events', url: 'events.html' },
                { label: 'Clubs', url: 'clubs.html' },
                { label: 'School Facilities', url: 'facilities.html' }
            ]
        },
        {
            label: 'Support Our Work',
            url: '#',
            children: [
                { label: 'Sponsorship', url: 'sponsorship.html' },
                { label: 'Give Back', url: 'give-back.html' },
                { label: 'People Behind the School', url: 'people-behind-school.html' },
                { label: 'Parent Portal', url: 'parents.html' }
            ]
        },
        { label: 'Contact', url: 'contact.html' }
    ],
    
    // Load branding from storage
    async loadBranding() {
        try {
            if (typeof window.ClientStorage !== 'undefined') {
                const stored = await window.ClientStorage.branding.get();
                if (stored && Object.keys(stored).length > 0) {
                    this.branding = { ...this.branding, ...stored };
                }
            }
        } catch (e) {
            console.warn('Error loading branding:', e);
        }
    },
    
    // Load contact from storage
    async loadContact() {
        try {
            if (typeof window.ClientStorage !== 'undefined') {
                const stored = await window.ClientStorage.contact.get();
                if (stored && Object.keys(stored).length > 0) {
                    this.contactInfo = { ...this.contactInfo, ...stored };
                }
            }
        } catch (e) {
            console.warn('Error loading contact:', e);
        }
    },
    
    // Render header HTML
    renderHeader() {
        const branding = this.branding;
        const contact = this.contactInfo;
        
        return `
            <header class="site-header" id="mainHeader">
                <div class="header-top">
                    <div class="container">
                        <div class="header-top-content">
                            <div class="contact-info">
                                <a href="mailto:${contact.email}" class="contact-item">
                                    <span class="contact-icon">
                                        <svg class="icon-mail" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </span>
                                    <span>${contact.email}</span>
                                </a>
                                <a href="tel:${contact.phone}" class="contact-item">
                                    <span class="contact-icon">
                                        <svg class="icon-phone" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </span>
                                    <span>${contact.phone}</span>
                                </a>
                            </div>
                            <nav class="top-nav-desktop">
                                ${this.renderNav(this.navigation, false)}
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="header-main">
                    <div class="container">
                        <div class="header-content">
                            <div class="logo">
                                <a href="index.html" class="logo-link">
                                    ${branding.logo ? 
                                        `<img src="${branding.logo}" alt="${branding.school_name}" class="logo-image">` : 
                                        `<div class="logo-placeholder"><span>${branding.school_name.charAt(0)}</span></div>`
                                    }
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
                            ${this.renderNav(this.navigation, true)}
                        </nav>
                    </div>
                </div>
                ${this.renderSearchOverlay()}
            </header>
        `;
    },
    
    renderSearchOverlay() {
        return `
            <div class="search-overlay" id="searchOverlay">
                <div class="search-overlay-content">
                    <form class="search-form" id="searchForm">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search pages..." autocomplete="off">
                        <button type="submit" class="search-submit" aria-label="Search">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                        <button type="button" class="search-close" id="searchClose" aria-label="Close search">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </form>
                    <div class="search-results" id="searchResults">
                        <div class="search-placeholder">
                            <p>Start typing to search...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    renderNav(items, isMobile) {
        return items.map(item => {
            if (item.children && item.children.length > 0) {
                const childHtml = item.children.map(child => 
                    `<a href="${child.url}">${child.label}</a>`
                ).join('');
                return `
                    <div class="${isMobile ? 'nav-dropdown' : 'top-nav-dropdown'}">
                        <a href="${item.url}" class="${isMobile ? 'nav-link dropdown-trigger' : 'top-nav-link'}">
                            ${item.label}
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
                return `<a href="${item.url}" class="${isMobile ? 'nav-link' : 'top-nav-link'}">${item.label}</a>`;
            }
        }).join('');
    },
    
    // Render footer HTML
    renderFooter() {
        const branding = this.branding;
        const contact = this.contactInfo;
        
        return `
            <footer class="site-footer">
                <div class="snowflakes-footer"></div>
                <div class="footer-content">
                    <div class="container">
                        <div class="footer-branding">
                            <h2 class="footer-school-name">${branding.school_name}</h2>
                        </div>
                        <div class="footer-grid">
                            <div class="footer-column">
                                <h3>Information</h3>
                                <ul class="footer-links">
                                    <li><strong>${branding.school_name}</strong></li>
                                    <li><span style="margin-right: 8px;">📍</span>${contact.address}</li>
                                    <li><span style="margin-right: 8px;">📞</span>${contact.phone}</li>
                                    <li><span style="margin-right: 8px;">📧</span>${contact.email}</li>
                                </ul>
                            </div>
                            <div class="footer-column">
                                <h4>Hours of Operation</h4>
                                <div style="margin-bottom: 20px;">
                                    <p style="font-weight: 600; margin-bottom: 8px; color: var(--white);">Students:</p>
                                    <ul class="footer-links" style="margin-left: 0;">
                                        <li>Monday - Friday: 7:00 AM – 5:00 PM</li>
                                        <li>Saturday: 8:00 AM – 1:00 PM</li>
                                        <li>Sunday: Closed</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="footer-column">
                                <h4>Quick Links</h4>
                                <ul class="footer-links">
                                    <li><a href="about.html">About Us</a></li>
                                    <li><a href="contact.html">Contact</a></li>
                                    <li><a href="clubs.html">Clubs</a></li>
                                    <li><a href="events.html">Events</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <button id="backToTop" class="back-to-top" style="display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; border-radius: 50%; background: #800020; color: white; border: none; cursor: pointer; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                    <line x1="12" y1="19" x2="12" y2="5"></line>
                    <polyline points="5 12 12 5 19 12"></polyline>
                </svg>
            </button>
        `;
    },
    
    mergeFilled(base, extra) {
        const next = { ...base };
        Object.keys(extra || {}).forEach(key => {
            const value = extra[key];
            if (value !== undefined && value !== null && String(value).trim() !== '') {
                next[key] = value;
            }
        });
        return next;
    },

    // Load branding from localStorage
    async loadBranding() {
        try {
            // Try ClientStorage first
            if (typeof window.ClientStorage !== 'undefined') {
                const branding = await window.ClientStorage.branding.get();
                if (branding && Object.keys(branding).length > 0) {
                    this.branding = this.mergeFilled(this.branding, branding);
                }
            }
            
            // Also try MediaStorage for compatibility
            if (typeof window.MediaStorage !== 'undefined' && window.MediaStorage.loadSettings) {
                const stored = window.MediaStorage.loadSettings('branding');
                if (stored && Object.keys(stored).length > 0) {
                    this.branding = this.mergeFilled(this.branding, stored);
                }
            }
        } catch (error) {
            console.warn('Error loading branding from localStorage:', error);
        }
    },
    
    // Load contact from storage
    async loadContact() {
        try {
            if (typeof window.ClientStorage !== 'undefined') {
                const contact = await window.ClientStorage.contact.get();
                if (contact && Object.keys(contact).length > 0) {
                    this.contactInfo = this.mergeFilled(this.contactInfo, contact);
                }
            }
            
            // Also try MediaStorage for compatibility
            if (typeof window.MediaStorage !== 'undefined' && window.MediaStorage.loadSettings) {
                const stored = window.MediaStorage.loadSettings('contact');
                if (stored && Object.keys(stored).length > 0) {
                    this.contactInfo = this.mergeFilled(this.contactInfo, stored);
                }
            }
        } catch (error) {
            console.warn('Error loading contact from localStorage:', error);
        }
    },
    
    // Initialize page
    async init() {
        // Load branding and contact from localStorage first
        await this.loadBranding();
        await this.loadContact();
        
        // Load header and footer
        const headerPlaceholder = document.getElementById('header-placeholder');
        const footerPlaceholder = document.getElementById('footer-placeholder');
        
        if (headerPlaceholder) {
            headerPlaceholder.innerHTML = this.renderHeader();
            this.initHeader();
        }
        
        if (footerPlaceholder) {
            footerPlaceholder.innerHTML = this.renderFooter();
            this.initFooter();
        }
        
        // Listen for branding updates
        window.addEventListener('adminDataSaved', async (event) => {
            if (event.detail && event.detail.settingsKey === 'branding') {
                await this.loadBranding();
                // Re-render header and footer with updated branding
                if (headerPlaceholder) {
                    headerPlaceholder.innerHTML = this.renderHeader();
                    this.initHeader();
                }
                if (footerPlaceholder) {
                    footerPlaceholder.innerHTML = this.renderFooter();
                    this.initFooter();
                }
            }
        });
        
        // Also listen for storage events (cross-tab updates)
        window.addEventListener('storage', async (event) => {
            if (event.key === 'sjs_branding' || event.key === 'admin_settings_branding') {
                await this.loadBranding();
                if (headerPlaceholder) {
                    headerPlaceholder.innerHTML = this.renderHeader();
                    this.initHeader();
                }
                if (footerPlaceholder) {
                    footerPlaceholder.innerHTML = this.renderFooter();
                    this.initFooter();
                }
            }
        });
    },
    
    initHeader() {
        if (typeof window.initHeaderChrome === 'function') {
            window.initHeaderChrome();
        }
    },
    
    initFooter() {
        // Back to top button
        const backToTop = document.getElementById('backToTop');
        if (backToTop) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.style.display = 'flex';
                } else {
                    backToTop.style.display = 'none';
                }
            });
            
            backToTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    }
};

// Auto-initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => StaticPages.init());
} else {
    StaticPages.init();
}
