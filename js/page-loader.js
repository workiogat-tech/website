/**
 * Page Loader - Generic page data loader for HTML pages
 */

const PageLoader = {
    /**
     * Load page-specific data
     */
    async loadPageData(pageName) {
        try {
            const response = await fetch(`/api/public/${pageName}-data.php`);
            const result = await response.json();
            if (result.success) {
                return result.data;
            }
            return null;
        } catch (error) {
            console.error(`Error loading ${pageName} data:`, error);
            return null;
        }
    },
    
    /**
     * Render page content
     */
    renderPage(pageName, data) {
        const container = document.getElementById('page-content');
        if (!container) return;
        
        // This will be customized per page
        container.innerHTML = '<p>Page content loading...</p>';
    }
};
