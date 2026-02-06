<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $barcode = $data['barcode'] ?? '';
    
    // Get product details
    $stmt = $pdo->prepare("SELECT id, name, price, stock_qty FROM products WHERE barcode = ? AND status = 1");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
        exit();
    }
    
    if ($product['stock_qty'] < 1) {
        echo json_encode(['success' => false, 'message' => 'Product out of stock']);
        exit();
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add or update item in cart
    if (isset($_SESSION['cart'][$barcode])) {
        $_SESSION['cart'][$barcode]['qty'] += 1;
    } else {
        $_SESSION['cart'][$barcode] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => 1
        ];
    }
    
    // Update subtotal
    $_SESSION['cart'][$barcode]['subtotal'] = 
        $_SESSION['cart'][$barcode]['price'] * $_SESSION['cart'][$barcode]['qty'];
    
    echo json_encode(['success' => true]);
}
?>