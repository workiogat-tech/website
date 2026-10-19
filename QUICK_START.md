# 🚀 Quick Start Guide - Run Your Website

## ⚡ Fastest Way (XAMPP)

### Step 1: Install XAMPP
1. Download: https://www.apachefriends.org/
2. Install (default location: `C:\xampp`)
3. ✅ Check Apache and MySQL during installation

### Step 2: Start Services
1. Open **XAMPP Control Panel**
2. Click **Start** button for **Apache**
3. Click **Start** button for **MySQL**
4. ✅ Both should show green "Running"

### Step 3: Copy Project
```
Copy your project folder to: C:\xampp\htdocs\
Example: C:\xampp\htdocs\2026sjs\
```

### Step 4: Setup Database
1. Open browser: **http://localhost/phpmyadmin**
2. Click **"New"** (left sidebar)
3. Database name: `excellence_academy`
4. Click **"Create"**
5. Click **"Import"** tab
6. Click **"Choose File"**
7. Select: `C:\xampp\htdocs\2026sjs\database\schema.sql`
8. Click **"Go"** at bottom
9. ✅ Wait for "Import has been successfully finished"

### Step 5: Access Website
Open browser and go to:
```
http://localhost/2026sjs/
```
(Replace `2026sjs` with your folder name)

### Step 6: Login
- Go to: **http://localhost/2026sjs/login.php**
- Email: `admin`
- Password: `admin123`

---

## 🔧 Alternative: PHP Built-in Server

If you have PHP installed separately:

### Step 1: Check PHP Installation
```powershell
php --version
```

### Step 2: Navigate to Project
```powershell
cd C:\Users\School01\Desktop\2026sjs
```

### Step 3: Start Server
```powershell
php -S localhost:8000
```

### Step 4: Access Website
Open browser: **http://localhost:8000**

**Note**: You'll need MySQL running separately for this method.

---

## 📋 Checklist

- [ ] XAMPP installed
- [ ] Apache started (green in XAMPP)
- [ ] MySQL started (green in XAMPP)
- [ ] Project copied to `C:\xampp\htdocs\`
- [ ] Database `excellence_academy` created
- [ ] Database schema imported
- [ ] Website accessible at `http://localhost/your-folder/`
- [ ] Can login with admin/admin123

---

## 🐛 Troubleshooting

### "Page Not Found" Error
- ✅ Check folder name in URL matches your folder name
- ✅ Make sure Apache is running
- ✅ Check file `index.php` exists

### "Database Connection Failed"
- ✅ Make sure MySQL is running
- ✅ Check `config/database.php` password (empty for XAMPP)
- ✅ Verify database `excellence_academy` exists

### "PHP Not Found"
- ✅ Install XAMPP (includes PHP)
- ✅ Or add PHP to Windows PATH

### Can't Import Database
- ✅ Make sure MySQL is running
- ✅ Check file path to `schema.sql`
- ✅ Try importing via command line (see below)

---

## 💻 Command Line Database Setup

If phpMyAdmin import doesn't work:

```powershell
# Navigate to MySQL bin folder
cd C:\xampp\mysql\bin

# Create database
.\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS excellence_academy;"

# Import schema (adjust path)
.\mysql.exe -u root excellence_academy < C:\xampp\htdocs\2026sjs\database\schema.sql
```

---

## 📍 Your Project URLs

After setup, access:

- **Homepage**: http://localhost/2026sjs/
- **Login**: http://localhost/2026sjs/login.php
- **Admin Panel**: http://localhost/2026sjs/admin.php
- **Contact**: http://localhost/2026sjs/contact.php

---

## ✅ Success Indicators

You'll know it's working when:
- ✅ Homepage loads with school content
- ✅ Can see header with navigation
- ✅ Can login to admin panel
- ✅ No PHP errors on pages

---

## 🆘 Still Having Issues?

1. Check XAMPP error logs:
   - Apache: `C:\xampp\apache\logs\error.log`
   - MySQL: `C:\xampp\mysql\data\*.err`

2. Check PHP errors:
   - Enable in `config/config.php`: `ini_set('display_errors', 1);`

3. Verify file permissions (Windows usually handles this automatically)
