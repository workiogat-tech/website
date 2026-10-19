/**
 * Admin Tab Loader
 * Loads admin tab content dynamically for HTML pages
 */

const AdminTabLoader = {
    /**
     * Load tab content
     */
    async loadTab(tabName) {
        try {
            // Try to load HTML version first
            const htmlResponse = await fetch(`admin-tabs/${tabName}.html`);
            if (htmlResponse.ok) {
                const html = await htmlResponse.text();
                return html;
            }
            
            // Fallback: Load data and render
            const dataResponse = await fetch(`/api/public/admin-tab-data.php?tab=${tabName}`);
            const result = await dataResponse.json();
            
            if (result.success) {
                return this.renderTab(tabName, result.data);
            }
            
            return '<div class="content-card"><p>Tab content not available</p></div>';
        } catch (error) {
            console.error('Error loading tab:', error);
            return '<div class="content-card"><p>Error loading tab content</p></div>';
        }
    },
    
    /**
     * Render tab content from data
     */
    renderTab(tabName, data) {
        // This will be implemented per tab
        return '<div class="content-card"><p>Tab rendering not implemented</p></div>';
    }
};
