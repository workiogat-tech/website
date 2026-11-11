/**
 * Footer Loader - Loads footer dynamically for HTML pages
 */

const FooterLoader = {
    async load() {
        try {
            const response = await fetch('/api/public/footer-data.php');
            const text = await response.text();
            
            // Check if we got PHP code instead of JSON
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                console.warn('PHP server not available - using default footer');
                this.renderDefault();
                return;
            }
            
            const result = JSON.parse(text);
            if (result.success) {
                this.render(result.data);
                this.init();
            } else {
                this.renderDefault();
            }
        } catch (error) {
            console.error('Error loading footer:', error);
            this.renderDefault();
        }
    },
    
    render(data) {
        const footerPlaceholder = document.getElementById('footer-placeholder');
        if (!footerPlaceholder) return;
        
        const branding = data.branding || {};
        const contactInfo = data.contactInfo || {};
        const footerContent = data.footerContent || {};
        const schoolName = branding.school_name || 'Excellence Academy';
        const address = contactInfo.address || '123 Education Street';
        const phone = contactInfo.phone || '0703574113';
        const email = contactInfo.email || 'info@sjs.com';
        const copyright = footerContent.copyright_text
            || (`© ${schoolName}. All rights reserved.`);
        
        const html = `
            <footer class="site-footer">
                <div class="snowflakes-footer"></div>
                <div class="footer-content">
                    <div class="container">
                        <div class="footer-branding">
                            <h2 class="footer-school-name">${this.escapeHtml(schoolName)}</h2>
                        </div>
                        <div class="footer-grid">
                            <div class="footer-column footer-info">
                                <h3>Information</h3>
                                <p class="footer-name">${this.escapeHtml(schoolName)}</p>
                                <ul class="footer-links">
                                    <li>${this.escapeHtml(address)}</li>
                                    <li><a href="tel:${this.escapeHtml(phone)}">${this.escapeHtml(phone)}</a></li>
                                    <li><a href="mailto:${this.escapeHtml(email)}">${this.escapeHtml(email)}</a></li>
                                </ul>
                            </div>
                            <div class="footer-column">
                                <h4>Hours</h4>
                                <ul class="footer-links footer-hours">
                                    <li><span>Mon–Fri</span> 7 AM – 5 PM</li>
                                    <li><span>Saturday</span> 8 AM – 1 PM</li>
                                    <li><span>Sunday</span> Closed</li>
                                </ul>
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
                <div class="footer-bottom">
                    <div class="container">${this.escapeHtml(copyright)}</div>
                </div>
            </footer>
        `;
        
        footerPlaceholder.innerHTML = html;
        if (typeof window.initBackToTop === 'function') window.initBackToTop();
    },
    
    renderDefault() {
        const footerPlaceholder = document.getElementById('footer-placeholder');
        if (footerPlaceholder) {
            footerPlaceholder.innerHTML = `
                <footer class="site-footer">
                    <div class="footer-content">
                        <div class="container">
                            <div class="footer-branding">
                                <h2 class="footer-school-name">Excellence Academy</h2>
                            </div>
                            <div class="footer-grid">
                                <div class="footer-column footer-info">
                                    <h3>Information</h3>
                                    <p class="footer-name">Excellence Academy</p>
                                    <ul class="footer-links">
                                        <li>123 Education Street</li>
                                        <li><a href="tel:0703574113">0703574113</a></li>
                                        <li><a href="mailto:info@sjs.com">info@sjs.com</a></li>
                                    </ul>
                                </div>
                                <div class="footer-column">
                                    <h4>Hours</h4>
                                    <ul class="footer-links footer-hours">
                                        <li><span>Mon–Fri</span> 7 AM – 5 PM</li>
                                        <li><span>Saturday</span> 8 AM – 1 PM</li>
                                        <li><span>Sunday</span> Closed</li>
                                    </ul>
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
            `;
            this.init();
        }
    },
    
    init() {
        if (typeof window.initBackToTop === 'function') {
            window.initBackToTop();
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
    document.addEventListener('DOMContentLoaded', () => FooterLoader.load());
} else {
    FooterLoader.load();
}
