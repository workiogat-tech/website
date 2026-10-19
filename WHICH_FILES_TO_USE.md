# Which Files to Use - HTML vs PHP

## ✅ USE THESE HTML FILES (Frontend - Works with Live Server)

All frontend pages have been converted to HTML. **Use these files**:

1. `index.html` - Homepage
2. `about.html` - About page
3. `contact.html` - Contact page
4. `login.html` - Admin login
5. `admin.html` - Admin panel
6. `activities.html` - Activities
7. `admission.html` - Admission
8. `announcements.html` - Announcements
9. `clubs.html` - Clubs
10. `curriculum.html` - Curriculum
11. `events.html` - Events
12. `facilities.html` - Facilities
13. `gallery.html` - Gallery
14. `give-back.html` - Give Back
15. `news.html` - News
16. `parents.html` - Parents Portal
17. `people-behind-school.html` - People Behind School
18. `resources.html` - Resources
19. `school-background.html` - School Background
20. `sponsorship.html` - Sponsorship
21. `teachers.html` - Teachers/Staff
22. `testimonials.html` - Testimonials

## ⚠️ IGNORE THESE PHP FILES (Backend Only - Keep for Server)

These PHP files in the root directory are **backend files** that provide data to the HTML pages. **Do NOT delete them** - they're needed for the admin panel and API:

- `index.php` - Backend version (use `index.html` instead)
- `about.php` - Backend version (use `about.html` instead)
- `contact.php` - Backend version (use `contact.html` instead)
- `login.php` - Backend version (use `login.html` instead)
- `admin.php` - Backend version (use `admin.html` instead)
- All other `*.php` files in root - Backend versions

**These PHP files are kept for:**
- PHP server compatibility
- Admin panel functionality
- API endpoints
- Database operations

## 📁 Backend Files (Keep These)

These folders contain backend code that must stay:

- `api/` - All API endpoints (needed for admin panel)
- `admin-tabs/` - Admin panel tabs (some are PHP, some HTML)
- `includes/` - PHP includes (header, footer, helpers)
- `config/` - Configuration files

## 🎯 Summary

**For Frontend (VS Code Live Server):**
- ✅ Use all `*.html` files
- ✅ Ignore `*.php` files in root (they're backend)

**For Backend (PHP Server):**
- ✅ Use `*.php` files OR `*.html` files (both work)
- ✅ HTML files will load data from PHP APIs when available
