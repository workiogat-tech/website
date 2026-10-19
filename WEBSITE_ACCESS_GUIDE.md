# Website Access Guide - Excellence Academy

## ✅ FIXES APPLIED

I've fixed the following issues:

1. **Fixed CSS and JavaScript paths** - Changed from absolute paths (`/css/style.css`) to relative paths (`css/style.css`)
2. **Fixed navigation links** - Updated all links to use relative paths
3. **Updated .htaccess** - Added PHP processing directives to ensure PHP files are executed
4. **Created test file** - Added `test-website.php` to verify PHP is working

## 🌐 CORRECT URLS TO ACCESS YOUR WEBSITE

### Main Website URLs:

1. **Homepage (Main Entry Point):**
   ```
   http://localhost/2026sjs/index.php
   ```
   OR simply:
   ```
   http://localhost/2026sjs/
   ```

2. **Test PHP Processing:**
   ```
   http://localhost/2026sjs/test-website.php
   ```
   This will show you if PHP is working correctly.

3. **Other Pages:**
   - Login: `http://localhost/2026sjs/login.php`
   - Contact: `http://localhost/2026sjs/contact.php`
   - Admin: `http://localhost/2026sjs/admin.php`

## ⚠️ IMPORTANT: If You Still See HTML Code

If you're still seeing HTML source code instead of the rendered website, follow these steps:

### Step 1: Check XAMPP is Running
1. Open **XAMPP Control Panel**
2. Make sure **Apache** shows "Running" (green status)
3. If not, click **Start** next to Apache

### Step 2: Restart Apache
1. In XAMPP Control Panel, click **Stop** for Apache
2. Wait 5 seconds
3. Click **Start** for Apache
4. Wait until it shows "Running"

### Step 3: Clear Browser Cache
1. Press **Ctrl + F5** (hard refresh)
2. Or clear your browser cache completely

### Step 4: Verify PHP is Processing
1. Go to: `http://localhost/2026sjs/test-website.php`
2. If you see a green "SUCCESS!" message → PHP is working ✅
3. If you see PHP code → PHP is NOT processing ❌

### Step 5: Check Apache PHP Configuration (If PHP still not working)

1. In XAMPP Control Panel, click **Config** next to Apache
2. Select **httpd.conf**
3. Make sure this line exists (uncommented):
   ```apache
   LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
   ```
   (The version number might be different - php7, php8, etc.)

4. Make sure this section exists:
   ```apache
   <FilesMatch \.php$>
       SetHandler application/x-httpd-php
   </FilesMatch>
   ```

5. Save the file and restart Apache

## 📁 File Location

Your website files are located at:
```
C:\xampp\htdocs\2026sjs\
```

Make sure all your files are in this location, NOT on your Desktop or other folders.

## 🎯 Quick Test Checklist

- [ ] Apache is running in XAMPP
- [ ] Using URL: `http://localhost/2026sjs/index.php`
- [ ] `test-website.php` shows success message
- [ ] Browser cache cleared (Ctrl + F5)
- [ ] Files are in `C:\xampp\htdocs\2026sjs\`

## 🆘 Still Having Issues?

If the website still doesn't display:

1. **Check the test file first:**
   - Go to: `http://localhost/2026sjs/test-website.php`
   - If this shows code instead of a styled page, PHP is not processing

2. **Check Apache error logs:**
   - In XAMPP, click **Logs** next to Apache
   - Look for any error messages

3. **Verify file permissions:**
   - Make sure files are readable (not locked by another program)

4. **Try a different browser:**
   - Sometimes browser extensions can interfere

## 📝 Summary

**The correct URL to access your website is:**
```
http://localhost/2026sjs/index.php
```

Or simply:
```
http://localhost/2026sjs/
```

Make sure Apache is running in XAMPP before accessing the URL!
