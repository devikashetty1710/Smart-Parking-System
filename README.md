# 🚗 Smart Parking Management System

A full-stack, secure, and visually stunning web application designed to automate parking slot reservations. This project features a state-of-the-art **glassmorphic dashboard**, real-time parking space search, interactive credit card checkout, secure relational database transaction flows, and automated HTML invoice invoicing utilizing a custom-built SMTP socket-level mail client in pure PHP.

---

## 🌟 Key Features

* **💎 Premium Glassmorphic User Interface:** Clean SaaS visual design utilizing frosted-glass panels, rich backdrop-blur filters, dynamic drop shadows, and soft glowing linear gradients.
* **📬 Pure-PHP SMTP Client Service:** A highly optimized, custom-engineered SMTP socket-level mail client built directly over standard PHP sockets (`fsockopen`/`stream_socket_client`) with TLS encryption handshakes. Automatically generates and transmits multi-part, beautifully formatted HTML welcome letters and booking permit invoices.
* **💳 Interactive Payment Checkout:** An elegant card payment checkout screen featuring real-time card spacing, expiration limits, input filters, and transaction logging.
* **🗄️ Relational Database Integrity:** Robust database design using transactional execution blocks to guarantee that parking space states (`Available`/`reserved`) synchronize perfectly with reservation updates and payments table registers.
* **🖥️ Dynamic User Dashboard:** Live counters displaying active bookings, total spent, and history lists, with modular profiles and responsive mobile layouts.

---

## 🛠️ Technology Stack

* **Frontend:** HTML5, Vanilla CSS3 (Custom variables, HSL grids, CSS transitions), JavaScript (ES6, regular-expression credit-card masking).
* **Backend:** Native PHP (Object-oriented session handlers, prepared statement SQL bindings, socket transport).
* **Database:** MySQL (Relational tables, foreign key constraints).
* **Environment:** XAMPP Local Web Server.

---

## 📂 Project Structure

```text
dbms_final/
├── config/
│   ├── database.php            # MySQL Database connection configuration
│   ├── mail.php.example        # SMTP Configuration template (leaks no secrets!)
│   └── setup_database.php      # Database creation and seeder runner script
├── css/
│   └── styles.css              # Main visual styling (Glassmorphism & animations)
├── includes/
│   ├── Mailer.php              # Pure PHP SMTP Socket client & HTML templates
│   ├── navbar.php              # Responsive Navigation component
│   └── footer.php              # Standard page footer
├── user/
│   ├── dashboard.php           # Elegant User portal & quick links
│   ├── reservations.php        # Detailed reservation log & filter panels
│   └── process_booking.php     # Booking validation & payment redirector
├── index.php                   # Public landing homepage
├── all_spaces.php              # Live parking space grids
├── payment.php                 # Card checkout form & transaction processor
├── setup_database.sql          # Primary database schema & seeder insert scripts
└── README.md                   # Project documentation
```

---



## 🚀 Installation & Local Setup

### 1. Prerequisites
Ensure you have **XAMPP** (containing Apache and MySQL Server with PHP 8+) installed on your local computer.

### 2. Clone the Repository
Clone this repository directly into your local XAMPP directory:
```bash
cd "C:\xampp\htdocs"
git clone https://github.com/devikashetty1710/Smart-Parking-System.git dbms_final
```

### 3. Initialize the Database
1. Turn on **Apache** and **MySQL** inside your XAMPP Control Panel.
2. In your browser, open the URL below to automatically create and seed the database tables:
   👉 `http://localhost/dbms_final/setup_database.php`

### 4. Setup Email Server Configurations (Optional)
To send real, live transactional emails to customers' inboxes:
1. Rename `config/mail.php.example` to `config/mail.php`.
2. Turn `SEND_REAL_EMAILS` to `true` and fill in your SMTP credentials:
   ```php
   define('SEND_REAL_EMAILS', true);
   define('SMTP_USER', 'your-gmail@gmail.com');
   define('SMTP_PASS', 'your-app-password');
   ```

### 5. Launch the Application!
Go to your browser and run:
👉 **`http://localhost/dbms_final/index.php`**

---

