# Excellence Academy - PHP/MySQL Version

This is the PHP/MySQL version of the Excellence Academy School Management System, converted from React/Node.js/PostgreSQL.

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript (AJAX/JSON)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ (password: root)
- **Email**: SMTP (cPanel compatible)
- **Server**: Apache with mod_rewrite (cPanel compatible)

## Setup Instructions

### 1. Database Setup

1. **Create Database**:
   ```sql
   mysql -u root -proot
   source database/schema.sql
   ```

   Or import `database/schema.sql` through phpMyAdmin in cPanel.

2. **Verify Database**:
   - Database name: `excellence_academy`
   - Username: `root`
   - Password: `root`
   - Host: `localhost`

### 2. Configuration

1. **Edit Database Configuration** (`config/database.php`):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'root');
   define('DB_NAME', 'excellence_academy');
   ```

2. **Configure SMTP** (`config/config.php`):
   ```php
   define('SMTP_HOST', 'localhost'); // Usually localhost for cPanel
   define('SMTP_PORT', 587);
   define('SMTP_SECURE', false); // true for SSL (port 465)
   define('SMTP_USER', 'your-email@yourdomain.com');
   define('SMTP_PASS', 'your-email-password');
   define('SMTP_FROM_EMAIL', 'your-email@yourdomain.com');
   define('SMTP_FROM_NAME', 'Excellence Academy');
   ```

### 3. File Permissions

Set proper permissions for uploads directory:
```bash
chmod 755 uploads/
chmod 755 uploads/*
```

### 4. Upload to cPanel

1. Upload all files to your cPanel public_html directory (or subdirectory)
2. Ensure PHP version is 7.4 or higher
3. Make sure mod_rewrite is enabled (for clean URLs)

### 5. Default Login Credentials

- **Username**: `admin`
- **Email**: `admin@excellenceacademy.com`
- **Password**: `admin123`

**Important**: Change the default password after first login!

## Directory Structure

```
/
├── api/              # API endpoints
│   ├── auth/        # Authentication endpoints
│   ├── forms/       # Form submission endpoints
│   └── content/     # Content management endpoints
├── config/          # Configuration files
│   ├── config.php   # Main configuration
│   └── database.php # Database connection
├── includes/        # PHP includes
│   ├── auth.php     # Authentication system
│   ├── emailService.php # Email service
│   ├── header.php   # Header component
│   └── footer.php   # Footer component
├── css/             # Stylesheets
│   └── style.css    # Main stylesheet
├── js/              # JavaScript files
│   └── main.js      # Main JavaScript
├── uploads/         # Uploaded files (images, documents)
├── database/        # Database files
│   └── schema.sql   # MySQL schema
├── index.php        # Homepage
├── login.php        # Login page
├── contact.php      # Contact page
├── admin.php        # Admin panel
└── logout.php       # Logout handler
```

## Features

### Public Pages
- Homepage with hero, about, teachers, activities, testimonials
- About page
- Teachers page
- Activities page
- Facilities page
- News & Announcements
- Contact page with form
- Gallery
- Sponsorship pages
- Parent Portal

### Admin Features
- Content management (hero, about, contact, etc.)
- Teacher management
- Activity management
- News & Announcements management
- Form submissions viewer
- Media library
- User management

### Forms
- Contact form (with email notifications)
- Sponsorship application
- Child sponsorship request
- Newsletter subscription
- Parent registration

## API Endpoints

All API endpoints are under `/api/`:

- `GET /api/health` - Health check
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/verify` - Verify session
- `POST /api/forms/contact` - Contact form submission
- `POST /api/forms/sponsorship-application` - Sponsorship application
- `POST /api/forms/child-sponsorship` - Child sponsorship
- `POST /api/forms/newsletter-subscription` - Newsletter subscription
- `POST /api/forms/parent-registration` - Parent registration
- `GET /api/content/{type}` - Get content (hero-content, about-content, etc.)
- `POST /api/content/{type}` - Create content
- `PUT /api/content/{type}` - Update content

## Email Configuration

The system uses PHP's `mail()` function by default. For better email delivery, you can:

1. **Use cPanel Email**: Configure SMTP settings in `config/config.php`
2. **Install PHPMailer** (optional): For advanced SMTP features
   ```bash
   composer require phpmailer/phpmailer
   ```

## Security Notes

1. **Change Default Password**: Immediately change the admin password after setup
2. **File Permissions**: Ensure uploads directory is writable but not executable
3. **Database Password**: Change from default 'root' password in production
4. **Session Security**: Sessions are configured with httpOnly cookies
5. **Input Validation**: All forms validate input on both client and server side

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `excellence_academy` exists

### Email Not Sending
- Check SMTP configuration in `config/config.php`
- Verify cPanel email account credentials
- Check server logs for email errors

### Upload Issues
- Check `uploads/` directory permissions (755)
- Verify PHP upload limits in php.ini
- Check file size limits

### Session Issues
- Ensure sessions directory is writable
- Check session configuration in `config/config.php`

## Development

For local development:
1. Install XAMPP/WAMP/MAMP
2. Place files in htdocs/www directory
3. Import database schema
4. Configure database connection
5. Access at `http://localhost/your-folder/`

## Support

For issues or questions, check:
- Database logs
- PHP error logs
- Apache error logs
- Browser console for JavaScript errors

## License

MIT License - See LICENSE file for details
