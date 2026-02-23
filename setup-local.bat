@echo off
REM InstaBAlancePRO Local Setup Script

echo.
echo ==================================
echo InstaBAlancePRO Local Setup
echo ==================================
echo.

REM Download PHP 8.2 if not already present
if not exist "C:\php-8.2\" (
    echo [1/5] Downloading PHP 8.2...
    powershell -Command "Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs17-x64.zip' -OutFile 'C:\php-8.2.27.zip' -UseBasicParsing"
    
    if errorlevel 1 (
        echo ERROR: Failed to download PHP
        exit /b 1
    )
    
    echo [1/5] Extracting PHP...
    powershell -Command "Expand-Archive -Path 'C:\php-8.2.27.zip' -DestinationPath 'C:\php-8.2' -Force"
    if errorlevel 1 (
        echo ERROR: Failed to extract PHP
        exit /b 1
    )
)

REM Copy environment file
echo [2/5] Setting up environment...
copy /Y .env.local .env >nul
if errorlevel 1 (
    echo ERROR: Failed to copy .env.local
    exit /b 1
)

REM Install Composer dependencies
if not exist "vendor\" (
    echo [3/5] Installing PHP dependencies...
    "C:\php-8.2\php.exe" composer.phar install --prefer-dist
    if errorlevel 1 (
        echo ERROR: Composer install failed
        exit /b 1
    )
)

REM Start MySQL
echo [4/5] Starting MySQL database...
docker-compose -f docker-compose.local.yml up -d
timeout /t 3 /nobreak

REM Wait for MySQL
echo [5/5] Waiting for database to be ready...
timeout /t 5 /nobreak

echo.
echo ==================================
echo Setup Complete!
echo ==================================
echo.
echo To start the app, run this command in a NEW terminal:
echo.
echo   C:\php-8.2\php.exe -S localhost:8000 -t public
echo.
echo Then open: http://localhost:8000
echo.
echo PhpMyAdmin: http://localhost:8001
echo User: root, Password: rootpassword
echo.
