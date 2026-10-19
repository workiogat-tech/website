/**
 * Client-Side Data Storage System
 * Replaces PHP backend with LocalStorage
 * No PHP required - works entirely in the browser
 */

// Prevent redeclaration
if (typeof window.ClientStorage === 'undefined') {
    const ClientStorage = {
    // Storage keys
    KEYS: {
        BRANDING: 'sjs_branding',
        HERO_CONTENT: 'sjs_hero_content',
        ABOUT_CONTENT: 'sjs_about_content',
        CONTACT_CONTENT: 'sjs_contact_content',
        FOOTER_CONTENT: 'sjs_footer_content',
        MEDIA: 'sjs_media',
        TEACHERS: 'sjs_teachers',
        TESTIMONIALS: 'sjs_testimonials',
        ACTIVITIES: 'sjs_activities',
        FACILITIES: 'sjs_facilities'
    },

    /**
     * Get data from storage
     */
    get(key, defaultValue = null) {
        try {
            const data = localStorage.getItem(key);
            if (data === null) {
                return defaultValue;
            }
            return JSON.parse(data);
        } catch (e) {
            console.error('Error reading from storage:', e);
            return defaultValue;
        }
    },

    /**
     * Save data to storage
     */
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Error saving to storage:', e);
            // Handle quota exceeded
            if (e.name === 'QuotaExceededError') {
                alert('Storage quota exceeded. Please clear some data or use a different browser.');
            }
            return false;
        }
    },

    /**
     * Remove data from storage
     */
    remove(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error('Error removing from storage:', e);
            return false;
        }
    },

    /**
     * Clear all app data
     */
    clearAll() {
        try {
            Object.values(this.KEYS).forEach(key => {
                localStorage.removeItem(key);
            });
            return true;
        } catch (e) {
            console.error('Error clearing storage:', e);
            return false;
        }
    },

    /**
     * Export all data as JSON
     */
    exportData() {
        const data = {};
        Object.entries(this.KEYS).forEach(([name, key]) => {
            data[name.toLowerCase()] = this.get(key);
        });
        return JSON.stringify(data, null, 2);
    },

    /**
     * Import data from JSON
     */
    importData(jsonString) {
        try {
            const data = JSON.parse(jsonString);
            Object.entries(this.KEYS).forEach(([name, key]) => {
                const keyName = name.toLowerCase();
                if (data[keyName] !== undefined) {
                    this.set(key, data[keyName]);
                }
            });
            return true;
        } catch (e) {
            console.error('Error importing data:', e);
            return false;
        }
    },

    // ========== BRANDING API ==========
    branding: {
        async get() {
            const data = ClientStorage.get(ClientStorage.KEYS.BRANDING, {
                school_name: 'SJS ACADEMY',
                tagline: 'Shaping Tomorrows Leaders Today',
                logo: '',
                favicon: '',
                primary_color: '#800020',
                secondary_color: '#6B0000',
                font_family: ''
            });
            return data;
        },

        async save(data) {
            const existing = await this.get();
            const updated = {
                ...existing,
                ...data,
                updated_at: new Date().toISOString()
            };
            ClientStorage.set(ClientStorage.KEYS.BRANDING, updated);
            
            // Also save to MediaStorage for compatibility
            if (typeof window.MediaStorage !== 'undefined' && window.MediaStorage.saveSettings) {
                window.MediaStorage.saveSettings('branding', updated);
            }
            
            return {
                success: true,
                message: 'Branding saved successfully',
                data: updated
            };
        }
    },

    // ========== HERO CONTENT API ==========
    hero: {
        async get() {
            const data = ClientStorage.get(ClientStorage.KEYS.HERO_CONTENT, {
                headline: 'Welcome to SJS',
                subheadline: 'Empowering minds, shaping futures',
                description: '',
                background_image: '',
                slides: [],
                slide_interval_ms: 5000,
                slide_direction: 'left',
                slide_mode: 'marquee',
                background_size: 'contain',
                background_position: 'center',
                image_scale: 1.0,
                headline_color: '#ffffff',
                subheadline_color: '#ffffff',
                description_color: '#ffffff',
                cta_text: '',
                cta_link: ''
            });
            return data;
        },

        async save(data) {
            const existing = await this.get();
            const updated = {
                ...existing,
                ...data,
                updated_at: new Date().toISOString()
            };
            ClientStorage.set(ClientStorage.KEYS.HERO_CONTENT, updated);
            
            // Also save to MediaStorage for compatibility
            if (typeof window.MediaStorage !== 'undefined' && window.MediaStorage.saveSettings) {
                window.MediaStorage.saveSettings('hero', updated);
            }
            
            return {
                success: true,
                message: 'Hero content saved successfully',
                data: updated
            };
        }
    },

    // ========== ABOUT CONTENT API ==========
    about: {
        async get() {
            const data = ClientStorage.get(ClientStorage.KEYS.ABOUT_CONTENT, {
                title: 'About SJS Academy',
                content: '',
                image: '',
                hero_image: '',
                about_image_2: '',
                hero_caption: 'Know Us Better',
                mission_title: 'Our Mission',
                mission_content: '',
                mission_paragraph2: '',
                core_values: [],
                statistics: []
            });
            return data;
        },

        async save(data) {
            const existing = await this.get();
            const updated = {
                ...existing,
                ...data,
                updated_at: new Date().toISOString()
            };
            ClientStorage.set(ClientStorage.KEYS.ABOUT_CONTENT, updated);
            
            // Also save to MediaStorage for compatibility
            if (typeof window.MediaStorage !== 'undefined' && window.MediaStorage.saveSettings) {
                window.MediaStorage.saveSettings('about', updated);
            }
            
            return {
                success: true,
                message: 'About content saved successfully',
                data: updated
            };
        }
    },

    // ========== CONTACT CONTENT API ==========
    contact: {
        async get() {
            const data = ClientStorage.get(ClientStorage.KEYS.CONTACT_CONTENT, {
                address: '',
                phone: '',
                email: '',
                hours: '',
                intro_text: '',
                map_embed: ''
            });
            return data;
        },

        async save(data) {
            const existing = await this.get();
            const updated = {
                ...existing,
                ...data,
                updated_at: new Date().toISOString()
            };
            ClientStorage.set(ClientStorage.KEYS.CONTACT_CONTENT, updated);
            
            // Also save to MediaStorage for compatibility
            if (typeof window.MediaStorage !== 'undefined' && window.MediaStorage.saveSettings) {
                window.MediaStorage.saveSettings('contact', updated);
            }
            
            return {
                success: true,
                message: 'Contact content saved successfully',
                data: updated
            };
        }
    }
    };

    // Make it globally available
    window.ClientStorage = ClientStorage;
}
