# Cafe Management System — Laravel REST API + Vue 3 + MySQL

Beginner-friendly small café system, with Admin and Cashier accounts. All money amounts are whole **Myanmar Kyat (MMK)**.

> **Important:** This download is an **application source overlay/starter**, not a Laravel distribution. The `backend/` directory includes the files to copy into a normal Laravel 12/13 project, NOT its `artisan`, `vendor/`, `composer.json`, `bootstrap/`, etc. The `frontend/` directory is a complete Vite/Vue source project, but does not include downloaded `node_modules/`. Existing project users: back up your project before merging and do not overwrite custom files blindly.

## What is inside

- Authentication: login, logout, session check with Laravel Sanctum **cookie-based SPA** auth (no access token stored in localStorage), plus per-login throttle.
- Roles: Admin can manage categories, products, tables, expenses, staff and reports. Cashier can create orders, progress orders and accept payments, and view menu/tables/dashboard.
- Products: name, category, selling price, cost, optional quantity tracking, visibility.
- Orders: dine-in or takeaway, multiple products, server-side pricing, database transactions and stock checks, lifecycle (`pending -> preparing -> served -> completed`), cancellation before payment with stock restoration.
- Table availability: a table is occupied when its order is not completed or cancelled.
- Payment: one full payment per order; cash, KBZPay, WavePay and card. It records transactions only; **no actual payment-gateway integration**.
- Expenses and report: date-filtered paid revenue, product costs, expenses, estimated profit, daily CSV export.
- Seed data: categories, seven products, eight tables and two demonstration users.

This is a **learning/MVP** system. Before real café use, add automated tests, refunds, backup/restore, audit logs, better permissions, tax/discount policy, real payment reconciliation, printing, and production HTTPS/deployment.

## A. Prerequisites for Windows / XAMPP

1. XAMPP with **PHP >= 8.3 for Laravel 13** (Laravel 12 supports PHP >= 8.2). In PowerShell verify `C:\xampp\php\php.exe -v`.
2. MySQL/MariaDB in XAMPP; press **Start** next to MySQL. Apache is optional if using `artisan serve`.
3. Composer installed and available in PowerShell (`composer --version`).
4. Node.js >= 20.19 or >= 22.12 (`node -v`) and npm (`npm.cmd -v`).
5. Keep the frontend and backend on the **same hostname `localhost`** but different ports (5173, 8000).

Use `npm.cmd`, not `npm`, in PowerShell if PowerShell reports that `npm.ps1 cannot be loaded because running scripts is disabled`. This avoids changing execution policy.

## B. Create Laravel (skip if your backend already exists)

Open PowerShell:

```powershell
cd C:\xampp\htdocs
mkdir cafe-management
cd cafe-management
composer create-project laravel/laravel backend
cd backend
php artisan install:api
```

`php artisan install:api` installs Sanctum and registers Laravel API routes. If Sanctum and API routes already exist, **do not run it again blindly** because it may modify routes. If you're using a particular Laravel version, follow its installation documentation.

If you already have **both** `backend/` and `frontend/`, use those folders instead. Do not create another Laravel project.

## C. Merge the downloaded source files

1. Extract this ZIP to a different temporary directory, e.g. `C:\Users\YourName\Downloads\cafe-management-starter`.
2. Back up your existing `backend/app/Models/User.php`, `backend/routes/api.php`, `frontend/src/`, `frontend/package.json`, `frontend/vite.config.js`.
3. Copy the downloadable package's `backend/app`, `backend/database/migrations`, `backend/database/seeders`, `backend/routes/api.php`, and `backend/config/cors.php` **into your backend**, preserving folder structure. Note that the supplied `User.php` and `routes/api.php` replace those defaults; if your app has prior code, merge it manually.
4. Copy all the downloaded package's `frontend/` files into your frontend folder, **after backing up existing Vue code**. In a fresh Vue frontend you can use the whole included frontend folder directly.
5. **Do not** replace Laravel's `artisan`, `composer.json`, `vendor`, `bootstrap/app.php`, or existing `.env` file with anything from the ZIP.

### Edit `backend/bootstrap/app.php`

Inside the existing `->withMiddleware(...)` section, enable Sanctum SPA session auth:

```php
use Illuminate\Foundation\Configuration\Middleware;

// Inside Application::configure(...):
->withMiddleware(function (Middleware $middleware): void {
    $middleware->statefulApi();
})
```

Do NOT paste a second `->withMiddleware` chain; edit the one that already exists. Keep your other middleware settings.

## D. Database configuration

