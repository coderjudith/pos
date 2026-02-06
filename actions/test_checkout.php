<?php
// Simulate what checkout.php does
session_start();

// Start output buffering to capture all output
ob_start();

// Include your files
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Simulate POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = ['cash' => 100];
$json_input = json_encode($input);

// Set the input for php://input
$temp = fopen('php://memory', 'r+');
fwrite($temp, $json_input);
rewind($temp);

// Override php://input stream
stream_wrapper_register('test', 'CheckoutTestStream');
class CheckoutTestStream {
    public $position = 0;
    public $data;
    
    public function stream_open($path, $mode, $options, &$opened_path) {
        global $json_input;
        $this->data = $json_input;
        return true;
    }
    
    public function stream_read($count) {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    
    public function stream_eof() {
        return $this->position >= strlen($this->data);
    }
}

// Include the actual checkout file
include 'checkout.php';

// Get all output
$output = ob_get_clean();

echo "<h2>Raw Output:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode as JSON
echo "<h2>JSON Decode Attempt:</h2>";
$decoded = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Valid JSON!<br>";
    print_r($decoded);
} else {
    echo "JSON Error: " . json_last_error_msg() . "<br>";
    echo "This means PHP errors are being output.";
}
?>