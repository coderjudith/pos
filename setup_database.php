<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pos_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<h2>POS System Setup</h2>";
    
    // Generate hashed passwords
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $cashier_hash = password_hash('cashier123', PASSWORD_DEFAULT);
    
    echo "<p>Generated password hashes...</p>";
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$admin_hash]);
    echo "<p>✓ Admin password updated</p>";
    
    // Update cashier password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'cashier'");
    $stmt->execute([$cashier_hash]);
    echo "<p>✓ Cashier password updated</p>";
    
    echo "<h3>✅ Setup Complete!</h3>";
    echo "<p>Default passwords have been set:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: admin, password: admin123</li>";
    echo "<li><strong>Cashier:</strong> username: cashier, password: cashier123</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
    
    echo "<hr>";
    echo "<p><strong>Note:</strong> Delete this file after setup for security.</p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 20px; background: #fee; border: 1px solid #fcc;'>";
    echo "<h3>Error: Database Connection Failed</h3>";
    echo "<p>Please ensure:</p>";
    echo "<ol>";
    echo "<li>XAMPP is running (Apache & MySQL)</li>";
    echo "<li>Database 'pos_db' exists</li>";
    echo "<li>Database tables are created</li>";
    echo "<li>Default users are inserted (run the SQL from pos_db.sql first)</li>";
    echo "</ol>";
    echo "<p>Error details: " . $e->getMessage() . "</p>";
    echo "</div>";
    
    // Show SQL to create database and tables
    echo "<h3>SQL to create database and tables:</h3>";
    echo "<pre>";
    echo "CREATE DATABASE IF NOT EXISTS pos_db;
USE pos_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, role) VALUES 
('admin', 'temporary', 'admin'),
('cashier', 'temporary', 'cashier');";
    echo "</pre>";
}
?>