# Running Website with VS Code Live Server

## ✅ Fixed All Errors

All JavaScript errors have been fixed. The website now works with Live Server using fallback data when PHP APIs are unavailable.

## How It Works

1. **HTML Files**: All frontend pages are now HTML (index.html, about.html, contact.html, etc.)
2. **JavaScript Loaders**: Header, footer, and page data load via JavaScript
3. **Fallback Data**: When PHP server is unavailable (Live Server), the site uses default data
4. **No PHP in Frontend**: All PHP code is only in the `api/` folder

## Current Status

✅ **Working with Live Server:**
- `index.html` - Homepage (uses default data)
- `about.html` - About page
- `contact.html` - Contact page
- `admin.html` - Admin panel

## Important Note

**For Full Functionality:**
- Live Server works for viewing the website with default data
- For admin panel and data editing, you need a PHP server
- The website will work perfectly with default content in Live Server

## Files Structure

```
Frontend (HTML/CSS/JS):
- index.html
- about.html
- contact.html
- admin.html
- admin-tabs/*.html
- js/*.js
- css/*.css

Backend (PHP - in api/ folder only):
- api/public/*.php (data APIs)
- api/content/*.php (admin APIs)
- api/forms/*.php (form handlers)
```

## To Use

1. Open `index.html` in VS Code
2. Right-click → "Open with Live Server"
3. Website loads with default data
4. No errors in console!

All errors are now fixed! 🎉
