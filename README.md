# Online Lorries Hiring System (OLHS)

A premium, modern web application designed to connect lorry owners with customers requiring cargo transportation services across Tanzania. OLHS provides a seamless, highly interactive experience for booking, managing, and tracking transportation requests.

## 🚀 Features

- **Customer Portal**:
  - Browse and search available lorries using a refined horizontal layout.
  - Interactive booking creation with custom pickup/delivery points and dates.
  - View real-time booking status (Pending, Accepted, In-Transit, Completed, Cancelled).
  - Secure payment checkout page.
- **Lorry Owner Portal**:
  - List and manage fleet details (load capacity, pricing, regional availability).
  - Approve or decline incoming booking requests.
  - Track payment status and trip history.
- **Interactive UI/UX**:
  - Sleek glassmorphic navigation bar and sidebar designs.
  - Fully responsive mobile experience with slide-out sidebar drawers triggered by standard hamburger toggles.
  - Premium micro-interactions: sidebar link hover translations, animated dashboard stats counters.
  - Unified premium typography using the `Plus Jakarta Sans` font family.

## 🛠️ Technology Stack

- **Backend**: PHP (MVC structure), Composer dependency manager.
- **Database**: MySQL / SQLite.
- **Frontend**: Standard semantic HTML5, custom Vanilla CSS3 (Design Tokens, flex/grid utilities), Vanilla JavaScript (no heavy frameworks for optimal loading speed).
- **Icons**: FontAwesome 6.4.0.

## 📦 Installation & Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/domycrucial/lory-hiring.git
   cd lory-hiring
   ```

2. **Install Dependencies**:
   Ensure you have [Composer](https://getcomposer.org/) installed:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   Copy `.env.example` to `.env` and fill in your database credentials:
   ```bash
   cp .env.example .env
   ```

4. **Run Database Migrations**:
   Run the database setup scripts located in the `database/` folder or import the schema.

5. **Start Local Development Server**:
   You can serve the application using PHP's built-in server:
   ```bash
   php serve.php
   ```
   Or load the project directory within a local dev environment like **Laragon** (accessible via `http://lory-hiring.test/`).

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
