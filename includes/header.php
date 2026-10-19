<?php
/**
 * Header Component - React-like with smooth animations
 * Matches the React version structure
 */
$db = getDB();
$branding = null;
$contactInfo = null;

try {
    $branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $contactInfo = $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
} catch (Exception $e) {
    error_log("Header error: " . $e->getMessage());
}

require_once __DIR__ . '/auth.php';
$auth = getAuth();
$currentUser = $auth->getCurrentUser();

$email = $contactInfo['email'] ?? 'info@sjs.com';
$phone = $contactInfo['phone'] ?? '0703574113';

// Load navigation menu from database
$navItems = [];
$navChildren = [];
try {
    $allNavItems = $db->fetchAll("SELECT * FROM navigation_items WHERE is_active = 1 ORDER BY parent_id ASC, order_index ASC");
    foreach ($allNavItems as $item) {
        if (empty($item['parent_id'])) {
            $navItems[] = $item;
        } else {
            if (!isset($navChildren[$item['parent_id']])) {
                $navChildren[$item['parent_id']] = [];
            }
            $navChildren[$item['parent_id']][] = $item;
        }
    }
} catch (Exception $e) {
    // If table doesn't exist, use empty array (will show default menu)
    error_log("Navigation menu error: " . $e->getMessage());
}

