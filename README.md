# Online Lorries Hiring System (OLHS) — Tanzania Transport Marketplace

<div align="center">

![OLHS Banner](https://img.shields.io/badge/OLHS-Tanzania%20Lorry%20Hiring%20System-0033a0?style=for-the-badge)
![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-success?style=for-the-badge)

**A complete, production-ready web platform connecting Lorry & Cargo Owners with Customers across Tanzania.**  
Features instant **Tigo Pesa & Bank Deposit Simulation**, dynamic **Lipa kwa Simu QR Checkout**, and full **English / Swahili (EN/SW)** bilingual localization.

</div>

---

## 🌟 Key Features & Capabilities

### 1. 👥 Customer Portal & Digital Wallet (`/wallet`)
- **Integrated Customer Wallet (`Mkoba Wangu`)**:
  - Store funds securely in your wallet to settle all lorry booking transactions across Tanzania.
  - **Tigo Pesa Mobile Money Simulation (`Lipa kwa Simu`)**: Interactive presets (`100,000 TZS` to `5,000,000 TZS`), mobile number entry (`065X / 067X / 071X`), and simulated USSD PIN Push verification modal.
  - **Tanzania Bank Transfer Simulation**: Instant deposit simulation via CRDB, NMB, and NBC Bank.
  - **Transaction Ledger**: Complete history table listing account deposits and lorry booking settlements.
- **Hired Lorry Search & Booking**:
  - Browse available approved lorries with capacity, pricing per km, and regional availability.
  - Interactive booking creation with custom pickup/delivery addresses, date/time, and cargo notes.
  - Live distance calculation and automatic price quotation.
- **1-Click Booking Payment & QR Code Receipt**:
  - Clicking **Pay Now** on an accepted booking displays a payment modal highlighting the exact **Lorry Hired for Delivery** name (`<Lorry Name>`).
  - Supports direct settlement from **My Wallet Balance** or **Lipa kwa Simu QR Code** embedded with Lorry Name, Booking Ref, and Amount Due.

### 2. 🚛 Lorry Owner Portal
- **Fleet Management**: Register and manage lorries with capacity (tonnes), pricing per km, vehicle photos, and regional coverage.
- **Booking Management**: Review incoming transport requests and accept or decline bookings.
- **Earnings & Payout Ledger**: Monitor payouts received from completed trips, platform commission fees (`10%`), and submit withdrawal requests to mobile money (`M-Pesa / Airtel Money`).

### 3. 🛡️ Administrator Portal (`/admin`)
- **Fleet Verification**: Moderate and approve/reject newly registered lorries before they appear in public search.
- **System Oversight**: Monitor platform bookings, financial logs, commission earnings, and user accounts.
- **Configurable Settings**: Manage platform commission rates and system parameters.

---

## 🚀 Quick Start Guide (Setting Up on a New Computer)

### Prerequisites
- **PHP**: Version **8.1** or higher (with PDO MySQL, cURL, mbstring, and OpenSSL extensions).
- **Database**: MySQL 8.0+ or MariaDB 10.5+ (included in XAMPP, Laragon, MAMP, or LAMP stack).
- **Web Server**: Apache / Nginx or built-in PHP development server.

---

### Step-by-Step Installation

#### 1. Clone or Copy the Repository
Place the project inside your web server document root (e.g., `C:\xampp\htdocs\lory-hiring` on Windows):
```bash
git clone https://github.com/domycrucial/lory-hiring.git
cd lory-hiring
```

#### 2. Environment Setup
Copy the example environment configuration file to `.env`:
```bash
# On Windows (PowerShell / CMD)
copy .env.example .env

# On macOS / Linux / Git Bash
cp .env.example .env
```
Edit `.env` and verify your database connection parameters:
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=lory_hiring
DB_USER=root
DB_PASS=
```

#### 3. Database Initialization & Seeding
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or CLI) and create a database named `lory_hiring`:
   ```sql
   CREATE DATABASE lory_hiring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import the database schema (`database/schema.sql`):
   ```bash
   mysql -u root -p lory_hiring < database/schema.sql
   ```
3. Import the demo seed data (`database/seeds.sql`) for ready-to-test accounts:
   ```bash
   mysql -u root -p lory_hiring < database/seeds.sql
   ```

#### 4. Launch the Web Application
- **Option A (PHP Built-in Server)**:
  Run the included server script from the project root:
  ```bash
  php serve.php
  ```
  Then open your browser at **`http://localhost:8000`**.

- **Option B (XAMPP / Laragon / Apache)**:
  Navigate to **`http://localhost/lory-hiring`** (or your Laragon virtual host e.g. `http://lory-hiring.test`).

---

## 🔑 Demo & Test Accounts

All demo accounts created by `database/seeds.sql` share the password: **`Password@123`**

| Role | Name | Email Address | Password | Key Features to Test |
| :--- | :--- | :--- | :--- | :--- |
| **Customer** | Ali Mohamed Kimaro | `ali.customer@gmail.com` | `Password@123` | Access `/wallet`, simulate Tigo Pesa deposit, book a lorry, click **Pay Now** to view Hired Lorry QR & settle payment. |
| **Lorry Owner** | Juma Hassan Salim | `juma.owner@gmail.com` | `Password@123` | Manage lorries, accept incoming bookings, view earnings ledger, request withdrawal. |
| **Administrator** | Platform Admin | `admin@olhs.co.tz` | `Password@123` | Access `/admin`, verify lorries, view system logs, inspect platform bookings. |
| **Super Admin** | System Administrator | `superadmin@olhs.co.tz` | `Password@123` | Full system control and configuration. |

---

## 📁 System Architecture & Directory Structure

```text
lory-hiring/
├── app/
│   ├── controllers/      # MVC Controllers (HomeController, WalletController, PaymentController, etc.)
│   ├── models/           # Database Models (User, Lorry, Booking, Payment, Withdrawal, etc.)
│   ├── views/            # Frontend Views & Templates
│   │   ├── layouts/      # Header (with Navigation sidebar & Swahili toggle) and Footer
│   │   └── pages/        # Customer Wallet, Lorry Search, Bookings, Owner & Admin Dashboards
│   └── helpers/          # Sanitization, Formatting, Mail, and SMS helper functions
├── config/
│   ├── env.php           # .env parser
│   ├── constants.php     # System-wide configuration constants
│   └── db.php            # PDO Singleton database connection & auto-schema patcher
├── database/
│   ├── schema.sql        # Full MySQL database schema (14 tables)
│   └── seeds.sql         # Demo users, lorries, and test data
├── public/
│   ├── css/              # Vanilla CSS tokens & responsive glassmorphism styles
│   └── js/               # Frontend client interactions
├── index.php             # Central Router & Application Front Controller
├── serve.php             # Helper CLI script for PHP built-in server
└── README.md             # Project documentation
```

---

## 🌐 Localization (Bilingual EN / SW)
OLHS includes native support for both **English** and **Swahili (Kiswahili)**. Users can switch languages instantly at any time by clicking the **`EN | SW`** button in the top navigation bar.

---

## 📄 License
This project is open-source and licensed under the **MIT License**.
