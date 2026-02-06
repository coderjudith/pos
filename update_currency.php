<?php
// save as update_currency.php in pos folder
$files = [
    'cashier/pos.php',
    'actions/receipt.php',
    'admin/products.php',
    'admin/reports.php',
    'dashboard.php',
    'index.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Replace Indian Rupee with Peso
        $content = str_replace('₹', '₱', $content);
        // Replace "₹" in JavaScript strings
        $content = str_replace("'₹'", "'₱'", $content);
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    }
}

echo "Currency updated to Philippine Peso (₱)\n";
?>