# Excellence Academy - School Management System

A school website with an admin panel. The public pages are HTML. The admin API is PHP, deployed on Vercel, with Postgres on Supabase.

## Host on Vercel with Supabase

1. In Supabase, create a project and a **public** Storage bucket named `uploads`.
2. Copy the **Transaction pooler** connection string (port 6543) from Supabase → Project Settings → Database.
3. In Vercel, import this project and set the environment variables from `.vercel.env.example`:
   - `DATABASE_URL`
   - `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_USERNAME`
   - `SUPABASE_URL`, `SUPABASE_SERVICE_ROLE_KEY`, `SUPABASE_STORAGE_BUCKET`
4. Deploy. The first API request creates the tables. The admin user is created from `ADMIN_EMAIL` and `ADMIN_PASSWORD` if that email does not exist yet.
5. Open `/login.html` and sign in with those admin values.

URL-encode any symbols in the database password before putting it in `DATABASE_URL`. Vercel request bodies are limited, so keep image uploads under about 4.5 MB.

## Local preview

Open `index.html` with a static server to preview the pages. Login and saving content need PHP plus the same `DATABASE_URL` in a `.env` file copied from `env.example`.

## 📁 Project Structure

```
├── index.html              # Homepage (use this)
├── about.html              # About page (use this)
├── contact.html            # Contact page (use this)
├── admin.html              # Admin panel (use this)
├── login.html              # Admin login (use this)
├── *.html                  # All other frontend pages
├── api/                    # Backend API endpoints
├── admin-tabs/             # Admin panel tabs
├── includes/               # PHP includes (header, footer, etc.)
├── config/                 # Configuration files
├── css/                    # Stylesheets
└── js/                     # JavaScript files
```

## 📄 Important Files

- **WHICH_FILES_TO_USE.md** - Guide on which files to use (HTML vs PHP)
- **SETUP_INSTRUCTIONS.md** - Detailed setup guide
- **ADMIN_CREDENTIALS.md** - Admin login credentials

## 🎯 Key Features

- ✅ All frontend pages converted to HTML (works with Live Server)
- ✅ Admin panel for content management
- ✅ Responsive design
- ✅ Database-driven content
- ✅ Image upload and management
- ✅ Color customization
- ✅ Multiple content sections

## 🔧 Admin Panel

Access the admin panel at `login.html`. Sign in with the `ADMIN_EMAIL` and `ADMIN_PASSWORD` set in Vercel.

## 📝 Notes

- Frontend uses HTML files (works without PHP)
- Backend PHP files are kept for server functionality
- Admin panel requires PHP server
- All pages load default content when PHP server unavailable
