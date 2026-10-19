# Complete Admin Panel Guide

## Overview
The admin panel now provides comprehensive control over **every aspect** of the website. All text, icons, sections, pages, and images can be edited from the admin panel, and changes save to the database and appear immediately on the website.

## Admin Panel Organization

### Core Content Management
1. **Dashboard** (`?tab=dashboard`) - Overview and statistics
2. **Branding** (`?tab=branding`) - School name, logo, favicon, colors
3. **Hero Section** (`?tab=hero`) - Homepage hero slider, headlines, buttons
4. **About Section** (`?tab=about`) - About page content
5. **Navigation Menu** (`?tab=navigation`) - **NEW!** Header navigation menu items
6. **Footer Content** (`?tab=footer`) - Footer links, hours, copyright

### Page Content Management
7. **Curriculum** (`?tab=curriculum`) - Curriculum page content
8. **Activities** (`?tab=activities`) - Activities/events management
9. **Teachers** (`?tab=teachers`) - Staff management
10. **Facilities** (`?tab=facilities`) - Facilities management
11. **Testimonials** (`?tab=testimonials`) - Testimonials management
12. **Gallery** (`?tab=gallery`) - Gallery settings
13. **News** (`?tab=news`) - News articles
14. **Announcements** (`?tab=announcements`) - Announcements
15. **Contact Info** (`?tab=contact`) - Contact details

### Special Pages
16. **Admission** (`?tab=admission`) - Admission page content
17. **Sponsorship** (`?tab=sponsorship`) - Sponsorship page
18. **Give Back** (`?tab=giveback`) - Give Back page
19. **Clubs** (`?tab=clubs`) - Clubs page
20. **School Background** (`?tab=background`) - School background page
21. **People Behind School** (`?tab=people-behind-school`) - People page
22. **Parents Portal** (`?tab=parents`) - Parents portal page

### Design & Customization
23. **Homepage Sections** (`?tab=homepage-sections`) - All homepage sections
24. **Home Sections** (`?tab=home-sections`) - Why Choose section
25. **Button Customization** (`?tab=button-customization`) - Button styles
26. **Website Colors** (`?tab=colors`) - Global color scheme
27. **Social Media** (`?tab=social-media`) - Social media links
28. **Map Settings** (`?tab=map`) - Google Maps configuration
29. **Festive Animations** (`?tab=festive-animations`) - Animation settings

### System Management
30. **Media Library** (`?tab=media`) - Image/file uploads and management
31. **User Management** (`?tab=users`) - Admin users
32. **Settings** (`?tab=settings`) - System settings
33. **All Updates Settings** (`?tab=all-updates-settings`) - News/announcements header

## Key Features

### ✅ Complete Content Control
- **Every text** on the website can be edited
- **Every icon** can be customized
- **Every section** has admin controls
- **Every page** has dedicated management
- **All images** upload, save, and display correctly

### ✅ Navigation Menu Management
- **NEW!** Full control over header navigation
- Add/edit/delete menu items
- Create dropdown menus with sub-items
- Reorder items with order index
- Add icons to menu items
- Changes appear immediately on website

### ✅ Enhanced Footer Management
- Copyright text
- Quick links (add/edit/delete)
- Hours of operation (add/edit/delete)
- Social media links (managed separately)
- All organized in tabs

### ✅ Image Management
- Upload images through Media Library
- Images save to `/uploads/` directory
- Database tracks all images
- Images display correctly on website
- Media Library modal for selecting images
- Image previews in admin panel

### ✅ Real-time Updates
- All changes save to database immediately
- Changes appear on website immediately (no cache)
- Auto-save functionality on forms
- Visual feedback with toast notifications
- Success/error messages

## Database Tables

### Navigation
- `navigation_items` - Header menu items (parent/child structure)

### Footer
- `footer_content` - Footer copyright and JSON links
- `footer_quick_links` - Footer quick links
- `footer_hours` - Footer hours of operation

