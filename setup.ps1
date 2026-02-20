# InstaBAlancePRO Interactive Setup Script
# Run: powershell -ExecutionPolicy ByPass -File setup.ps1

Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  InstaBAlancePRO - Development Environment Setup               ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Colors
$success = @{ ForegroundColor = 'Green' }
$warning = @{ ForegroundColor = 'Yellow' }
$error_color = @{ ForegroundColor = 'Red' }
$info = @{ ForegroundColor = 'Cyan' }

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")
if (-not $isAdmin) {
    Write-Host "⚠ WARNING: This script is not running as Administrator." @warning
    Write-Host "   Some features may not work. For best results:" @warning
    Write-Host "   Right-click PowerShell → 'Run as Administrator' → Re-run this script" @warning
    Write-Host ""
}

# Step 1: Check Prerequisites
Write-Host "[1/5] Checking Prerequisites..." @info
Write-Host ""

# Check PHP
Write-Host "  ✓ Checking PHP..." -NoNewline
$php_version = php --version 2>&1 | Select-Object -First 1
if ($php_version) {
    Write-Host " Found: $php_version" @success
} else {
    Write-Host " NOT FOUND" @error_color
    Write-Host "    Please install PHP 8.2+ from https://www.php.net/downloads" @warning
    exit 1
}

# Check Composer
Write-Host "  ✓ Checking Composer..." -NoNewline
$composer_version = composer --version 2>&1
if ($composer_version) {
    Write-Host " Found: $(($composer_version -split '\n')[0])" @success
} else {
    Write-Host " NOT FOUND" @error_color
    Write-Host "    Please install Composer from https://getcomposer.org/download" @warning
    exit 1
}

# Check MySQL
Write-Host "  ✓ Checking MySQL..." -NoNewline
$mysql_version = mysql --version 2>&1
if ($mysql_version) {
    Write-Host " Found: $mysql_version" @success
} else {
    Write-Host " NOT FOUND" @warning
    Write-Host "    MySQL is needed for database. You can install manually or use Docker." @warning
}

Write-Host ""

# Step 2: Install Composer Dependencies
Write-Host "[2/5] Installing PHP Dependencies..." @info
Write-Host ""
Write-Host "Running: composer install" @info
Write-Host ""

composer install
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ Composer install failed!" @error_color
    exit 1
}

Write-Host ""
Write-Host "✓ Dependencies installed successfully" @success
Write-Host ""

# Step 3: Setup .env file
Write-Host "[3/5] Setting up Configuration..." @info

if (Test-Path ".env") {
    Write-Host "  ✓ .env file already exists" @success
} else {
    Write-Host "  Creating .env from .env.example..." @info
    Copy-Item ".env.example" ".env"
    Write-Host "  ✓ .env file created" @success
}

Write-Host ""
Write-Host "  Edit your .env file with:" @info
Write-Host "  - Database credentials (DB_HOST, DB_USER, DB_PASS)" @info
Write-Host "  - Instagram OAuth keys (INSTAGRAM_APP_ID, INSTAGRAM_APP_SECRET)" @info
Write-Host "  - Stripe keys (optional)" @info
Write-Host ""

# Step 4: Database Setup Info
Write-Host "[4/5] Database Setup..." @info
Write-Host ""

$dbChoice = Read-Host "Do you want to setup the database now? (y/n)"
if ($dbChoice -eq 'y' -or $dbChoice -eq 'Y') {
    Write-Host ""
    Write-Host "To setup database manually:" @info
    Write-Host "1. Open MySQL Command Line or MySQL Workbench" @info
    Write-Host "2. Create database:" @info
    Write-Host "   CREATE DATABASE instagram_unfollower CHARACTER SET utf8mb4;" @warning
    Write-Host "3. Import schema:" @info
    Write-Host "   mysql -u root -p instagram_unfollower < database/schema.sql" @warning
    Write-Host ""
    Write-Host "Or if using Docker:" @info
    Write-Host "   docker-compose up -d  (comes with MySQL pre-configured)" @warning
} else {
    Write-Host "You can setup database later with the commands above." @info
}

Write-Host ""

# Step 5: Startup Info
Write-Host "[5/5] Ready to Start!" @info
Write-Host ""
Write-Host "To start the development server:" @success
Write-Host ""
Write-Host "  # Option A: Built-in PHP Server (Recommended for quick testing)" @info
Write-Host "  php -S localhost:8000 -t public/" @warning
Write-Host ""
Write-Host "  # Option B: Using Docker (Recommended for production-like setup)" @info
Write-Host "  docker-compose up -d" @warning
Write-Host ""
Write-Host "Then open: http://localhost:8000" @info
Write-Host ""

# Option to start server now
$startChoice = Read-Host "Start the PHP development server now? (y/n)"
if ($startChoice -eq 'y' -or $startChoice -eq 'Y') {
    Write-Host ""
    Write-Host "Starting PHP development server on http://localhost:8000" @success
    Write-Host "Press CTRL+C to stop the server" @warning
    Write-Host ""
    php -S localhost:8000 -t public/
} else {
    Write-Host ""
    Write-Host "✓ Setup complete! Run the commands above when ready." @success
    Write-Host ""
}
