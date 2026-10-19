<?php
/**
 * Footer Component
 */
$db = getDB();
$footerContent = $db->fetchOne("SELECT * FROM footer_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$socialMedia = $db->fetchAll("SELECT * FROM social_media WHERE is_active = 1 ORDER BY created_at DESC");
$branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$contactInfo = $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");

$socialLinks = [];
$quickLinks = [];

if ($footerContent) {
    if (!empty($footerContent['social_links'])) {
        $socialLinks = json_decode($footerContent['social_links'], true) ?: [];
    }
    if (!empty($footerContent['quick_links'])) {
        $quickLinks = json_decode($footerContent['quick_links'], true) ?: [];
    }
}

$schoolName = $branding['school_name'] ?? 'Excellence Academy';
$address = $contactInfo['address'] ?? '123 Education Street, Learning City';
$phone = $contactInfo['phone'] ?? '0703574113';
$email = $contactInfo['email'] ?? 'info@sjs.com';

// Check if we're on the homepage
$currentPage = basename($_SERVER['PHP_SELF']);
$isHomepage = ($currentPage === 'index.php' || $currentPage === 'index.html' || $currentPage === '');
?>
<footer class="site-footer">
    <div class="snowflakes-footer"></div>
    <div class="footer-content">
        <div class="container">
            <div class="footer-branding">
                <h2 class="footer-school-name"><?php echo htmlspecialchars($schoolName); ?></h2>
            </div>
            <div class="footer-grid">
                <!-- Information Column -->
                <div class="footer-column">
                    <h3>Information</h3>
                    <ul class="footer-links">
                        <li><strong><?php echo htmlspecialchars($schoolName); ?></strong></li>
                        <li>
                            <span style="margin-right: 8px;">📍</span>
                            <?php echo htmlspecialchars($address); ?>
                        </li>
                        <li>
                            <span style="margin-right: 8px;">📞</span>
                            <?php echo htmlspecialchars($phone); ?>
                        </li>
                        <li>
                            <span style="margin-right: 8px;">📧</span>
                            <?php echo htmlspecialchars($email); ?>
                        </li>
                    </ul>
                </div>
                
                <!-- Hours of Operation Column -->
                <div class="footer-column">
                    <h4>Hours of Operation</h4>
                    <div style="margin-bottom: 20px;">
                        <p style="font-weight: 600; margin-bottom: 8px; color: var(--white);">Students:</p>
                        <ul class="footer-links" style="margin-left: 0;">
                            <li>Monday - Friday: 7:00 AM – 5:00 PM</li>
                            <li>Saturday: 8:00 AM – 1:00 PM</li>
                            <li>Sunday: Closed</li>
                            <li>Visitation Days: 12:00 PM to 5:00 PM</li>
                        </ul>
                    </div>
                    <div>
                        <p style="font-weight: 600; margin-bottom: 8px; color: var(--white);">Staff:</p>
                        <ul class="footer-links" style="margin-left: 0;">
                            <li>Monday - Friday: 8:00 AM – 5:00 PM</li>
                        </ul>
                    </div>
                </div>
                
                <!-- More to Discover Column -->
                <div class="footer-column">
                    <h4>More to Discover</h4>
                    <ul class="footer-links">
                        <li><a href="school-background.php">School Background</a></li>
                        <li><a href="curriculum.php">Curriculum</a></li>
                        <li><a href="facilities.php">School Facilities</a></li>
                        <li><a href="sponsorship.php">Support Our Work</a></li>
                    </ul>
                </div>
                
                <!-- Quick Links Column -->
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="clubs.php">Clubs</a></li>
                        <li><a href="events.php">Events</a></li>
                    </ul>
                </div>
                
                <!-- Social Media Column -->
                <div class="footer-column">
                    <h4>Social Media</h4>
                    <ul class="footer-links">
                        <?php 
                        $youtubeLink = '';
                        if (!empty($socialMedia)) {
                            foreach ($socialMedia as $social) {
                                if (stripos($social['platform'], 'youtube') !== false || stripos($social['url'], 'youtube') !== false) {
                                    $youtubeLink = $social['url'];
                                    break;
                                }
                            }
                        }
                        if (!empty($youtubeLink)): ?>
                            <li><a href="<?php echo htmlspecialchars($youtubeLink); ?>" target="_blank" rel="noopener noreferrer">YouTube</a></li>
                        <?php else: ?>
                            <li><a href="#" target="_blank" rel="noopener noreferrer">YouTube</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Newsletter Modal -->
<div id="newsletterModal" class="newsletter-modal" style="display: none;">
    <div class="newsletter-modal-overlay"></div>
    <div class="newsletter-modal-content">
        <div class="newsletter-modal-header">
            <div class="newsletter-header-left">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <h3>Subscribe to Newsletter</h3>
            </div>
            <button class="newsletter-close" id="newsletterClose" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="newsletter-modal-body">
            <p class="newsletter-description">Stay updated with the latest news, events, and achievements from our school.</p>
            <form id="newsletterForm" class="newsletter-form">
                <div class="newsletter-form-group">
                    <label for="newsletterName">Your Name (Optional)</label>
                    <input type="text" id="newsletterName" name="name" placeholder="Enter your name">
                </div>
                <div class="newsletter-form-group">
                    <label for="newsletterEmail">Email Address <span class="required">*</span></label>
                    <input type="email" id="newsletterEmail" name="email" placeholder="Enter your email address" required>
                </div>
                <div class="newsletter-form-actions">
                    <button type="button" class="newsletter-btn-cancel" id="newsletterCancel">Cancel</button>
                    <button type="submit" class="newsletter-btn-subscribe">Subscribe Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top" aria-label="Back to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="19" x2="12" y2="5"></line>
        <polyline points="5 12 12 5 19 12"></polyline>
    </svg>
</button>

<!-- Festive Animations Script - Loads settings and starts animations when enabled -->
<script src="js/festive-animations.js"></script>
