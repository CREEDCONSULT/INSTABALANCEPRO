# 🎯 IMPLEMENTATION STEPS - InstaBAlancePRO

## Current Status
✅ All 12 PROMPTs Complete  
✅ Full Codebase Ready  
✅ Database Schema Ready  
✅ Setup Files Created  
⏳ Ready for Deployment & Testing

---

## 📋 What You Need (Choose Your Path)

### Path A: Quick Docker Setup (Easiest - 2 minutes)
**Prerequisites**: Docker Desktop installed

```powershell
# 1. Navigate to project
cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"

# 2. Start containers
docker-compose up -d

# 3. Done! Open http://localhost:8000
```

**Includes**:
- PHP 8.2 with Apache
- MySQL 8.0 with auto-imported schema
- PHPMyAdmin at http://localhost:8080

---

### Path B: Manual Setup (Complete Control - 15 minutes)
**Prerequisites**: PHP 8.2, MySQL, Composer installed locally

```powershell
# 1. Navigate to project
cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"

# 2. Run setup script
powershell -ExecutionPolicy ByPass -File setup.ps1

# 3. Configure .env file
# Edit .env with your database credentials:
# DB_HOST=127.0.0.1
# DB_NAME=instagram_unfollower
# DB_USER=root
# DB_PASS=your_password

# 4. Create database
mysql -u root -p < database/schema.sql

# 5. Start server
php -S localhost:8000 -t public/
```

---

### Path C: Automated Windows Setup (Admin Required - 10 minutes)
**Prerequisites**: Administrator access

```powershell
# 1. Right-click PowerShell, select "Run as Administrator"

# 2. Run setup batch file
cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
.\setup-dev.bat

# 3. Follow the prompts (installs PHP, MySQL, Composer)

# 4. When complete, close and reopen PowerShell

# 5. Run manual setup from above
```

---

## 🚀 QUICK START (Path A - Recommended)

### Step 1: Install Docker Desktop (if not already done)
1. Download from https://www.docker.com/products/docker-desktop
2. Run installer
3. Restart computer
4. Open PowerShell and verify:
   ```powershell
   docker --version
   docker-compose --version
   ```

### Step 2: Start the Application
```powershell
cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
docker-compose up -d
```

### Step 3: Wait for MySQL
```powershell
# Wait ~30 seconds for MySQL to initialize, then check:
docker-compose ps
# Should show all containers as "Up"
```

### Step 4: Open in Browser
```
http://localhost:8000
```

You should see the InstaBAlancePRO homepage!

### Step 5: To Stop
```powershell
docker-compose down
```

---

## 🔧 Configuration

### Instagram OAuth (Required for Login)

1. Go to **https://developers.facebook.com**
2. Create an App (Consumer type)
3. Add "Instagram Basic Display" product
4. Get your **App ID** and **App Secret**
5. For local testing, set redirect to:
   ```
   http://localhost:8000/auth/callback
   ```

6. Edit `.env` or in Docker:
   - Edit `.env` file and add:
   ```env
   INSTAGRAM_APP_ID=your_app_id
   INSTAGRAM_APP_SECRET=your_app_secret
   INSTAGRAM_REDIRECT_URI=http://localhost:8000/auth/callback
   ```
   
   - Or if using Docker, edit `.env` and restart:
   ```powershell
   docker-compose down
   docker-compose up -d
   ```

### Stripe (Optional, for Billing)

1. Go to **https://dashboard.stripe.com/test/apikeys**
2. Copy **Test Secret Key** and **Test Publishable Key**
3. Add to `.env`:
   ```env
   STRIPE_SECRET_KEY=sk_test_...
   STRIPE_PUBLISHABLE_KEY=pk_test_...
   ```

For testing: Use card **4242 4242 4242 4242**

---

## ✅ Testing Checklist

### Phase 1: Basic Setup (5 minutes)
- [ ] Server running at http://localhost:8000
- [ ] No "Connection Refused" errors
- [ ] Static assets loading (CSS, JS visible)
- [ ] No 500 errors in console

### Phase 2: Authentication (10 minutes)
- [ ] Click "Login" button
- [ ] Redirects to Instagram OAuth
- [ ] After login, redirects back to app
- [ ] Dashboard shows account information
- [ ] Session persists on page refresh

### Phase 3: Database (5 minutes)
- [ ] Click "Sync Now" on dashboard
- [ ] See spinner/loading indicator
- [ ] Sync completes successfully
- [ ] Followers/Following counts appear
- [ ] Data persists after refresh

