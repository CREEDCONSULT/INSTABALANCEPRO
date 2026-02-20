# 🚀 QUICK START GUIDE - InstaBAlancePRO

## Option 1: Automatic Setup (Recommended for Windows)

### Prerequisites
- Windows 10/11
- Administrator access to PowerShell

### Steps
1. **Open PowerShell as Administrator**
   - Right-click PowerShell → "Run as Administrator"

2. **Navigate to project**
   ```powershell
   cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
   ```

3. **Run setup script**
   ```powershell
   .\setup-dev.bat
   ```

4. **Wait for installation** (5-10 minutes)

5. **Close and reopen PowerShell**

6. **Start development server**
   ```powershell
   cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
   php -S localhost:8000 -t public/
   ```

7. **Open browser to http://localhost:8000**

---

## Option 2: Docker Setup (If you have Docker Desktop)

### Prerequisites
- Docker Desktop installed
- ~500MB free disk space

### Steps
1. **Navigate to project**
   ```powershell
   cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
   ```

2. **Start Docker containers**
   ```powershell
   docker-compose up -d
   ```

3. **Wait for MySQL to initialize** (30 seconds)

4. **Open http://localhost in browser**

5. **To stop**
   ```powershell
   docker-compose down
   ```

---

## Option 3: Manual Setup (Windows Users)

### Step 1: Install PHP
- Download PHP 8.2 from https://www.php.net/downloads
- Extract to `C:\php`
- Add `C:\php` to Windows PATH:
  1. Press `Win + X` → System
  2. Click "Advanced system settings"
  3. Click "Environment Variables"
  4. Under "System variables", find "Path" → Edit
  5. Click "New" → Add `C:\php`
  6. Click OK

### Step 2: Install Composer
- Download from https://getcomposer.org/download/
- Run installer (next, next, finish)
- Verify: Open new PowerShell, type `composer --version`

### Step 3: Install MySQL
- Download MySQL from https://dev.mysql.com/downloads/mysql/
- Run installer (typical setup)
- Configuration Wizard:
  - Server type: Developer Default
  - MySQL Port: 3306
  - MySQL Root Password: `root` (for dev only!)

### Step 4: Setup Project
```powershell
# Navigate to project
cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"

# Install dependencies
composer install

# Create .env file
Copy-Item .env.example .env

# Open .env and add these (at minimum):
# APP_ENV=development
# APP_DEBUG=true
# APP_URL=http://localhost:8000
# DB_HOST=localhost
# DB_NAME=instagram_unfollower
# DB_USER=root
# DB_PASS=root
```

### Step 5: Setup Database
```powershell
# Open MySQL Command Line
mysql -u root -p

# Enter password: root

# Create database
CREATE DATABASE instagram_unfollower CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Import schema
mysql -u root -p instagram_unfollower < database/schema.sql
```

### Step 6: Start Server
```powershell
cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
php -S localhost:8000 -t public/
```

### Step 7: Open Browser
- Navigate to **http://localhost:8000**

---

## Configuration

### Setup Instagram OAuth (For Login Testing)

1. Go to https://developers.facebook.com
2. Create an App (type: Consumer)
3. Add "Instagram Basic Display" product
4. Get your App ID and App Secret
5. In `config/app.php`, add:
   ```php
   'instagram' => [
       'client_id' => 'YOUR_APP_ID',
       'client_secret' => 'YOUR_APP_SECRET',
       'redirect_uri' => 'http://localhost:8000/auth/callback',
   ]
   ```

Or in `.env`:
```
INSTAGRAM_APP_ID=your_app_id_here
INSTAGRAM_APP_SECRET=your_app_secret_here
INSTAGRAM_REDIRECT_URI=http://localhost:8000/auth/callback
```

### Setup Stripe (For Billing Testing)

1. Go to https://dashboard.stripe.com/test/apikeys
2. Get your **Test Secret Key** and **Test Publishable Key**
3. Add to `.env`:
   ```
   STRIPE_SECRET_KEY=sk_test_...
   STRIPE_PUBLISHABLE_KEY=pk_test_...
   ```

For testing, use test card: **4242 4242 4242 4242**

---

## Troubleshooting

### "PHP command not found"
- Make sure you've added PHP to PATH
- Restart PowerShell after adding to PATH
- Verify: `php --version`

### "MySQL connection refused"
- Check MySQL is running: 
  ```powershell
  Get-Service MySQL80
  ```
- If stopped, start it:
  ```powershell
  Start-Service MySQL80
  ```

### "Composer command not found"
- Restart PowerShell after installing Composer
- Verify: `composer --version`

### "Port 8000 already in use"
- Use different port: `php -S localhost:8001 -t public/`
- Or find what's using port 8000:
  ```powershell
  netstat -ano | findstr :8000
  ```

### Database errors
- Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- Check schema imported: `mysql -u root -p instagram_unfollower -e "SHOW TABLES;"`
- Reimport if needed: `mysql -u root -p instagram_unfollower < database/schema.sql`

---

## Next Steps After Setup

1. **Login**: Click "Login" and authenticate with Instagram
2. **Sync Data**: Click "Sync Now" to fetch followers/following
3. **View Rankings**: Go to /accounts/ranked
4. **Test Features**: Try all the tabs and features
5. **Check Console**: Open DevTools (F12) for any JavaScript errors

---

## Getting Help

If you get stuck:
1. Check the error message carefully
2. Google the error + "Windows" or "PHP"
3. Check that all services are running (MySQL, etc.)
4. Try Option 2 (Docker) if Option 3 is too complicated

---

**Ready? Choose your option above and start with Step 1!**
