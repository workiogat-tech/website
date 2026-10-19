<?php
/**
 * Setup Navigation Items Table
 * Creates the navigation_items table if it doesn't exist
 */
require_once __DIR__ . '/config/config.php';

$db = getDB();

try {
    // Check if table exists
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'navigation_items'");
    
    if (!$tableExists) {
        // Create navigation_items table
        $db->query("
            CREATE TABLE IF NOT EXISTS navigation_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                label VARCHAR(200) NOT NULL,
                url VARCHAR(500),
                parent_id INT NULL,
                order_index INT DEFAULT 0,
                icon VARCHAR(100),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order (order_index),
                INDEX idx_parent (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ Navigation items table created successfully!\n";
    } else {
        echo "✅ Navigation items table already exists.\n";
    }
    
    // Check if footer_quick_links table exists
    $footerLinksExists = $db->fetchOne("SHOW TABLES LIKE 'footer_quick_links'");
    if (!$footerLinksExists) {
        $db->query("
            CREATE TABLE IF NOT EXISTS footer_quick_links (
                id INT AUTO_INCREMENT PRIMARY KEY,
                label VARCHAR(200) NOT NULL,
                url VARCHAR(500),
                section VARCHAR(100) DEFAULT 'quick-links',
                order_index INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order (order_index),
                INDEX idx_section (section)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ Footer quick links table created successfully!\n";
    } else {
        echo "✅ Footer quick links table already exists.\n";
    }
    
    // Check if footer_hours table exists
    $footerHoursExists = $db->fetchOne("SHOW TABLES LIKE 'footer_hours'");
    if (!$footerHoursExists) {
        $db->query("
            CREATE TABLE IF NOT EXISTS footer_hours (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category VARCHAR(100) NOT NULL,
                day_range VARCHAR(200),
                hours VARCHAR(200),
                order_index INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order (order_index)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ Footer hours table created successfully!\n";
    } else {
        echo "✅ Footer hours table already exists.\n";
    }
    
    echo "\n✅ All navigation and footer tables are ready!\n";
    echo "You can now use the Navigation Menu and Footer Content tabs in the admin panel.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
