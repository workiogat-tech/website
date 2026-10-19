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
                school_name: 'Excellence Academy',
                favicon: ''
            },
            heroContent: {
                headline: 'Welcome to Excellence Academy',
                subheadline: 'Empowering minds, shaping futures',
                description: 'We focus on academic excellence, moral values, and holistic development for every child.',
                slides: [],
                slide_mode: 'marquee',
                headline_color: '#ffffff',
                subheadline_color: '#ffffff'
            },
            aboutContent: {
                title: 'About Us',
                description: 'Excellence Academy is committed to providing quality education.',
                image: ''
            },
            contactInfo: {
                email: 'info@sjs.com',
                phone: '0703574113',
                address: '123 Education Street'
            },
            academicsContent: {
                title: 'Academics',
                description: 'SJS Academy is a dynamic and growing co-educational institution.',
                student_count: '2,524',
                success_rate: '98%',
                curriculum_link: 'curriculum.html',
                button_text: 'Learn More About Our Curriculum',
                image: ''
            },
            threeColumnSections: [
                {
                    title: 'School Life',
                    description: 'SJS is a day and boarding school where learners engage in a variety of co-curricular events and clubs.',
                    image: ''
                },
                {
                    title: 'Health',
                    description: 'SJS is a Church of Uganda-founded institution operating in partnership with the Government\'s USE Program.',
                    image: ''
                },
                {
                    title: 'Sports',
                    description: 'SJS promotes teamwork and community engagement through inter-school and inter-house sports competitions.',
                    image: ''
                }
            ],
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
                return { school_name: 'Excellence Academy', favicon: '' };
            }
            
            const result = JSON.parse(text);
            return result.data || result;
        } catch (error) {
            console.error('Error loading branding:', error);
            return { school_name: 'Excellence Academy', favicon: '' };
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
                return { email: 'info@sjs.com', phone: '0703574113', address: '123 Education Street' };
            }
            
            const result = JSON.parse(text);
            return result.data || result;
        } catch (error) {
            console.error('Error loading contact info:', error);
            return { email: 'info@sjs.com', phone: '0703574113', address: '123 Education Street' };
        }
    }
};

// Export for use in HTML pages
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DataLoader;
}
