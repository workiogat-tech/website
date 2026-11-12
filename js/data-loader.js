/**
 * Data Loader - Fetches data from PHP APIs for HTML frontend
 * This allows HTML pages to work without PHP in the frontend
 */

const DataLoader = {
    baseURL: '/api/public',
    
    /**
     * Load homepage data
     */
    async loadHomepageData() {
        try {
            const response = await fetch(`${this.baseURL}/homepage-data.php`);
            const text = await response.text();
            
            // Check if we got PHP code instead of JSON
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                console.warn('PHP server not available - using default homepage data');
                return this.getDefaultHomepageData();
            }
            
            const result = JSON.parse(text);
            if (result.success) {
                return result.data;
            }
            throw new Error(result.error || 'Failed to load homepage data');
        } catch (error) {
            console.error('Error loading homepage data:', error);
            return this.getDefaultHomepageData();
        }
    },
    
    /**
     * Get default homepage data when API is unavailable
     */
    getDefaultHomepageData() {
        return {
            branding: {
                school_name: '',
                favicon: ''
            },
            heroContent: {
                headline: '',
                subheadline: '',
                description: '',
                slides: [],
                slide_mode: 'marquee',
                headline_color: '#ffffff',
                subheadline_color: '#ffffff'
            },
            aboutContent: {
                title: 'About Us',
                description: '',
                image: ''
            },
            contactInfo: {
                email: '',
                phone: '',
                address: ''
            },
            academicsContent: {
                title: '',
                description: '',
                student_count: '',
                success_rate: '',
                curriculum_link: '',
                button_text: '',
                image: ''
            },
            threeColumnSections: [],
            teachers: [],
            testimonials: [],
            activities: [],
            facilities: [],
            schoolLifeItems: [],
            sportsItems: [],
            galleryImages: []
        };
    },
    
    /**
     * Load page data (generic)
     */
    async loadPageData(pageName) {
        try {
            const response = await fetch(`${this.baseURL}/${pageName}-data.php`);
            const text = await response.text();
            
            // Check if we got PHP code instead of JSON
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                console.warn(`PHP server not available for ${pageName}`);
                return null;
            }
            
            const result = JSON.parse(text);
            if (result.success) {
                return result.data;
            }
            throw new Error(result.error || `Failed to load ${pageName} data`);
        } catch (error) {
            console.error(`Error loading ${pageName} data:`, error);
            return null;
        }
    },
    
    /**
     * Load branding data
     */
    async loadBranding() {
        try {
            const response = await fetch('/api/content/branding.php');
            const text = await response.text();
            
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                return { school_name: '', favicon: '' };
            }
            
            const result = JSON.parse(text);
            return result.data || result;
        } catch (error) {
            console.error('Error loading branding:', error);
            return { school_name: '', favicon: '' };
        }
    },
    
    /**
     * Load navigation menu
     */
    async loadNavigation() {
        try {
            const response = await fetch('/api/content/navigation.php');
            const text = await response.text();
            
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                return [];
            }
            
            const result = JSON.parse(text);
            return result.data || result.items || [];
        } catch (error) {
            console.error('Error loading navigation:', error);
            return [];
        }
    },
    
    /**
     * Load contact info
     */
    async loadContactInfo() {
        try {
            const response = await fetch('/api/content/contact.php');
            const text = await response.text();
            
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                return { email: '', phone: '', address: '' };
            }
            
            const result = JSON.parse(text);
            return result.data || result;
        } catch (error) {
            console.error('Error loading contact info:', error);
            return { email: '', phone: '', address: '' };
        }
    }
};

// Export for use in HTML pages
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DataLoader;
}
