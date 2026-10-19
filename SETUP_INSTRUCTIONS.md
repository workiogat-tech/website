# Step-by-Step Setup Instructions

## Prerequisites

1. **XAMPP** (includes PHP, MySQL, Apache) - Download from https://www.apachefriends.org/
   OR
2. **WAMP** (Windows) - Download from https://www.wampserver.com/
   OR
3. **PHP** installed separately with MySQL

## Step 1: Install XAMPP (Recommended for Windows)

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP (default location: `C:\xampp`)
3. Start XAMPP Control Panel
4. Start **Apache** and **MySQL** services

## Step 2: Copy Your Project

1. Copy your project folder to: `C:\xampp\htdocs\`
2. Rename it to something simple like `school` or `excellence-academy`
3. Your project path should be: `C:\xampp\htdocs\school\`

## Step 3: Set Up Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "New" to create a database
3. Database name: `excellence_academy`
4. Click "Create"
5. Click on "Import" tab
6. Click "Choose File" and select: `database/schema.sql`
7. Click "Go" to import

**OR use command line:**
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS excellence_academy;"
.\mysql.exe -u root excellence_academy < C:\xampp\htdocs\school\database\schema.sql
```

## Step 4: Configure Database Connection

1. Open: `config/database.php`
2. Verify these settings (should already be correct for XAMPP):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default is empty
define('DB_NAME', 'excellence_academy');
```

## Step 5: Configure Email (Optional)

1. Open: `config/config.php`
2. Update SMTP settings if you want to test email functionality

## Step 6: Access Your Website

1. Open your web browser
2. Go to: **http://localhost/school/**
   (Replace `school` with your folder name)

## Step 7: Login

- **URL**: http://localhost/school/login.php
- **Username/Email**: `admin`
- **Password**: `admin123`

## Alternative: Using PHP Built-in Server (No XAMPP)

If you have PHP installed separately:

```powershell
# Navigate to your project folder
cd C:\Users\School01\Desktop\2026sjs

# Start PHP server
php -S localhost:8000
```

Then access: **http://localhost:8000**

**Note**: You'll still need MySQL running separately for this method.

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `excellence_academy` exists

### Page Not Found (404)
- Check your folder name in the URL
- Make sure Apache is running
- Check file permissions

### PHP Errors
- Check PHP error logs in XAMPP: `C:\xampp\php\logs\php_error_log`
- Enable error display in `config/config.php` (for development)

## Quick Start Commands

### Using XAMPP:
1. Start XAMPP Control Panel
2. Start Apache and MySQL
3. Open browser: http://localhost/school/

### Using PHP Built-in Server:
```powershell
cd C:\Users\School01\Desktop\2026sjs
php -S localhost:8000
```
Then open: http://localhost:8000