### Phase 4: Features (20 minutes)
Tests to run in order:

**Ranked List**
- [ ] Navigate to `/accounts/ranked`
- [ ] See table of accounts
- [ ] Filter by username (search)
- [ ] Filter by score range
- [ ] Sort by different columns
- [ ] Pagination works

**Kanban Board**
- [ ] Navigate to `/kanban`
- [ ] See 4 columns
- [ ] Drag card between columns
- [ ] Click card to edit
- [ ] Data persists after refresh

**Activity Calendar**
- [ ] Navigate to `/activity`
- [ ] See calendar grid
- [ ] See heatmap colors
- [ ] Click day to see events
- [ ] Navigate months

**Billing**
- [ ] Navigate to `/billing`
- [ ] See current plan
- [ ] See usage stats
- [ ] Click "Upgrade"
- [ ] See pricing page

**Settings**
- [ ] Navigate to `/settings`
- [ ] Try changing profile name
- [ ] Try changing password
- [ ] Try adjusting scoring sliders
- [ ] Try exporting data (JSON/CSV)

### Phase 5: Error Handling (5 minutes)
- [ ] Go to `/invalid-route` → See 404 page
- [ ] Stop server, try request → See error
- [ ] Check browser console (F12) → No red errors

---

## 🐛 Troubleshooting

### Docker Issues

**"Cannot connect to http://localhost:8000"**
```powershell
# Check container status
docker-compose ps

# If not running, check logs
docker-compose logs

# Restart containers
docker-compose restart
```

**"Port 8000 already in use"**
```powershell
# Change port in docker-compose.yml:
# Change "8000:80" to "8001:80"
# Then access http://localhost:8001
```

### Manual Setup Issues

**"PHP command not found"**
```powershell
# Restart PowerShell after installing PHP
# Then test:
php --version

# If still not found, add to PATH manually
$env:PATH += ";C:\php"
```

**"MySQL connection refused"**
```powershell
# Check MySQL service
Get-Service MySQL80

# Start if stopped
Start-Service MySQL80

# Test connection
mysql -u root -p
```

**"Port 8000 already in use"**
```powershell
# Use different port
php -S localhost:8001 -t public/

# Or find what's using 8000
netstat -ano | findstr :8000
```

### Database Issues

**"Database doesn't exist"**
```sql
-- Create it manually
CREATE DATABASE instagram_unfollower CHARACTER SET utf8mb4;

-- Then import schema
mysql -u root -p instagram_unfollower < database/schema.sql
```

**"No tables in database"**
```bash
# Import schema
mysql -u root -p instagram_unfollower < database/schema.sql

# Verify
mysql -u root -p instagram_unfollower -e "SHOW TABLES;"
```

---

## 📊 Server Monitoring

### Check what's running
```powershell
# Docker
docker-compose ps

# PHP server
netstat -ano | findstr :8000

# MySQL
Get-Service MySQL80
```

### View logs
```powershell
# Docker
docker-compose logs -f web
docker-compose logs -f db

# PHP (built-in server shows in terminal)
# MySQL (check system Event Viewer)
```

---

## 🎓 Next Steps After Setup

1. **Explore the Codebase**
   - Check `src/Controllers/` for business logic
   - Check `src/Views/pages/` for UI templates
   - Check `src/Services/` for external API integration

2. **Customize Configuration**
   - Update `.env` with your Instagram app credentials
   - Configure email in `.env` (MAIL_*)
   - Set up SSL certificate for production

3. **Test All Features**
   - Complete the testing checklist above
   - Try different flows and interactions
   - Check browser console (F12) for errors

4. **Deploy to Production**
   - Follow production deployment guide in `tech-stack.md`
   - Use DigitalOcean, AWS, or PaaS provider
   - Setup SSL/HTTPS
   - Configure backups
   - Monitor application

---

## 📞 Support Resources

- **Error in Terminal?** Copy the error message and Google it
- **Database Issue?** Check `database/schema.sql` for structure
- **Code Question?** Read comments in `src/` files
- **API Issue?** Check InstagramApiService in `src/Services/`
- **Deployment Question?** See `tech-stack.md`

---

## ✨ Success Indicators

You'll know setup is working when:
- ✅ Website loads at http://localhost:8000
- ✅ You can login with Instagram
- ✅ Dashboard shows follower data
- ✅ All tabs work without errors (F12 console clean)
- ✅ You can sync, create queue, view kanban, etc.

---

**You're ready! Choose your path above and get started! 🚀**

Questions? Check QUICK_START.md or this guide again.
