<?php
// Store Configuration
define('STORE_NAME', 'MANILA MART');
define('STORE_ADDRESS', '123 Rizal Avenue, Manila');
define('STORE_PHONE', '(02) 8123-4567');
define('STORE_VAT', 'TIN: 123-456-789-000');

// Currency Configuration (Philippine Peso)
define('CURRENCY_SYMBOL', '₱');
define('CURRENCY_CODE', 'PHP');
define('CURRENCY_NAME', 'Philippine Peso');

// Date/Time Format (Philippine format)
define('DATE_FORMAT', 'm/d/Y');  // or 'd/m/Y' based on preference
define('TIME_FORMAT', 'h:i A');
define('DATETIME_FORMAT', DATE_FORMAT . ' ' . TIME_FORMAT);

// Receipt Configuration
define('RECEIPT_WIDTH', '80mm');
define('RECEIPT_HEADER', 'SALAMAT PO! COME AGAIN!');
define('RECEIPT_FOOTER', 'Please present receipt for returns within 7 days');

// VAT Configuration for Philippines (12%)
define('VAT_RATE', 0.12);        // 12% VAT
define('VAT_NAME', 'VAT');       // Tax display name
define('VAT_ENABLED', true);     // Set to false to disable VAT

// Business Hours
define('OPEN_TIME', '8:00 AM');
define('CLOSE_TIME', '9:00 PM');
?>