### Content Tables
- `branding` - School branding
- `hero_content` - Hero section
- `about_content` - About section
- `curriculum_content` - Curriculum page
- `contact_content` - Contact info
- `teachers` - Teachers/staff
- `facilities` - Facilities
- `activities` - Activities/events
- `testimonials` - Testimonials
- `news` - News articles
- `announcements` - Announcements
- `gallery_settings` - Gallery page header
- `teachers_settings` - Teachers page header
- `facilities_settings` - Facilities page header
- `testimonials_settings` - Testimonials page header
- `news_settings` - News page header
- `announcements_settings` - Announcements page header
- `all_updates_settings` - All updates page header

### Media
- `media` - All uploaded files
- `media_sections` - Media organization sections

### Special Pages
- `admission_content` - Admission page
- `sponsorship_content` - Sponsorship page
- `giveback_content` - Give Back page
- `clubs_content` - Clubs page
- `school_background_content` - School background
- `people_behind_school_content` - People page
- `parents_portal_content` - Parents portal

### Homepage Sections
- `school_life_items` - School Life section
- `academics_content` - Academics section
- `sports_items` - Sports items
- `sports_content` - Sports section
- `three_column_sections` - Three column sections
- `custom_section_content` - Custom section
- `vision_mission_content` - Vision/Mission
- `core_values_items` - Core values
- `why_choose_content` - Why Choose header
- `why_choose_points` - Why Choose points
- `join_section_content` - Join section

### Design
- `button_settings` - Button customization
- `website_colors` - Website colors
- `social_media` - Social media links
- `map_settings` - Map configuration
- `festive_animations_settings` - Animations

## How to Use

### 1. Access Admin Panel
- Go to `admin.php`
- Login with admin credentials
- Navigate using sidebar menu

### 2. Edit Navigation Menu
- Click "Navigation Menu" in sidebar
- Click "Add Menu Item" to create new items
- Click "Edit" to modify existing items
- Click "Add Sub-item" to create dropdown menus
- Changes appear immediately on website header

### 3. Edit Footer
- Click "Footer Content" in sidebar
- Use tabs: Basic Content, Quick Links, Hours, Social Media
- Add/edit/delete links and hours
- Changes appear immediately on website footer

### 4. Upload Images
- Go to "Media Library" tab
- Create sections to organize images
- Upload images (JPEG, PNG, GIF, WEBP)
- Images are available for selection in all admin forms
- Click "Select from Media Library" button in image fields

### 5. Edit Page Content
- Navigate to any page tab (e.g., "About Section", "Teachers")
- Fill in forms
- Click "Save Changes"
- Changes appear immediately on website

### 6. Auto-Save
- Most forms have auto-save enabled
- Changes save automatically after 2 seconds of inactivity
- Visual feedback shows save status

## API Endpoints

All admin forms connect to API endpoints:
- `api/content/navigation.php` - Navigation menu CRUD
- `api/content/footer.php` - Footer content
- `api/content/footer-links.php` - Footer quick links
- `api/content/footer-hours.php` - Footer hours
- `api/content/hero-content.php` - Hero section
- `api/content/about.php` - About section
- `api/content/contact.php` - Contact info
- `api/content/branding.php` - Branding
- `api/content/teachers.php` - Teachers
- `api/content/activities.php` - Activities
- `api/content/facilities.php` - Facilities
- `api/content/testimonials.php` - Testimonials
- `api/content/news.php` - News
- `api/content/announcements.php` - Announcements
- `api/media/upload.php` - Image uploads
- `api/media/list.php` - List all media

## Verification Checklist

✅ Navigation menu editable from admin panel
✅ Footer fully editable (links, hours, content)
✅ All page content editable
✅ All images upload and display correctly
✅ All changes save to database
✅ Changes appear immediately on website
✅ All admin tabs have proper forms
✅ All forms connect to API endpoints
✅ Image selection from Media Library works
✅ Auto-save functionality works
✅ Toast notifications show success/error

## Notes

- If navigation_items table is empty, website shows default menu
- If footer_quick_links table is empty, footer shows default links
- All content has fallback defaults if database is empty
- Images are stored in `/uploads/` directory
- Database tracks all image paths
- All changes are versioned (old records marked inactive)

## Support

If you encounter any issues:
1. Check browser console for errors
2. Check PHP error logs
3. Verify database tables exist
4. Verify file permissions on `/uploads/` directory
5. Check API endpoint responses in Network tab
