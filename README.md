# 🛠️ UstaMarket - Hardware & Household E-Commerce Platform

> A full-stack, secure e-commerce web application and role-based administrative dashboard tailored for hardware and household goods management.

---

## 🌟 Features

### 👤 Role-Based Administration Panel (`admin.php`)
* **Director (Direktor):** Full system access, financial monitoring, technical user support (password resets), and the ability to register/delete other administrators (Director, Accountant, Warehouse).
* **Accountant (Mühasib):** Access to financial reports, inventory valuation, profit margins (purchase vs. selling price), employee registry, and salary management.
* **Warehouse Manager (Anbardar):** Stock management, adding new products, updating prices and quantities without viewing sensitive financial earnings.

### 🛒 Customer Storefront (`index.php`)
* Modern, responsive UI built with **Tailwind CSS**.
* Interactive product catalog with search and category filtering.
* Shopping cart and dynamic checkout system.
* Secure user authentication (Login/Registration with profile management).

---

## 🗄️ Database Structure (`MySQL`)
The project utilizes relational database tables structured as follows:
* `users` — Customer accounts and credentials.
* `admins` — Administrative staff with role definitions (`position`).
* `products` — Inventory items featuring both `purchase_price` and `price` (selling price) for margin tracking.
* `categories` — Product categorization.
* `employees` — Staff directory with FIN codes, identity details, and monthly salaries.
* `orders` — Customer transactions and sales analytics.

---

## 🚀 Getting Started

### Prerequisites
* Local server environment like **XAMPP**, **WAMP**, or **MAMP** (PHP 8.0+ and MySQL).

### Installation Steps

1. **Clone the repository:**
   ```bash
