<?php
// clear_cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

echo json_encode(['success' => true]);
?>