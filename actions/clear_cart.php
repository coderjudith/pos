<?php
session_start();

header('Content-Type: application/json');

// Clear the cart from session
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

echo json_encode(['success' => true]);
?>