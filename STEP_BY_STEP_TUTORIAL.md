# Learn the Café Management System, one step at a time

This tutorial is for a beginner who already knows a little HTML, JavaScript, and PHP. Open the code in VS Code while following each step.

## Step 1 — Understand the 3 parts

- **Vue 3 frontend:** `frontend/src/`. This is what the cashier sees. It displays forms, tables, and buttons.
- **Laravel backend:** `backend/app/`, `backend/routes/`. This receives requests from Vue, checks input and user permissions, and handles business rules.
- **MySQL database:** tables built by `backend/database/migrations/`. Laravel models represent those tables.

Example: the **Menu** page asks `GET http://localhost:8000/api/products` via Axios, Laravel queries MySQL and sends JSON back to Vue.

## Step 2 — Start the backend

1. Open XAMPP and start MySQL.
2. Create the database `cafe_management` in phpMyAdmin.
3. Open PowerShell in the `backend` directory.
4. Run `composer install`, copy `.env.example` to `.env`, and set the MySQL credentials.
5. Run `php artisan key:generate` once to set the encryption key.
6. Run `php artisan migrate` to create tables.
7. Run `php artisan db:seed --class=CafeSeeder` to create demo records (development only).
8. Run `php artisan serve --host=localhost --port=8000`.

Check `http://localhost:8000/` in your browser: Laravel should return a short JSON response. You can also run `php artisan route:list --path=api` to list the routes.

## Step 3 — Start the frontend

1. Open a second PowerShell in `frontend`.
2. Copy `.env.example` to `.env`.
3. Run `npm.cmd install` and `npm.cmd run dev`.
4. Open `http://localhost:5173`.

Read `frontend/src/main.js` first: this defines the routes (pages) and protects pages that require authentication.

## Step 4 — Learn how login/logout works

Read these files in order:

1. `frontend/src/pages/Login.vue`: the email/password form calls `signIn`.
2. `frontend/src/session.js`: first requests `/sanctum/csrf-cookie` and then posts credentials to `/api/login`.
3. `backend/routes/api.php`: maps the request to `AuthController::login`.
4. `backend/app/Http/Controllers/Api/AuthController.php`: Laravel validates the login, starts a session, and returns the user.
5. `frontend/src/App.vue`: shows the user in the sidebar and calls the logout endpoint.

**Exercise:** Try a wrong password, then the correct one. Sign out and confirm protected pages redirect to `/login`.

## Step 5 — Learn database migrations and models

Read `backend/database/migrations/2026_10_23_000002_create_cafe_tables.php`. Its `Schema::create` calls create these tables:

- `categories`: names like Coffee and Tea.
- `products`: menu name, category, sell price, cost, stock and active flag.
- `cafe_tables`: number of seats and table name.
- `orders`: order number, status, total, optional table.
- `order_items`: snapshot of product name, price and quantity when sold.
- `payments`: paid amount, method and timestamp (one payment per order).
- `expenses`: operational expenses.

The `users` table is created by a separate migration and extended to include staff roles.

For example, `backend/app/Models/Product.php` tells Laravel which fields may be saved and how to cast them. The `category()` method tells Eloquent how products relate to categories.

**Exercise:** Add a category from the app, then look for it in phpMyAdmin under the `categories` table.

## Step 6 — Learn REST endpoints with menu CRUD

Open `backend/routes/api.php`:

- `GET /api/products`: read products.
- `POST /api/products`: create a product.
- `PUT /api/products/{product}`: update a product.
- `DELETE /api/products/{product}`: delete a product.

All routes require authentication except login. The controller's `admin()` method prevents Cashiers from modifying categories, products, tables, staff and reports. Read the methods in `backend/app/Http/Controllers/Api/CafeController.php` and compare them with `frontend/src/pages/Catalog.vue`.

**Exercise:** Add Iced Americano, edit its price, then disable it. Check that it is unavailable in New Order.

## Step 7 — Trace one complete sale

1. `frontend/src/pages/NewOrder.vue`: cashier selects products/quantity and table.
2. `POST /api/orders`: Vue sends product IDs and quantities **without trusting client prices**.
3. `CafeController::storeOrder`: Laravel validates the request, locks the table/products, checks stock, reads MySQL prices and inserts order and order items in a database transaction.
4. `frontend/src/pages/Orders.vue`: cashier opens the order and advances Pending → Preparing → Served → Completed.
5. `POST /api/orders/{order}/pay`: records a full payment; it does **not** charge actual accounts or payment gateways.
6. `GET /api/dashboard` and `GET /api/reports?from=...&to=...`: report data comes from saved orders/payments/expenses.

**Exercise:** Sell one Latte (5,500 MMK) and one Croissant (4,000 MMK) at Table 1. The total should be **9,500 MMK**. Pay with Cash and inspect the `orders`, `order_items` and `payments` tables in phpMyAdmin.

## Step 8 — Modify the project yourself

Start with a safe small change: add a product field called `description`.

1. Create a new Laravel migration: `php artisan make:migration add_description_to_products_table`.
2. In the migration, add a nullable text column to `products`.
3. Run `php artisan migrate` (do not use `migrate:fresh` to preserve data).
4. Update the `Product` model `$fillable` fields.
5. Update `CafeController::productData` to validate an optional description.
6. Update the product form and display in `frontend/src/pages/Catalog.vue`.
7. Test adding/editing a description in the app and phpMyAdmin.

This shows the standard pattern for most Laravel + Vue features: **migration → model → controller → route → Vue page**.

## How to continue learning

Add recipes and ingredient-level stock, discounts/taxes, receipts, refunds, and automated tests. For real payment methods, integrate authorized payment providers; recording KBZPay/WavePay in this MVP is only bookkeeping. Keep backups and review security before deploying publicly.
