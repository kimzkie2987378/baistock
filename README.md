# BaiStock: Inventory and Sales Monitoring System

**BaiStock** is an inventory, storage, and sales monitoring system designed for neighborhood stores, sari-sari stores, and small retail shops. It replaces manual notebook and spreadsheet recording with a secure, automated database solution that can run on a single computer or local network without requiring an active internet connection.

---

## 🎨 UI Design & Specifications
- **Design Inspiration**: Faithfully implemented based on the provided BaiStock UI mockups (Login & Main Dashboard).
- **Branding**: Official purple color palette (`#7C3AED`, `#6D28D9`, `#190933`).
- **Icons**: Sourced from [lucide.dev](https://lucide.dev) and bundled locally for offline use.
- **Charts**: Interactive Sales Overview Bar Chart powered by Chart.js (Daily, Weekly, and Monthly views).

---

## 👥 User Roles & Access Control
The system features strict Role-Based Access Control (RBAC) as specified in the SRS:
1. **Store Owner** (`ROLE_OWNER`): Highest operational access. Manages products, pricing, stock levels, reviews low-stock reports, and views all financial insights.
2. **Store Staff / Cashier** (`ROLE_STAFF`): Operational user. Records sales (POS), performs stock-ins, checks product availability, prints receipts. Unauthorized actions (e.g. deleting products or managing accounts) are blocked.
3. **System Administrator** (`ROLE_ADMIN`): Full system control, user account provisioning, technical database backup and restore.

### 🔑 Pre-Seeded Demo Accounts
| Role | Username | Password | Access Capabilities |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin` | `admin123` | Full access, user accounts, system settings, database |
| **Store Owner** | `owner` | `owner123` | Products, stock-ins, reports, sales, settings |
| **Store Staff** | `staff` | `staff123` | Point of Sale, stock-in, inventory lookup, receipts |

---

## 🚀 Key Modules & Features

### 1. Dashboard (`/`)
- **Metric Cards**:
  - Total Products: **24** (`+2 this week`)
  - Total Stock Units: **1,248** (`+56 this week`)
  - Low Stock Items: **5** (`+2 this week`)
  - Total Sales: **₱ 8,540.00** (`+12% this week`)
- **Sales Overview**: Interactive purple bar chart with switchable Daily, Weekly, and Monthly modes.
- **Low Stock Alert**: Real-time list of items at or below reorder level (e.g., Cola 330ml, Chips, Water, Bread, Instant Noodles) with instant restock links.
- **Recent Sales**: Live list of recent transactions with receipt totals in ₱ and cashier details.
- **Quick Actions**: 6 shortcuts for Add Product, Record Sale, Stock In, View Reports, Manage Users, and Settings.

### 2. Product Management (`/products`)
- Catalog listing with product thumbnails, SKU codes, categories, pricing, stock quantities, and status badges.
- Category filters (Beverages, Snacks, Bakery, Pantry, Canned Goods, Personal Care, etc.).
- Stock level filters (All, Low Stock, Out of Stock, In Stock).
- Add Product (`/products/new`) with automatic or manual SKU generation and data validation.
- Edit Product (`/products/{id}/edit`) and Owner-protected deletion (`/products/{id}/delete`).
- Export complete inventory catalog to CSV (`/products/export/csv`).

### 3. Inventory Management (`/inventory`)
- Real-time stock status monitoring.
- **Stock-In** (`/inventory/stock-in`): Record incoming stock units with supplier/batch notes.
- **Stock Adjustment** (`/inventory/adjust/{id}`): Correct discrepancies following physical count.
- **Stock Movement Audit Log** (`/inventory/history`): Immutable audit trail tracking all IN, OUT, and ADJUST operations.

### 4. Sales Recording & POS (`/sales/record`)
- Fast Point of Sale interface designed to complete sales in under 1 minute.
- Real-time product search and category filtering.
- Interactive cart with quantity increment/decrement controls.
- Automatic calculation of subtotal, total in ₱, cash tendered, and change.
- Payment method support: Cash, GCash, Maya.
- **Automatic Stock Deduction**: Deducts stock upon checkout and logs the movement automatically.

### 5. Receipts & Invoices (`/receipts`)
- Archive of all sales transactions.
- Printable thermal receipt view (`/receipts/{id}`) optimized for receipt printers (`window.print()`), complete with store details, itemized breakdown, tax calculation, change, barcode, and footer note.

### 6. Delivery Tracking (`/delivery`)
- Track incoming restock shipments from suppliers (San Miguel, Universal Robina, Nestle, etc.).
- Tracking numbers, expected delivery dates, invoice costs, and delivery notes.
- "Mark Received" action to log stock arrivals.

### 7. Reports & Analytics (`/reports`)
- Store Revenue Breakdown and Accumulated Sales.
- Total Inventory Retail Valuation in Philippine Peso (₱).
- Top 5 Best-Selling Products by units sold and revenue generated.
- Low-Stock Reorder Recommendations with suggested reorder quantities.
- Print-friendly layout and CSV export.

### 8. User Management (`/users`)
- Restricted to Admin and Owner roles.
- Create new user accounts, assign roles, and toggle active/inactive status.

### 9. Settings & Database Tools (`/settings`)
- Store profile configuration (Store Name, Address, Contact, Currency ₱, Default Reorder Level).
- One-click JSON backup export (`/settings/backup/export`).
- One-click database reset and sample data re-seed (`/settings/reset-data`).

---

## 💻 Technical Stack & Local Deployment
- **Framework**: Symfony 8.1 (PHP 8.5)
- **Database**: SQLite (Zero configuration, offline file storage in `var/baistock.db`)
- **ORM**: Doctrine ORM with migrations and entities
- **Styling**: Tailwind CSS & custom styling matching the design mockups
- **Icons**: Lucide Icons from [lucide.dev](https://lucide.dev) (locally bundled)
- **Charts**: Chart.js (locally bundled)

### Starting the Application
Double-click `run.bat` or execute in terminal:
```bash
php -S localhost:8088 -t public
```
Then open your web browser at:
[http://localhost:8088](http://localhost:8088)
