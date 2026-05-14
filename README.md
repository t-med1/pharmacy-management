# PharmaManager

> A modern, production-ready pharmacy management system built with Laravel 8.

[![PHP](https://img.shields.io/badge/PHP-8.0-777BB4?style=flat-square&logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-4.x-7952B3?style=flat-square&logo=bootstrap)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## Overview

PharmaManager is a full-featured pharmacy administration dashboard that covers the entire operational cycle of a pharmacy — from purchasing medicines from suppliers, to managing stock levels, processing sales, and generating reports. It is designed for single or multi-user pharmacy environments, with role-based access control for fine-grained permission management.

---

## Features

### Inventory Management
- **Purchases** — Record medicine stock from suppliers with cost price, quantity, expiry date, and an image. Full CRUD with image upload.
- **Products** — Define the retail-facing product on top of each purchase with a selling price and optional discount (%).
- **Expired Medicines** — Real-time list of all medicines past their expiry date.
- **Out-of-Stock Alerts** — Automatic visual indicators and a dedicated view for zero-stock items.

### Sales
- **Point-of-Sale** — Record a sale and automatically decrement inventory. Prevents overselling with stock validation.
- **Stock Warning Notifications** — In-app notification when stock drops to ≤ 5 units after a sale.
- **Sale Editing** — Editing a sale correctly restores the original quantity and recalculates based on new values.

### Reporting & Export
- **Sales Reports** — Filter by date range, view summary totals (revenue, units sold, number of transactions).
- **Purchase Reports** — Filter by date range, view cost totals and unit counts.
- **Export** — Every report can be exported to **PDF**, **Excel**, **CSV**, or **Print** directly from the browser (no server packages required).

### Dashboard
- Today's revenue and monthly revenue stat cards
- Low-stock and expired medicine counters with quick links
- 7-day revenue bar chart (last week at a glance)
- Overview doughnut chart (purchases / suppliers / sales)
- Recent sales table with live DataTable pagination

### Activity Log
- Every create, update, delete, sale, and purchase action is logged with user, IP address, and timestamp.
- Searchable and sortable log table for auditing.
- Clear-log action for administrators.

### Access Control
- Role-based access using **Spatie Laravel Permission**
- Per-action permission guards (`create-sale`, `destroy-purchase`, `view-reports`, etc.)
- Admin can create roles, assign permissions to roles, and assign roles to users

### Administration
- **User Management** — CRUD with avatar upload and role assignment
- **Supplier Management** — Full supplier details (company, contact, address)
- **Category Management** — Organise medicines by category
- **Application Settings** — Configurable app name, logo, currency symbol
- **Database Backups** — Create, download, and delete backups via the UI (Spatie Laravel Backup)

---

## Tech Stack

| Layer       | Technology |
|-------------|------------|
| Backend     | PHP 8.0, Laravel 8.x |
| Frontend    | Bootstrap 4, jQuery, Inter font |
| Tables      | Yajra DataTables (server-side) |
| Charts      | Chart.js |
| Auth / RBAC | Laravel Sanctum + Spatie Permission |
| Backups     | Spatie Laravel Backup |
| Settings    | QCod Laravel App Settings |
| Database    | MySQL 8 |

---

## Project Architecture

```
app/
├── Http/
│   ├── Controllers/Admin/   # One controller per domain (Purchase, Product, Sale, etc.)
│   └── Requests/            # Form Request classes for all validation
├── Models/                  # Eloquent models with scopes, casts, and relationships
│   ├── Purchase.php         # Inventory record (scopeExpired, scopeOutOfStock, scopeLowStock)
│   ├── Product.php          # Retail price layer on top of Purchase (scopeInStock)
│   ├── Sale.php             # Sales transaction (scopeToday, scopeThisMonth)
│   └── ActivityLog.php      # Audit trail
├── Helpers/
│   ├── Helpers.php          # route_is(), notify()
│   └── ActivityHelper.php   # activity_log() global helper
├── Events/
│   └── PurchaseOutStock.php # Fires when stock drops to ≤ 5
└── Notifications/
    └── StockAlertNotification.php

database/
├── migrations/              # 14 migrations, cleanly ordered
└── seeders/

resources/views/admin/
├── layouts/app.blade.php    # Master layout
├── includes/                # Header + Sidebar partials
├── dashboard.blade.php      # Main dashboard
├── purchases/               # Purchase CRUD + reports
├── products/                # Product CRUD + expired + outstock
├── sales/                   # Sale CRUD + reports
├── suppliers/               # Supplier CRUD
├── users/                   # User CRUD + profile
├── roles/                   # Role CRUD
├── activity/                # Activity log
└── components/              # Reusable Blade components (alerts, modals)

public/assets/
├── css/style.css            # Template base styles
├── css/custom.css           # Design overrides (CSS custom properties)
└── plugins/                 # DataTables, Chart.js, SweetAlert2, Select2, pdfmake, jszip
```

### Key Design Decisions

- **Purchase ≠ Product** — `Purchase` is the inventory/medicine record (supplier, cost, stock, expiry). `Product` is the retail configuration on top (selling price, discount). This separation lets the same medicine have different selling configurations.
- **Eager loading everywhere** — All DataTable queries use `with([...])` to prevent N+1 queries.
- **Transactions for stock mutations** — Every sale create/update uses `DB::transaction()` to keep inventory and sales records atomic.
- **Form Requests** — All validation lives in `app/Http/Requests/` rather than inline in controllers.
- **Model scopes** — Business queries (`expired`, `outOfStock`, `lowStock`) are encapsulated as named scopes, not repeated across controllers.

---

## Installation

### Prerequisites
- PHP >= 8.0
- Composer
- MySQL 8+
- Node.js (optional, for asset compilation)

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/t-med1/pharmacy-management.git
cd pharmacy-management

# 2. Install PHP dependencies
composer install

# 3. Copy environment file and configure it
cp .env.example .env

# 4. Edit .env — set your database credentials
#    DB_DATABASE=pharmacy
#    DB_USERNAME=root
#    DB_PASSWORD=your_password

# 5. Generate application key
php artisan key:generate

# 6. Run migrations
php artisan migrate

# 7. (Optional) Seed with sample data
php artisan db:seed

# 8. Create storage symlink
php artisan storage:link

# 9. Start the development server
php artisan serve
```

Visit `http://localhost:8000/admin/register` to create your first admin account, then log in at `http://localhost:8000/admin/login`.

### Default Permissions Setup

After registering, use the **Access Control** section to:
1. Create permissions (e.g. `view-products`, `create-sale`, `destroy-purchase`, etc.)
2. Create a role (e.g. `Admin`) and assign all permissions to it
3. Assign the role to your user via **Users → Edit**

---

## Environment Variables

Key `.env` values to configure:

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | `PharmaManager` |
| `DB_DATABASE` | MySQL database name | `laravel` |
| `DB_USERNAME` | MySQL username | `root` |
| `DB_PASSWORD` | MySQL password | _(empty)_ |
| `MAIL_*` | Mail settings for password reset | _(configure per provider)_ |
| `FILESYSTEM_DRIVER` | Storage driver | `public` |

---

## Screenshots

> _Screenshots can be added to a `/docs/screenshots/` folder and linked here._

| Dashboard | Sales Report | Inventory |
|-----------|-------------|-----------|
| _(screenshot)_ | _(screenshot)_ | _(screenshot)_ |

---

## Roadmap

- [ ] REST API with Sanctum token authentication for mobile clients
- [ ] Multi-branch / multi-warehouse support
- [ ] Email alerts for low stock (configurable threshold)
- [ ] Barcode scanning support
- [ ] Customer management and prescription tracking
- [ ] Dark mode

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you'd like to change.

---

## License

This project is open-source under the [MIT License](LICENSE).

---

## Contact

Built by [Telaj](mailto:telaj.contact@gmail.com) — feel free to reach out for questions or feedback.
