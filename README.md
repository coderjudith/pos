# 🏪 POS System - Point of Sale Software

A complete, beginner-friendly Point of Sale system built with plain PHP, MySQL, and JavaScript. Designed for small to medium retail stores in the Philippines.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

## ✨ Features

### 🔐 Authentication & Security

- **Role-based access control** (Admin/Cashier)
- **Secure password hashing** with bcrypt
- **Session-based authentication**
- **PDO prepared statements** to prevent SQL injection

### 💰 Cashier POS Interface

- **Barcode scanning support** (keyboard input simulation)
- **Real-time cart management** with session storage
- **Quantity adjustment** (increase/decrease/remove)
- **Automatic VAT calculation** (12% for Philippines)
- **Cash input with change calculation**
- **One-click checkout process**

### 📦 Product Management (Admin)

- **CRUD operations** for products
- **Barcode, name, price, stock management**
- **Active/inactive product status**
- **Stock quantity tracking**

### 📊 Sales & Reporting

- **Daily sales reports**
- **Date range filtering**
- **Best-selling products analysis**
- **Transaction history**
- **Receipt generation** (thermal printer optimized)

### 🧾 Receipt System

- **80mm thermal printer optimized**
- **Auto-print dialog** on completion
- **Philippine Peso (₱) formatting**
- **VAT breakdown** (12% calculation)
- **Professional receipt design**

## 🚀 Installation Guide

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + PHP + MySQL)
- Web browser (Chrome, Firefox, Edge)
- Text editor (VS Code, Sublime, Notepad++)

### Step 1: Setup XAMPP

1.  Download and install XAMPP
2.  Start Apache and MySQL from XAMPP Control Panel
3.  Open phpMyAdmin: `http://localhost/phpmyadmin`

### Step 2: Create Database

1.  Create a new database named `pos_db`
2.  Import the SQL schema:

-- Run this in phpMyAdmin SQL tab

    `CREATE DATABASE pos_db;
    			USE pos_db;

-- Tables will be created automatically on first run
-- Or import the provided pos_db.sql file

### Step 3: Install POS System

1.  Download/clone the POS files
2.  Extract to: `C:\xampp\htdocs\pos\` (Windows) or `/opt/lampp/htdocs/pos/` (Linux)
3.  Folder structure should be:
    - pos/
      - index.php
      - dashboard.php
      - cashier/
      - admin/
      - actions/
      - includes/

### Step 4: Configure Database

Edit `/includes/db.php`:

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'pos_db');
    define('DB_USER', 'root');      // Default XAMPP username
    define('DB_PASS', '');          // Default XAMPP password (empty)

### Step 5: Set Up Users

Run this SQL in phpMyAdmin:

-- Insert default users (passwords will be hashed)

    INSERT INTO users (username, password, role) VALUES
    ('admin', '$2y$10$YourHashedPasswordHere', 'admin'),
    ('cashier', '$2y$10$YourHashedPasswordHere', 'cashier');

To generate password hashes, create a temporary file:

    <?php
    echo password_hash('admin123', PASSWORD_DEFAULT);
    echo password_hash('cashier123', PASSWORD_DEFAULT);
    ?>

## 📁 File Structure

    pos/
      - index.php                  # Login page
      - dashboard.php              # Main dashboard
      - cashier/
    - pos.php                  # POS interface
      - admin/
    - products.php             # Product management
    - reports.php              # Sales reports
      - actions/
    - checkout.php             # Process sales
    - receipt.php              # Generate receipts
    - add_to_cart.php          # Cart operations
    - update_cart.php
    - remove_from_cart.php
    - get_cart.php
    - clear_cart.php
    - logout.php
      - includes/
    - db.php                   # Database connection
    - auth.php                 # Authentication functions
    - config.php               # Configuration (optional)
      - assets/                     # CSS, JS, images

## 👥 User Roles & Credentials

### Default Accounts

| Role    | Username | Password   | Access             |
| ------- | -------- | ---------- | ------------------ |
| Admin   | admin    | admin123   | Full system access |
| Cashier | cashier  | cashier123 | POS interface only |

### Permissions

- **Admin**: Full access (products, reports, POS, settings)
- **Cashier**: POS interface, checkout, receipts

## 🧪 Testing Without Hardware

### Without Barcode Scanner

- Click products directly in POS interface
- Type barcode manually and press Enter
- Use test barcodes: `8901234567890`, `8901234567891`

### Without Thermal Printer

- Receipt opens in browser
- Save as PDF: Select "Microsoft Print to PDF"
- Print preview: Ctrl+P to check formatting
- Cancel print dialog if no printer

## 🧮 VAT Calculation

The system automatically calculates **12% VAT** (Philippine standard):
Subtotal: ₱100.00
VAT (12%): ₱12.00
Total: ₱112.00

## 🖨️ Thermal Printer Setup

### Recommended Printers

- EPSON TM Series
- Star Micronics
- Citizen Thermal Printers

### Printer Settings

Paper Width: 80mm
Font: Courier New
Font Size: 12px
Margins: None/Minimal
Orientation: Portrait

### Browser Print Settings

- **Chrome/Edge**: More settings → Paper size: 80mm × Auto
- **Firefox**: Page Setup → Paper size: Custom (80mm)
- **All**: Set margins to "None" or "Minimum"

## 🔧 Configuration

### Customize Store Details

Edit `/actions/receipt.php`:

      **Store information**
        Store Name: 'Your Store Name'
        Address: 'Your Address'
        Phone: 'Your Phone Number'
        VAT Number: 'Your TIN'

### Change Currency

Edit `/includes/config.php` (if created):

    define('CURRENCY_SYMBOL', '₱');  // Philippine Peso
    define('CURRENCY_CODE', 'PHP');

### Adjust VAT Rate

Edit `/cashier/pos.php`:

    //calculateTotals() function
    const vatRate = 0.12;  // Change to your tax rate

## 🐛 Troubleshooting

### Common Issues & Solutions

| Issue                        | Solution                                          |
| ---------------------------- | ------------------------------------------------- |
| "404 Not Found"              | Check files are in `htdocs/pos/`                  |
| "Database connection failed" | Verify XAMPP is running, check db.php credentials |
| "Session errors"             | Clear browser cache, restart Apache               |
| "Receipt not printing"       | Check browser print settings, try PDF export      |
| "Cart not updating"          | Check JavaScript console (F12) for errors         |

### Debug Mode

Enable debugging by editing `/includes/db.php`:

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

## 📈 Future Enhancements

Planned features:

- Inventory alerts (low stock notifications)
- Customer management
- Multiple payment methods (GCash, credit card)
- Employee time tracking
- Sales analytics dashboard
- Export reports to Excel/PDF
- Multi-store support
- Offline mode capability

## 🔒 Security Notes

- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ Session-based authentication
- ✅ Role-based access control

**Important**: Delete test files after setup:

- `setup_password.php`, `test_*.php`, `update_currency.php`

## 🤝 Contributing

1.  Fork the repository
2.  Create a feature branch
3.  Commit changes
4.  Push to the branch
5.  Create a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Developer

Built with ❤️ for Philippine retail businesses.

## 🆘 Support

For issues or questions:

1.  Check the Troubleshooting section
2.  Review browser console (F12)
3.  Check XAMPP error logs
4.  Ensure all prerequisites are installed

---

**Happy Selling!** 🛒💰

_Last Updated: February 2026_  
_Version: 1.0.0_
