<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$cash = floatval($input['cash'] ?? 0);

// Validate cart
if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['subtotal'];
}

// Validate cash
if ($cash < $total) {
    echo json_encode(['success' => false, 'message' => 'Insufficient cash']);
    exit();
}

$change = $cash - $total;

try {
    $pdo->beginTransaction();
    
    // Insert sale
    $stmt = $pdo->prepare("
        INSERT INTO sales (cashier_id, total_amount, cash, change_amount) 
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$stmt->execute([
        $_SESSION['user_id'],
        $total,
        $cash,
        $change
    ])) {
        throw new Exception('Failed to insert sale');
    }
    
    $sale_id = $pdo->lastInsertId();
    
    // Insert sale items and update stock
    foreach ($_SESSION['cart'] as $barcode => $item) {
        // Insert sale item
        $stmt = $pdo->prepare("
            INSERT INTO sale_items (sale_id, product_id, price, qty, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if (!$stmt->execute([
            $sale_id,
            $item['product_id'],
            $item['price'],
            $item['qty'],
            $item['subtotal']
        ])) {
            throw new Exception('Failed to insert sale item');
        }
        
        // Update product stock
        // Update product stock
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock_qty = stock_qty - ? 
            WHERE id = ? AND stock_qty >= ?
        ");
        
        if (!$stmt->execute([$item['qty'], $item['product_id'], $item['qty']])) {
            throw new Exception('Insufficient stock for product ID: ' . $item['product_id']);
        }
    }
    
    $pdo->commit();
    
    // Clear cart from session (but keep it for display until page reload)
    $saved_cart = $_SESSION['cart']; // Save for response if needed
    $_SESSION['last_sale_id'] = $sale_id;
    
    echo json_encode([
        'success' => true, 
        'sale_id' => $sale_id,
        'total' => $total,
        'cash' => $cash,
        'change' => $change
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Checkout error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()]);
}
?>