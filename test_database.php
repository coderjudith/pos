<?php
require_once 'includes/db.php';

echo "<h2>Testing Database Connection</h2>";

// Test 1: Check connection
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✓ Database connection successful<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    exit();
}

// Test 2: Check tables exist
$tables = ['users', 'products', 'sales', 'sale_items'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✓ Table '$table' exists ($count records)<br>";
    } catch (Exception $e) {
        echo "✗ Table '$table' missing or error: " . $e->getMessage() . "<br>";
    }
}

// Test 3: Insert test sale
echo "<h3>Test Sale Insertion</h3>";
try {
    $pdo->beginTransaction();
    
    // Insert test sale
    $stmt = $pdo->prepare("INSERT INTO sales (cashier_id, total_amount, cash, change_amount) VALUES (1, 50.00, 100.00, 50.00)");
    $stmt->execute();
    $sale_id = $pdo->lastInsertId();
    
    echo "✓ Test sale inserted. ID: $sale_id<br>";
    
    // Check if we can retrieve it
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch();
    
    if ($sale) {
        echo "✓ Sale retrieval successful<br>";
    }
    
    $pdo->rollBack(); // Rollback test transaction
    echo "✓ Test transaction rolled back<br>";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "<br>";
}

echo "<h3><a href='cashier/pos.php'>Go to POS</a></h3>";
?>