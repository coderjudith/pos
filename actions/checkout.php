<?php
// COMPLETE checkout.php with database support

// Turn off error display
error_reporting(0);
ini_set('display_errors', 0);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Include database
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Handle GET requests (for testing)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'message' => 'Checkout API is working. Use POST for checkout.',
        'session_user' => $_SESSION['username'] ?? 'Unknown'
    ]);
    exit();
}

// Handle POST requests (real checkout)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$cash = floatval($input['cash'] ?? 0);

// Validate cart
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    if (isset($item['subtotal'])) {
        $total += floatval($item['subtotal']);
    }
if (VAT_ENABLED) {
    $vat_rate = VAT_RATE;
    $subtotal = $total;  // Amount before tax
    $vat_amount = $subtotal * $vat_rate;
    $grand_total = $subtotal + $vat_amount;
} else {
    $subtotal = $total;
    $vat_amount = 0;
    $grand_total = $total;
}

    }

// Validate cash
if ($cash < $total) {
    echo json_encode(['success' => false, 'message' => 'Insufficient cash']);
    exit();
}

$change = $cash - $total;

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // 1. Insert sale record
    $stmt = $pdo->prepare("
    INSERT INTO sales (cashier_id, subtotal, vat_amount, total_amount, cash, change_amount) 
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $_SESSION['user_id'],
    $subtotal,
    $vat_amount,
    $grand_total,  // This is now total with VAT
    $cash,
    $change
]);
    
    $sale_id = $pdo->lastInsertId();
    
    // 2. Insert sale items and update stock
    foreach ($_SESSION['cart'] as $barcode => $item) {
        // Insert sale item
        $stmt = $pdo->prepare("
            INSERT INTO sale_items (sale_id, product_id, price, qty, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $sale_id,
            $item['product_id'],
            $item['price'],
            $item['qty'],
            $item['subtotal']
        ]);
        
        // Update product stock
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock_qty = stock_qty - ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$item['qty'], $item['product_id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Save sale_id to session for receipt
    $_SESSION['last_sale_id'] = $sale_id;
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Return success
    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id,
        'message' => 'Checkout successful!',
        'total' => $total,
        'cash' => $cash,
        'change' => $change
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>