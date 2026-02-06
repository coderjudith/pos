<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $barcode = $data['barcode'] ?? '';
    $qty = intval($data['qty'] ?? 0);
    
    if (!isset($_SESSION['cart'][$barcode])) {
        echo json_encode(['success' => false, 'message' => 'Item not in cart']);
        exit();
    }
    
    // Check stock availability
    $stmt = $pdo->prepare("SELECT stock_qty FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch();
    
    if ($product['stock_qty'] < $qty) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }
    
    // Update quantity
    $_SESSION['cart'][$barcode]['qty'] = $qty;
    $_SESSION['cart'][$barcode]['subtotal'] = 
        $_SESSION['cart'][$barcode]['price'] * $qty;
    
    echo json_encode(['success' => true]);
}
?>