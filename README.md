# HR Payroll — Laravel + Bootstrap + MySQL

Clean Laravel 12 + Bootstrap 5 HR & Payroll app with a public product website.

## Setup

1. Start XAMPP MySQL  
2. Database name: `hrpayroll`  

```bash
composer install
npm install && npm run build
php artisan migrate:fresh --seed
php artisan serve
```

- Website: http://127.0.0.1:8000  
- Login: http://127.0.0.1:8000/login  

## Demo

| Role | Email | Password |
|------|-------|----------|
| Employee | arjun.sharma@corp.com | demo1234 |
| Manager | divya.krishnan@corp.com | demo1234 |
| Admin | admin@corp.com | admin1234 |
