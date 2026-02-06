<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get sale ID
$sale_id = $_GET['sale_id'] ?? 0;

if (!$sale_id) {
    echo json_encode(['success' => false, 'message' => 'No sale ID provided']);
    exit();
}

try {
    // Get sale details
    $stmt = $pdo->prepare("
        SELECT s.*, u.username as cashier_name 
        FROM sales s 
        JOIN users u ON s.cashier_id = u.id 
        WHERE s.id = ? AND s.cashier_id = ?
    ");
    $stmt->execute([$sale_id, $_SESSION['user_id']]);
    $sale = $stmt->fetch();
    
    if (!$sale) {
        echo json_encode(['success' => false, 'message' => 'Sale not found or access denied']);
        exit();
    }
    
    // Get sale items
    $stmt = $pdo->prepare("
        SELECT si.*, p.name 
        FROM sale_items si 
        JOIN products p ON si.product_id = p.id 
        WHERE si.sale_id = ?
        ORDER BY si.id
    ");
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll();
    
    // Calculate totals
    $subtotal = $sale['total_amount'] / 1.12; // Assuming 12% VAT
    $vat = $sale['total_amount'] - $subtotal;
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'receipt_id' => str_pad($sale['id'], 6, '0', STR_PAD_LEFT),
        'sale_time' => date('g:i A', strtotime($sale['sale_date'])),
        'cashier' => $sale['cashier_name'],
        'items' => $items,
        'subtotal' => $subtotal,
        'vat' => $vat,
        'total' => $sale['total_amount'],
        'cash' => $sale['cash'],
        'change' => $sale['change_amount']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>