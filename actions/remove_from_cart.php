<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $barcode = $data['barcode'] ?? '';
    
    if (isset($_SESSION['cart'][$barcode])) {
        unset($_SESSION['cart'][$barcode]);
    }
    
    echo json_encode(['success' => true]);
}
?>