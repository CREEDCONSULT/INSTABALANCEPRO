@echo off
REM InstaBAlancePRO Development Setup Script
REM **IMPORTANT: Run this as Administrator**

echo ============================================
echo InstaBAlancePRO Development Environment Setup
echo ============================================
echo.

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo.
    echo How to run as Administrator:
    echo 1. Right-click on this file (setup-dev.bat)
    echo 2. Select "Run as administrator"
    pause
    exit /b 1
)

echo [1/4] Installing PHP 8.2...
choco install php --version=8.2.0 -y
if errorlevel 1 goto :error

echo.
echo [2/4] Installing Composer...
choco install composer -y
if errorlevel 1 goto :error

echo.
echo [3/4] Installing MySQL (MariaDB)...
choco install mysql-community-server -y
if errorlevel 1 goto :error

echo.
echo [4/4] Refreshing PATH environment...
setx PATH "%PATH%;C:\tools\php\php-8.2.0;C:\Program Files\MySQL\MySQL Server 8.0\bin"

echo.
echo ============================================
echo ✓ Setup Complete!
echo ============================================
echo.
echo Next steps:
echo 1. Close and reopen PowerShell
echo 2. Run: cd "C:\Users\HP ENVY x360\Desktop\INSTABALANCEPRO"
echo 3. Run: php -S localhost:8000 -t public/
echo.
pause
exit /b 0

:error
echo.
echo ✗ Setup failed! Check the error message above.
pause
exit /b 1
