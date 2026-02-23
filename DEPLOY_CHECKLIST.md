# 000webhost Deployment Checklist

## Pre-Upload: On Your Local Machine

- [ ] Run `composer install` to generate `vendor/` folder
- [ ] Verify `.env` file exists with database credentials
- [ ] Test `.env` loads correctly: Check `config/app.php`
- [ ] Verify `database/schema.sql` exists
- [ ] Check `public/.htaccess` exists and is valid

## Upload to 000webhost (or FileZilla/WinSCP)

- [ ] Upload all files EXCEPT:
  - `node_modules/` (if exists)
  - `.git/` directory
  - `.gitignore`
  - `*.md` files (optional, for reference)
  - Docker files (not needed: `Dockerfile`, `docker-compose*.yml`)

Minimal upload structure:
```
public_html/
├── public/              ✓ UPLOAD
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── src/                 ✓ UPLOAD
├── config/              ✓ UPLOAD
├── database/            ✓ UPLOAD
├── vendor/              ✓ UPLOAD (include vendor/)
├── composer.json        ✓ UPLOAD
├── .env                 ✓ UPLOAD (update with DB creds first)
├── .htaccess            ✓ UPLOAD (root level)
└── logs/                ✓ CREATE folder
```

## Post-Upload: In 000webhost Dashboard

1. **Create MySQL Database**
   - Go to MySQL Databases
   - Create database (auto-prefixed with your ID)
   - Create user with password
   - Note credentials

2. **Update .env**
   - Edit `.env` in File Manager
   - Set DB_HOST, DB_NAME, DB_USER, DB_PASS
   - Set APP_URL to your domain
   - Save

3. **Import Database Schema**
   - Go to phpMyAdmin
   - Select your database
   - Import tab → Upload `database/schema.sql`
   - Click Import

4. **Test Connection**
   - Visit your domain: `https://yourdomain.000webhostapp.com`
   - You should see InstaBAlancePRO login page
   - If 404, check `.htaccess` is in root `public_html/`

5. **Create .htaccess** (if needed)
   - In File Manager, create new file `.htaccess` in `public_html/`
   - Add routing rules (see DEPLOY_000WEBHOST.md)

## Quick FTP Upload with FileZilla

1. Host: `files.000webhost.com`
2. User: Your 000webhost email/username
3. Password: Your FTP password
4. Connect
5. Navigate to `public_html`
6. Drag & drop your project files
7. Click **Process Queue**
8. Wait for upload to complete

## Verification

Once uploaded & database imported:
- [ ] App loads at your domain
- [ ] Login page appears
- [ ] No 404 errors for CSS/JS
- [ ] Database connection works
- [ ] Can navigate to different pages

## Time to Deploy

- Account creation: 2 minutes
- File upload: 5-10 minutes (depending on file size)
- Database setup: 2 minutes
- **Total: ~15 minutes to live demo**

---

**Need help during deployment?** Check error logs:
- File Manager → `logs/error.log`
- Dashboard → Error Logs tab
