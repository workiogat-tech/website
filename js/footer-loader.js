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
        const schoolName = branding.school_name || 'Excellence Academy';
        const address = contactInfo.address || '123 Education Street';
        const phone = contactInfo.phone || '0703574113';
        const email = contactInfo.email || 'info@sjs.com';
        
        const html = `
            <footer class="site-footer">
                <div class="snowflakes-footer"></div>
                <div class="footer-content">
                    <div class="container">
                        <div class="footer-branding">
                            <h2 class="footer-school-name">${this.escapeHtml(schoolName)}</h2>
                        </div>
                        <div class="footer-grid">
                            <div class="footer-column">
                                <h3>Information</h3>
                                <ul class="footer-links">
                                    <li><strong>${this.escapeHtml(schoolName)}</strong></li>
                                    <li><span style="margin-right: 8px;">📍</span>${this.escapeHtml(address)}</li>
                                    <li><span style="margin-right: 8px;">📞</span>${this.escapeHtml(phone)}</li>
                                    <li><span style="margin-right: 8px;">📧</span>${this.escapeHtml(email)}</li>
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
            <button id="backToTop" class="back-to-top" aria-label="Back to top">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                    <line x1="12" y1="19" x2="12" y2="5"></line>
                    <polyline points="5 12 12 5 19 12"></polyline>
                </svg>
            </button>
        `;
        
        footerPlaceholder.innerHTML = html;
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
                                <div class="footer-column">
                                    <h3>Information</h3>
                                    <ul class="footer-links">
                                        <li><strong>Excellence Academy</strong></li>
                                        <li><span style="margin-right: 8px;">📍</span>123 Education Street</li>
                                        <li><span style="margin-right: 8px;">📞</span>0703574113</li>
                                        <li><span style="margin-right: 8px;">📧</span>info@sjs.com</li>
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
                <button id="backToTop" class="back-to-top" aria-label="Back to top" style="display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; border-radius: 50%; background: #800020; color: white; border: none; cursor: pointer; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                        <line x1="12" y1="19" x2="12" y2="5"></line>
                        <polyline points="5 12 12 5 19 12"></polyline>
                    </svg>
                </button>
            `;
            this.init();
        }
    },
    
    init() {
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