// Function to render navigation menu
function renderNavMenu($items, $children, $isMobile = false) {
    $html = '';
    foreach ($items as $item) {
        $hasChildren = isset($children[$item['id']]) && !empty($children[$item['id']]);
        $url = $item['url'] ?? '#';
        $label = htmlspecialchars($item['label']);
        $icon = !empty($item['icon']) ? htmlspecialchars($item['icon']) . ' ' : '';
        
        if ($hasChildren) {
            // Dropdown menu item
            $html .= '<div class="' . ($isMobile ? 'nav-dropdown' : 'top-nav-dropdown') . '">';
            $html .= '<a href="' . htmlspecialchars($url) . '" class="' . ($isMobile ? 'nav-link dropdown-trigger' : 'top-nav-link') . '">';
            $html .= $icon . $label;
            $html .= '<svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
            $html .= '<polyline points="6 9 12 15 18 9"></polyline></svg></a>';
            $html .= '<div class="' . ($isMobile ? 'dropdown-menu' : 'top-dropdown-menu') . '">';
            foreach ($children[$item['id']] as $child) {
                $childUrl = $child['url'] ?? '#';
                $childLabel = htmlspecialchars($child['label']);
                $childIcon = !empty($child['icon']) ? htmlspecialchars($child['icon']) . ' ' : '';
                $html .= '<a href="' . htmlspecialchars($childUrl) . '">' . $childIcon . $childLabel . '</a>';
            }
            $html .= '</div></div>';
        } else {
            // Regular link
            $html .= '<a href="' . htmlspecialchars($url) . '" class="' . ($isMobile ? 'nav-link' : 'top-nav-link') . '">' . $icon . $label . '</a>';
        }
    }
    return $html;
}
?>
<header class="site-header" id="mainHeader">
    <!-- Top Bar - Contact Info and Navigation -->
    <div class="header-top">
        <div class="container">
            <div class="header-top-content">
                <!-- Contact Info - Left -->
                <div class="contact-info">
                    <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="contact-item">
                        <span class="contact-icon">
                            <svg class="icon-mail" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <span><?php echo htmlspecialchars($email); ?></span>
                    </a>
                    <a href="tel:<?php echo htmlspecialchars($phone); ?>" class="contact-item">
                        <span class="contact-icon">
                            <svg class="icon-phone" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </span>
                        <span><?php echo htmlspecialchars($phone); ?></span>
                    </a>
                </div>

                <!-- Navigation Menu - Right (Desktop) -->
                <nav class="top-nav-desktop">
                    <?php 
                    // Render navigation from database, or fallback to default
                    if (!empty($navItems)) {
                        echo renderNavMenu($navItems, $navChildren, false);
                    } else {
                        // Default menu if database is empty
                        ?>
                        <a href="index.php" class="top-nav-link">Home</a>
                        <div class="top-nav-dropdown">
                            <a href="#" class="top-nav-link">Who We Are 
                                <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </a>
                            <div class="top-dropdown-menu">
                                <a href="about.php">About Us</a>
                                <a href="school-background.php">School Background</a>
                                <a href="teachers.php">Meet Our Staff</a>
                                <a href="testimonials.php">Testimonials</a>
                                <a href="gallery.php">School Gallery</a>
                                <a href="#" class="newsletter-link" onclick="event.preventDefault(); (function(){ var modal = document.getElementById('newsletterModal'); if(modal) { modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; } else if(typeof window.openNewsletterModal === 'function') { window.openNewsletterModal(); } })(); return false;">Newsletter</a>
                            </div>
                        </div>
                        <div class="top-nav-dropdown">
                            <a href="#" class="top-nav-link">School Life 
                                <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </a>
                            <div class="top-dropdown-menu">
                                <a href="events.php">Events</a>
                                <a href="clubs.php">Clubs</a>
                                <a href="facilities.php">School Facilities</a>
                            </div>
                        </div>
                        <div class="top-nav-dropdown">
                            <a href="#" class="top-nav-link">Support Our Work 
                                <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </a>
                            <div class="top-dropdown-menu">
                                <a href="sponsorship.php">Sponsorship</a>
                                <a href="give-back.php">Give Back</a>
                                <a href="people-behind-school.php">People Behind the School</a>
                                <a href="parents.php">Parent Portal</a>
                            </div>
                        </div>
                        <a href="contact.php" class="top-nav-link">Contact</a>
                        <?php
                    }
                    ?>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Main Header - Logo and Search -->
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <!-- Logo - Left -->
                <div class="logo">
                    <a href="index.php" class="logo-link">
                        <?php if (!empty($branding['logo'])): ?>
                            <img src="<?php echo htmlspecialchars($branding['logo']); ?>" alt="<?php echo htmlspecialchars($branding['school_name'] ?? 'School Logo'); ?>" class="logo-image">
                        <?php else: ?>
                            <div class="logo-placeholder">
                                <span><?php echo htmlspecialchars(substr($branding['school_name'] ?? 'E', 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Search and Mobile Menu - Right -->
                <div class="header-actions">
                    <!-- Search Button -->
                    <button class="search-button" id="searchButton" aria-label="Search">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu">
                        <span class="menu-icon">☰</span>
                        <span class="close-icon" style="display: none;">✕</span>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation (inline expansion below header, like now folder) -->
            <nav class="main-nav mobile-nav" id="mainNav">
                <?php 
                // Render navigation from database, or fallback to default
                if (!empty($navItems)) {
                    echo renderNavMenu($navItems, $navChildren, true);
                } else {
                    // Default menu if database is empty
                    ?>
                    <a href="index.php" class="nav-link">Home</a>
                    <div class="nav-dropdown">
                        <a href="#" class="nav-link dropdown-trigger">Who We Are 
                            <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </a>
                        <div class="dropdown-menu">
                            <a href="about.php">About Us</a>
                            <a href="school-background.php">School Background</a>
                            <a href="teachers.php">Meet Our Staff</a>
                            <a href="testimonials.php">Testimonials</a>
                            <a href="gallery.php">School Gallery</a>
                            <a href="#" onclick="event.preventDefault(); if(typeof openNewsletterModal === 'function') { openNewsletterModal(); } else { alert('Newsletter feature is loading...'); }">Newsletter</a>
                        </div>
                    </div>
                    <div class="nav-dropdown">
                        <a href="#" class="nav-link dropdown-trigger">School Life 
                            <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </a>
                        <div class="dropdown-menu">
                            <a href="events.php">Events</a>
                            <a href="clubs.php">Clubs</a>
                            <a href="facilities.php">School Facilities</a>
                        </div>
                    </div>
                    <div class="nav-dropdown">
                        <a href="#" class="nav-link dropdown-trigger">Support Our Work 
                            <svg class="icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </a>
                        <div class="dropdown-menu">
                            <a href="sponsorship.php">Sponsorship</a>
                            <a href="give-back.php">Give Back</a>
                            <a href="people-behind-school.php">People Behind the School</a>
                            <a href="parents.php">Parent Portal</a>
                        </div>
                    </div>
                    <a href="contact.php" class="nav-link">Contact</a>
                    <?php
                }
                ?>
                <?php if ($auth->isLoggedIn()): ?>
                    <a href="admin.php" class="nav-link">Admin</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Search Overlay (Full Screen) -->
    <div class="search-overlay" id="searchOverlay">
        <div class="search-overlay-content">
            <form class="search-form" id="searchForm">
                <input type="text" id="searchInput" class="search-input" placeholder="Search..." autocomplete="off">
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
                    <svg class="search-placeholder-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <p>Start typing to search...</p>
                </div>
            </div>
        </div>
    </div>
</header>