Open phpMyAdmin at `http://localhost/phpmyadmin`, create database `cafe_management` (collation `utf8mb4_unicode_ci`). Or execute the included `database/create_database.sql` in phpMyAdmin SQL tab.

If your backend has no `.env`, create it from the framework copy:

```powershell
cd C:\xampp\htdocs\cafe-management\backend
Copy-Item .env.example .env
php artisan key:generate
```

**Keep any existing `APP_KEY`** if your `.env` is already configured. In `backend/.env`, set these values (adapt user/password if your MySQL differs):

```dotenv
APP_NAME="Cafe Management"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafe_management
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
SANCTUM_STATEFUL_DOMAINS=localhost:5173
FRONTEND_URL=http://localhost:5173
```

The source includes `backend/.env.cafe.example` as a reference, **not a complete `.env`**. Laravel manages schema using migrations, so you do not import an application table dump.

Now run:

```powershell
cd C:\xampp\htdocs\cafe-management\backend
php artisan config:clear
php artisan migrate
php artisan db:seed --class=CafeSeeder
php artisan route:list --path=api
```

Important: use `php artisan migrate`, **not** `migrate:fresh`, especially if integrating with an existing database. `migrate:fresh` destroys tables/data. Only run the seeder in development; repeated seeding resets the sample user passwords to their known defaults.

### Time zone

For Myanmar business date reports, configure `backend/config/app.php` to use `Asia/Yangon` (`'timezone' => 'Asia/Yangon'`) and keep your server/database timezone policy consistent. Changing time zones on an existing production database requires checking stored timestamps and report boundaries.

## E. Install frontend dependencies

Open **another PowerShell window**:

```powershell
cd C:\xampp\htdocs\cafe-management\frontend
Copy-Item .env.example .env
npm.cmd install
```

`frontend/.env` should contain:

```dotenv
VITE_API_URL=http://localhost:8000
```

For your already existing Vue project instead of replacing `package.json`, install required packages:

```powershell
npm.cmd install axios vue-router bootstrap
npm.cmd install -D @vitejs/plugin-vue vite
```

Make sure its `src/main.js` imports and Vite configuration match the included files. `npm install` on the bundled frontend already handles all dependencies.

## F. Run both servers

**Terminal 1 — Laravel:**

```powershell
cd C:\xampp\htdocs\cafe-management\backend
php artisan serve --host=localhost --port=8000
```

**Terminal 2 — Vue:**

```powershell
cd C:\xampp\htdocs\cafe-management\frontend
npm.cmd run dev
```

Go to **http://localhost:5173** in your browser. Keep both terminals open, and keep XAMPP MySQL running. Do not mix `127.0.0.1` and `localhost` in these URLs, as cookie authentication may fail.

## G. Development-only demo accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@cafe.test` | `ChangeMe123!` |
| Cashier | `cashier@cafe.test` | `ChangeMe123!` |

**Change demo credentials immediately before any real or shared use. Never expose a seeded demo account to the public internet.** An Admin can change account passwords in Staff Management.

## H. Your first hands-on flow

1. Login as Admin.
2. **Menu management:** browse the Coffee, Tea, Snacks and Desserts categories, add `Iced Americano` with price 4,500 MMK, cost 1,700 MMK and choose whether you track stock.
3. **Tables:** check that `Table 1` is available.
4. **New order:** select Dine in, Table 1, add a Latte (5,500 MMK) and a Croissant (4,000 MMK); the displayed total should be **9,500 MMK**.
5. **Orders:** open the new order; progress it Pending → Preparing → Served → Completed. Before completing, choose **Cash** and record the full 9,500 MMK payment.
6. **Dashboard:** revenue and order counters update when refreshed. Table 1 is freed once order status is Completed.
7. **Expenses:** add an Electricity expense of 20,000 MMK.
8. **Reports:** select the transaction date and review revenue, estimated cost, expenses and profit; export daily CSV.
9. **Staff:** create a Cashier, log out, and test that cashier cannot edit the menu or access expenses, reports, staff pages.

## I. What each file does (beginner guide)

```text
backend/
  routes/api.php              HTTP API endpoints (/api/login, /api/orders, ...)
  app/Http/Controllers/Api/
    AuthController.php         session login, logout, current user
    CafeController.php         validation, rules, CRUD, sales calculations
  app/Http/Middleware/
    EnsureActiveUser.php       blocks disabled accounts from accessing the API
  app/Models/
    User.php                   Laravel user, role & active flag
    Category.php               category database model
    Product.php                menu product & stock
    CafeTable.php              dining table
    Order.php                  order and relationships
    OrderItem.php              line-item snapshot for historical prices
    Payment.php                full payment record
    Expense.php                operating expense
  database/migrations/        creates MySQL tables and foreign keys
  database/seeders/           starter/demo records
  config/cors.php             browser frontend allowed to call backend

frontend/
  index.html                  Vite entry HTML
  src/main.js                 Vue setup, router and route protection
  src/session.js              login/logout/current user session
  src/api.js                  Axios client & MMK formatter
  src/App.vue                 sidebar, header and page layout
  src/pages/*.vue             each screen's UI and API calls
  src/style.css               styling & mobile layout
  package.json                frontend libraries and npm scripts
  vite.config.js              Vue compiler and dev server port
```

