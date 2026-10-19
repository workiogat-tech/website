# Render Deployment Guide

This guide will help you deploy your PHP application to Render.

## Prerequisites

1. A Render account (sign up at [render.com](https://render.com))
2. Your code pushed to a Git repository (GitHub, GitLab, or Bitbucket)

## Step 1: Create a New Web Service on Render

1. Log in to your Render dashboard
2. Click **"New +"** → **"Blueprint"** (if using render.yaml) OR **"Web Service"** (for manual setup)
3. Connect your Git repository
4. Render will automatically detect the `render.yaml` file if present

## Step 2: Database Setup

⚠️ **IMPORTANT**: Render primarily supports PostgreSQL, but your application uses MySQL. You have two options:

### Option A: Use External MySQL Service (Recommended)

Use a MySQL-compatible service and set environment variables manually:

1. **Recommended Services:**
   - **PlanetScale** (serverless MySQL) - https://planetscale.com
   - **Railway** (MySQL support) - https://railway.app
   - **AWS RDS** (MySQL) - https://aws.amazon.com/rds
   - **DigitalOcean Managed Databases** - https://www.digitalocean.com/products/managed-databases

2. After creating your MySQL database, update environment variables in Render:
   - `DB_HOST` - Your MySQL host
   - `DB_PORT` - Usually 3306
   - `DB_NAME` - Your database name (e.g., `sjs`)
   - `DB_USER` - Your database username
   - `DB_PASS` - Your database password

3. **Comment out the database service in `render.yaml`** to avoid creating a PostgreSQL database

### Option B: Use PostgreSQL (Requires Schema Changes)

If you want to use Render's managed PostgreSQL:

1. The `render.yaml` will create a PostgreSQL database automatically
2. You'll need to convert your MySQL schema to PostgreSQL
3. Update your PHP code to use PostgreSQL-specific syntax

**For this guide, we'll assume you're using Option A (External MySQL).**

## Step 3: Environment Variables

The `render.yaml` file includes environment variables that will be auto-populated from the database service. You'll need to manually set:

### SMTP Configuration
Update these in the Render dashboard (Settings → Environment):

- `SMTP_HOST` - Your SMTP server (e.g., `smtp.gmail.com` for Gmail)
- `SMTP_PORT` - Port number (587 for TLS, 465 for SSL)
- `SMTP_SECURE` - `"true"` for SSL, `"false"` for TLS
- `SMTP_USER` - Your email address
- `SMTP_PASS` - Your email password
- `SMTP_FROM_EMAIL` - From email address
- `SMTP_FROM_NAME` - From name (default: "Excellence Academy")

### Optional
- `ADMIN_EMAILS` - Comma-separated list of admin emails

## Step 4: Import Database Schema

After your MySQL database is created (external service):

1. Get your database connection details from your MySQL provider
2. Import your schema:

```bash
# Using MySQL command line
mysql -h YOUR_DB_HOST -P YOUR_DB_PORT -u YOUR_DB_USER -p YOUR_DB_NAME < database/schema-sjs.sql

# Example for PlanetScale (using their CLI)
pscale connect YOUR_DB_NAME --execute "source database/schema-sjs.sql"

# Or use a MySQL client like MySQL Workbench, phpMyAdmin, DBeaver, or TablePlus
```

**Database files to import (in order):**
1. `database/schema-sjs.sql` (main schema - import this first)
2. `database/curriculum-schema.sql` (if needed)
3. `database/homepage-sections-schema.sql` (if needed)
4. `database/update-about-schema.sql` (if needed)

## Step 5: File Uploads

Render provides persistent disk storage. The uploads directory will persist between deployments.

1. Ensure the `uploads/` directory exists in your repository
2. Render will automatically handle file permissions
3. Files uploaded will persist in `/opt/render/project/src/uploads/`

## Step 6: Deploy

1. Push your code to your Git repository
2. Render will automatically detect changes and deploy
3. Monitor the deployment logs in the Render dashboard

## Step 7: Verify Deployment

1. Visit your Render URL (e.g., `https://excellence-academy.onrender.com`)
2. Test the login:
   - URL: `https://your-app.onrender.com/login.php`
   - Username/Email: `admin`
   - Password: `admin123`

## Configuration Files

### render.yaml
This file defines your infrastructure as code:
- Web service configuration
- Database service configuration
- Environment variables

### .nginx.conf (Optional)
Custom Nginx configuration for advanced routing and caching.

## Troubleshooting

### Database Connection Issues
- Verify environment variables are set correctly (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`)
- Check that your external MySQL database is running and accessible
- Ensure database name matches (`sjs`)
- Verify your MySQL host allows connections from Render's IP addresses (check your MySQL provider's firewall/network settings)
- Test connection using a MySQL client before deploying

### File Upload Issues
- Check that `uploads/` directory exists and is writable
- Verify `MAX_UPLOAD_SIZE` in `config/config.php` (50MB default)

### PHP Errors
- Check deployment logs in Render dashboard
- Verify PHP version (set in `render.yaml` as `PHP_VERSION`)
- Ensure all required PHP extensions are available

### Routing Issues
- Check `.nginx.conf` if using custom routing
- Verify `index.php` is in the root directory
- Check that mod_rewrite rules are working (Render uses Nginx, not Apache)

## Default Credentials

⚠️ **IMPORTANT**: Change these immediately after deployment!

- **Username/Email**: `admin`
- **Password**: `admin123`

## Cost Considerations

- **Starter Plan**: Free tier available (with limitations)
- **Standard Plan**: $7/month per service
- **Pro Plan**: $25/month per service

Database pricing varies by plan. Check Render's pricing page for current rates.

## Support

- Render Documentation: https://render.com/docs
- Render Community: https://community.render.com
- PHP Runtime: https://render.com/docs/php
