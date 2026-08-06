# Running with XAMPP

Quick steps to run this Laravel project using XAMPP on Windows.

1. Start XAMPP Control Panel and ensure **Apache** and **MySQL** are running.
2. Copy `.env.example` to `.env` (the project includes a helper batch script that does this).
3. Update `.env` database settings if your MySQL username/password differ. Default values expect:
   - DB_CONNECTION=mysql
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=hrpayroll
   - DB_USERNAME=root
   - DB_PASSWORD=

4. From the project root run the batch helper (edit path to PHP if needed):

```powershell
& 'C:\xampp\php\php.exe' setup-xampp.bat
```

This will:
- install PHP dependencies with Composer
- generate an app key
- run `php artisan migrate:fresh --seed` (will create database tables and seed demo data)
- install npm packages and build frontend assets

5. After setup, you can either:
- Serve using Laravel's built-in server (recommended for local development):

```powershell
& 'C:\xampp\php\php.exe' artisan serve --host=127.0.0.1 --port=8000
```

- Or configure Apache virtual host to point to the `public` directory and access via browser directly.

Troubleshooting
- If `composer` is not available in PATH, install Composer or run `php composer.phar install` if you have the phar.
- Ensure PHP version in XAMPP meets the project's requirement (see `composer.json`, needs PHP ^8.2).
- If migrations fail, verify MySQL is running and `.env` DB credentials are correct.

If you want, I can add an Apache virtual host sample config and a PowerShell script to start the server automatically.