Example lifecycle for a new order:

```text
Click "Place order" in Vue NewOrder.vue
       │
       └─ POST /api/orders, item IDs + quantities only
                  │
          routes/api.php
                  │
          CafeController::storeOrder()
             - validate items
             - check occupied table
             - lock/check stock
             - read actual database prices
             - create order + items in one transaction
                  │
            MySQL tables: orders, order_items, products
                  │
          JSON response ──> Vue shows order in Orders.vue
```

Database relationships:

- `users 1 → many orders`, `users 1 → many payments/expenses`
- `categories 1 → many products`
- `cafe_tables 1 → many orders` (each order 0 or 1 table)
- `orders 1 → many order_items`
- `orders 1 → 0/1 payments`
- `products 1 → many order_items` (nullable on historical line items after product deletion)

## J. API cheat sheet

All `api/*` endpoints except login require a logged-in session. `POST /api/login` is session-based; first fetch `/sanctum/csrf-cookie` from the frontend. Most state-changing endpoints use request validation and return JSON; invalid inputs return HTTP 422.

| Method | Endpoint | Access |
| --- | --- | --- |
| POST | `/api/login` | Guest |
| GET | `/api/me` | Logged in |
| POST | `/api/logout` | Logged in |
| GET | `/api/dashboard` | Both roles |
| GET | `/api/categories` `/api/products` `/api/tables` | Both roles |
| POST/PUT/DELETE | `/api/categories`, `/api/products`, `/api/tables` and `/{id}` | Admin |
| GET | `/api/orders` `/api/orders/{id}` | Both |
| POST | `/api/orders` | Both |
| PATCH | `/api/orders/{id}/status` | Both |
| POST | `/api/orders/{id}/pay` | Both |
| GET/POST/DELETE | `/api/expenses` (and `/{id}`) | Admin |
| GET | `/api/reports?from=YYYY-MM-DD&to=YYYY-MM-DD` | Admin |
| GET/POST/PUT | `/api/staff` (and `/{id}`) | Admin |

Sample order request:

```json
{
  "type": "dine_in",
  "cafe_table_id": 1,
  "notes": "No sugar",
  "items": [
    { "product_id": 3, "qty": 1 },
    { "product_id": 6, "qty": 1 }
  ]
}
```

Total price is **NOT** accepted from the browser; the server looks it up itself. This example assumes default seed IDs.

## K. Troubleshooting

| Problem | Check |
| --- | --- |
| `npm.ps1 cannot be loaded` | Use `npm.cmd install` / `npm.cmd run dev` in PowerShell. |
| `php` not recognized | Use `C:\xampp\php\php.exe artisan ...` or add it to `PATH`. |
| `php artisan install:api` missing | Verify the backend is an actual Laravel 11+ project and Composer dependencies are installed. |
| `Class Laravel\Sanctum\HasApiTokens not found` | Run `php artisan install:api` before merging and check Composer. |
| `SQLSTATE 1049 Unknown database` | Create `cafe_management` first. |
| `SQLSTATE 2002 Connection refused` | Start XAMPP MySQL and check the DB port. |
| HTTP 419 CSRF error | Use consistent `localhost` URLs, enable `$middleware->statefulApi()`, check CORS credentials and Axios config, then clear cookies and retry. |
| HTTP 401 unauthenticated | Login again; check session cookie, `SANCTUM_STATEFUL_DOMAINS`, app session config and frontend origin. |
| HTTP 403 on menu edits | Cashier role cannot change the menu; login as Admin. |
| Routes missing | Run `php artisan route:list --path=api`, check `bootstrap/app.php` API routing and `routes/api.php`. |
| Changes to `.env` ignored | Run `php artisan config:clear` then restart backend. |
| Port 5173 occupied | Stop the other Vite process. This project uses a strict port because cookie CORS config expects 5173. |

## L. Suggested next steps

After learning the complete sale flow: add individual ingredients (beans, milk, syrup), recipes and inventory deductions; order discount/tax support; printable receipts; refunds; secure backups and user activity audit logs; tests and production deployment. Don't deploy this learning starter as-is.
