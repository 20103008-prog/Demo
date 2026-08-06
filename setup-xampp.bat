@echo off
SETLOCAL ENABLEDELAYEDEXPANSION

:: Default XAMPP PHP path - change if your XAMPP is installed elsewhere
set XAMPP_PHP=C:\xampp\php\php.exe

:check_php
if not exist "%XAMPP_PHP%" (
    echo PHP not found at %XAMPP_PHP%.
    echo Please enter full path to PHP executable (eg. C:\xampp\php\php.exe):
    set /p XAMPP_PHP=
    if not exist "%XAMPP_PHP%" (
        echo PHP executable still not found. Aborting.
        pause
        exit /b 1
    )
)

:: Ensure .env exists
if not exist .env (
    copy .env.example .env > nul
    echo Created .env from .env.example
)

:: Install Composer dependencies (requires composer in PATH)
echo Installing PHP dependencies (composer)...
composer install
if errorlevel 1 (
    echo Composer install failed. Make sure Composer is installed and in PATH.
    pause
    exit /b 1
)

:: Generate app key
necho Generating app key...
"%XAMPP_PHP%" artisan key:generate

:: Run migrations and seed the DB (MySQL must be running in XAMPP)
necho Running migrations and seeders (will drop existing data)...
"%XAMPP_PHP%" artisan migrate:fresh --seed
if errorlevel 1 (
    echo Migration failed. Check your DB connection in .env and ensure MySQL is running.
    pause
    exit /b 1
)

:: Build frontend (node + npm required)
necho Installing and building frontend assets (npm)...
npm install
npm run build

:: Done message
necho Setup complete. Start Apache and MySQL from XAMPP Control Panel, then open http://127.0.0.1:8000 if using php artisan serve, or configure virtual host for Apache.
pause
ENDLOCAL
