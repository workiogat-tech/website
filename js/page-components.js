/**
 * Page Components Loader
 * Loads header and footer dynamically for HTML pages
 */

const PageComponents = {
    /**
     * Load header component
     */
    async loadHeader() {
        try {
            const response = await fetch('/api/public/header-data.php');
            const result = await response.json();
            if (result.success) {
                const headerHTML = this.renderHeader(result.data);
                const headerPlaceholder = document.getElementById('header-placeholder');
                if (headerPlaceholder) {
                    headerPlaceholder.innerHTML = headerHTML;
                    // Re-initialize any header scripts
                    this.initHeader();
                }
            }
        } catch (error) {
            console.error('Error loading header:', error);
        }
    },
    
    /**
     * Load footer component
     */
    async loadFooter() {
        try {
            const response = await fetch('/api/public/footer-data.php');
            const result = await response.json();
            if (result.success) {
                const footerHTML = this.renderFooter(result.data);
                const footerPlaceholder = document.getElementById('footer-placeholder');
                if (footerPlaceholder) {
                    footerPlaceholder.innerHTML = footerHTML;
                }
            }
        } catch (error) {
            console.error('Error loading footer:', error);
        }
    },
    
    /**
     * Render header HTML
     */
    renderHeader(data) {
        // This will be populated with actual header HTML structure
        // For now, return a placeholder
        return `<header class="site-header">
            <div class="header-top">
                <div class="container">
                    <div class="header-top-content">
                        <div class="contact-info">
                            <a href="mailto:${data.contactInfo?.email || ''}" class="contact-item">
                                <span>${data.contactInfo?.email || ''}</span>
                            </a>
                            <a href="tel:${data.contactInfo?.phone || ''}" class="contact-item">
                                <span>${data.contactInfo?.phone || ''}</span>
                            </a>
                        </div>
                        <nav class="top-nav">${this.renderNavigation(data.navigation || [])}</nav>
                    </div>
                </div>
            </div>
            <!-- Main header content will be loaded here -->
        </header>`;
    },
    
    /**
     * Render navigation
     */
    renderNavigation(items) {
        return items.map(item => 
            `<a href="${item.url || '#'}" class="top-nav-link">${item.label || ''}</a>`
        ).join('');
    },
    
    /**
     * Render footer HTML
     */
    renderFooter(data) {
        return `<footer class="site-footer">
            <div class="footer-content">
                <div class="container">
                    <div class="footer-branding">
                        <h2>${data.branding?.school_name || 'School Name'}</h2>
                    </div>
                    <!-- Footer content will be loaded here -->
                </div>
            </div>
        </footer>`;
    },
    
    /**
     * Initialize header functionality
     */
    initHeader() {
        // Initialize mobile menu, dropdowns, etc.
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => {
                document.body.classList.toggle('mobile-menu-open');
            });
        }
    },
    
    /**
     * Load all page components
     */
    async loadAll() {
        await Promise.all([
            this.loadHeader(),
            this.loadFooter()
        ]);
    }
};

// Auto-load on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        PageComponents.loadAll();
    });
} else {
    PageComponents.loadAll();
}
