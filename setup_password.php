<?php
// Generate password hashes for default users
echo "<h2>Password Hash Generator</h2>";

$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$cashier_hash = password_hash('cashier123', PASSWORD_DEFAULT);

echo "<h3>Generated Hashes:</h3>";
echo "<strong>Admin (password: admin123):</strong><br>";
echo "<code>$admin_hash</code><br><br>";

echo "<strong>Cashier (password: cashier123):</strong><br>";
echo "<code>$cashier_hash</code><br><br>";

echo "<h3>SQL Commands to Run:</h3>";
echo "<pre>";
echo "UPDATE users SET password = '$admin_hash' WHERE username = 'admin';\n";
echo "UPDATE users SET password = '$cashier_hash' WHERE username = 'cashier';";
echo "</pre>";

echo "<p>Copy the SQL commands above and run them in phpMyAdmin.</p>";
?>