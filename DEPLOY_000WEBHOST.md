# Deploy InstaBAlancePRO to 000webhost

## What is 000webhost?
- **Free PHP hosting** with MySQL database included
- **No credit card required**
- Instant setup (~30 seconds)
- Perfect for testing/demos

## Step 1: Create Account & App

1. Go to [000webhost.com](https://www.000webhost.com)
2. Click **"Create Your Free Website"**
3. Sign up with email (or use GitHub login - faster)
4. Enter a domain name (e.g., `instabalancepro-demo`)
5. Accept terms and create account
6. **Check your email** and confirm the account
7. Your site will be live at `https://instabalancepro-demo.000webhostapp.com`

## Step 2: Connect via FTP

After account confirmation, you'll get FTP details in your dashboard:
- **FTP Host:** e.g., `files.000webhost.com`
- **FTP User:** Your email or username
- **FTP Password:** Shown in dashboard
- **FTP Port:** 21

### Option A: Use File Manager (Easiest)
1. Go to **File Manager** in 000webhost dashboard
2. Delete the `index.html` file
3. Upload all files from your project

### Option B: Use FTP Client (More reliable)
1. Download **FileZilla** or **WinSCP** (free FTP clients)
2. Enter FTP credentials above
3. Connect to `files.000webhost.com`
4. Upload entire project to `public_html` folder

**Important:** Make sure the file structure is:
```
public_html/
  ├── public/
  │   ├── index.php
  │   ├── assets/
  │   └── .htaccess
  ├── src/
  ├── config/
  ├── database/
  ├── vendor/
  ├── composer.json
  └── .env
```

## Step 3: Configure Database

1. In 000webhost dashboard, go to **MySQL Databases**
2. Create a new database (e.g., `instagram_unfollower`)
3. Create a MySQL user with password
4. You'll get credentials like:
   - Database: `id00000_instab`
   - User: `id00000_user`
   - Password: `abc123xyz`
   - Host: `localhost` (always localhost on shared hosting)

## Step 4: Update .env File

Edit the `.env` file in `public_html/.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://instabalancepro-demo.000webhostapp.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=id00000_instab
DB_USER=id00000_user
DB_PASS=abc123xyz
```

**Upload the updated .env file via File Manager**

## Step 5: Import Database Schema

1. In 000webhost dashboard, go to **phpMyAdmin**
2. Select your database
3. Go to **Import** tab
4. Upload `database/schema.sql` from your project
5. Click **Import**

This creates all the tables automatically.

## Step 6: Fix Apache Configuration

Create `.htaccess` in `public_html/` with:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php?/$1 [QSA,L]
</IfModule>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

## Step 7: Test the App

1. Open your domain: `https://instabalancepro-demo.000webhostapp.com`
2. You should see the login page
3. Navigate and test all features

## Troubleshooting

### 404 Errors on Routes
- Check `.htaccess` is in `public_html/` root
- Verify `mod_rewrite` is enabled (000webhost has it by default)
- Clear browser cache

### Database Connection Error
- Verify credentials in `.env` match your MySQL setup
- Use phpMyAdmin to test connection
- Check database name matches exactly

### PHP Errors
- Go to **Error Logs** in 000webhost dashboard
- Check `logs/error.log` if it exists
- Verify all PHP extensions are available

### Missing Vendor Files
- Run `composer install` locally before uploading
- Or use the provided `vendor/` directory in the repo

## Access phpMyAdmin

**Direct URL:** `https://instabalancepro-demo.000webhostapp.com/phpmyadmin`

Or through 000webhost dashboard → **Manage** → **phpMyAdmin**

Login with your MySQL credentials created in Step 3.

---

**That's it!** Your app is now live and testable online. 🎉

Share the URL with anyone to demo the app without local setup complexity.
