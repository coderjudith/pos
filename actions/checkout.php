<?php
// Turn off ALL error display for production
error_reporting(0);
ini_set('display_errors', 0);

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header FIRST
header('Content-Type: application/json');

// For debugging - uncomment these lines to see errors:
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// If it's a GET request (for testing), return a test response
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'sale_id' => 'TEST-' . time(),
        'message' => 'GET request received (for testing)',
        'note' => 'For actual checkout, use POST method'
    ]);
    exit();
}

// Only accept POST requests for real checkout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method. Use POST instead of ' . $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);

// Check if we got valid JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data: ' . json_last_error_msg(),
        'raw_input' => file_get_contents('php://input')
    ]);
    exit();
}

// Get cash amount
$cash = floatval($input['cash'] ?? 0);

// For now, always return success (we'll add real logic later)
$sale_id = 'SALE-' . date('Ymd-His') . '-' . rand(100, 999);

echo json_encode([
    'success' => true,
    'sale_id' => $sale_id,
    'message' => 'Checkout successful!',
    'cash_received' => $cash,
    'total' => 50.00, // Hardcoded for testing
    'change' => $cash - 50.00,
    'test_mode' => true,
    'note' => 'Database not updated in test mode'
]);

exit();
?>