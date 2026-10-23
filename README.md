# Café Management System — complete application source

This archive contains **Laravel 12 application code**, **Vue 3/Vite frontend**, and **MySQL schema migrations/seed data**. It contains the Laravel **application scaffold** (`composer.json`, `artisan`, config, routing, public entry point), so you do **not** have to run `composer create-project` first. As is normal for source archives, you must install dependencies on your computer (`vendor/` and `node_modules/` are **not** included). This is a learning/MVP project, not a production-ready POS.

> If you already have a Laravel/Vue project you want to keep, read `MERGE_INTO_EXISTING_PROJECT.md` before copying any files. Back up your work first; avoid replacing important custom code.

## Requirements

- Windows with XAMPP/MySQL running. PHP **8.2 or newer**; ensure required PHP extensions are enabled (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, etc.).
- Composer and Node.js with npm. For Vite 7, use Node >=20.19 or >=22.12.
- Two PowerShell windows. Always use `localhost` (not a mix of localhost and 127.0.0.1) for web addresses.

## Installation steps (Windows PowerShell)

1. Extract this ZIP to `C:\xampp\htdocs\cafe-management` so that it contains folders `backend`, `frontend`, `database`.
2. Start **MySQL** from the XAMPP control panel; open phpMyAdmin at <http://localhost/phpmyadmin>. Create a database named `cafe_management`. You can also run `database/create_database.sql` from the phpMyAdmin SQL tab.
3. From a PowerShell terminal, install backend dependencies, create your `.env`, set your app key and migrate + seed:

```powershell
cd C:\xampp\htdocs\cafe-management\backend
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=CafeSeeder
php artisan serve --host=localhost --port=8000
```

If your PHP executable isn't on PATH, use `C:\xampp\php\php.exe artisan ...` instead of `php artisan ...`. Do **not** run `migrate:fresh` when you need to keep existing data. **Never seed development demo users on an Internet-facing production instance.**

4. In a **second** PowerShell window, install and launch the Vue frontend:

```powershell
cd C:\xampp\htdocs\cafe-management\frontend
Copy-Item .env.example .env
npm.cmd install
npm.cmd run dev
```

PowerShell may block `npm.ps1`; using `npm.cmd` avoids changing execution policy. Visit <http://localhost:5173> in your browser. Both PowerShell windows must stay open; XAMPP's MySQL must remain running.

### Demo login (LOCAL DEVELOPMENT ONLY)

- Admin: `admin@cafe.test` / `ChangeMe123!`
- Cashier: `cashier@cafe.test` / `ChangeMe123!`

Immediately change or remove these credentials before sharing the system. Real KBZPay/WavePay integration, refunds, receipt printing, audit logs, and production backups are **not** implemented.

## Functional modules

1. Login/logout — Sanctum session cookies + CSRF (no token in localStorage)
2. Dashboard — today's paid revenue, order counts, unpaid orders, recent orders
3. Categories + products — add/edit/delete menu items; stock tracking
4. Tables — add/edit/delete and show occupied/available
5. Orders — dine-in/takeaway, line items, server-side price calculation, status flow
6. Payments — record full payment by Cash/KBZPay/WavePay/Card (record only)
7. Expenses — add/delete spending records
8. Reports — sales, product costs, expenses, estimated profit and daily CSV
9. Staff — admin/cashier roles, active account flag, account editing

### First exercise

Login as Admin → Menu → make a new product → New order → choose a table → add products → Place order → Orders → Record Payment → mark statuses → Dashboard → Reports.

## Folder tree

```text
cafe-management/
  README.md
  MERGE_INTO_EXISTING_PROJECT.md
  database/create_database.sql
  backend/
    composer.json, artisan, .env.example
    bootstrap/app.php, bootstrap/providers.php
    config/...
    routes/api.php, routes/web.php
    app/Http/Controllers/Api/AuthController.php
    app/Http/Controllers/Api/CafeController.php
    app/Models/...
    database/migrations/...
    database/seeders/CafeSeeder.php
    public/index.php
  frontend/
    package.json, index.html, vite.config.js, .env.example
    src/App.vue, src/main.js, src/api.js, src/session.js
    src/pages/...
    src/style.css
```

### How it works

Vue's `NewOrder.vue` sends item IDs and quantities to `POST /api/orders`. Laravel validates quantities, checks stock/table availability within a database transaction, loads product prices from MySQL, inserts `orders` and `order_items`, and returns JSON. Vue displays it in `Orders.vue`. Payment and report endpoints read the same saved data.

## Troubleshooting

- **No database:** ensure `cafe_management` exists, MySQL is started, and `backend/.env` DB credentials match XAMPP.
- **`php` not recognized:** use `C:\xampp\php\php.exe artisan serve`.
- **`npm.ps1` scripts disabled:** run `npm.cmd install` / `npm.cmd run dev`.
- **HTTP 419:** use `localhost` for both ports, check browser cookies, and clear Laravel's config cache with `php artisan config:clear`.
- **HTTP 401:** make sure you logged in; check `SANCTUM_STATEFUL_DOMAINS=localhost:5173` in `.env`.
- **Routes missing:** `php artisan route:list --path=api`.
- **Composer/PHP extension error:** enable missing extensions in your active PHP's `php.ini` and verify `php -m`.

For a file-by-file explanation, endpoint reference and deeper setup advice, read `MERGE_INTO_EXISTING_PROJECT.md` (its merge instructions are for an *existing* Laravel scaffold, and aren't needed for this full-source download).
