<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$sale_id = $_GET['sale_id'] ?? 0;

// Get sale details
$stmt = $pdo->prepare("
    SELECT s.*, u.username as cashier_name 
    FROM sales s 
    JOIN users u ON s.cashier_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch();

if (!$sale) {
    die('Sale not found');
}

// Get sale items
$stmt = $pdo->prepare("
    SELECT si.*, p.name 
    FROM sale_items si 
    JOIN products p ON si.product_id = p.id 
    WHERE si.sale_id = ?
");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        /* Thermal printer friendly receipt styles */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
            
            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
                font-family: 'Courier New', monospace;
                width: 80mm;
            }
            
            .no-print {
                display: none !important;
            }
            
            button {
                display: none !important;
            }
        }
        
        body {
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: white;
        }
        
        .receipt {
            border: 1px dashed #ccc;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .address {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .receipt-info {
            margin-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 3px 0;
            border-bottom: 1px dashed #ccc;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .total-section {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-row.grand {
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .print-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 20px auto;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }
        
        .back-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px auto;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <div class="store-name">POS STORE</div>
            <div class="address">123 Main Street, City</div>
            <div class="address">Phone: (123) 456-7890</div>
        </div>
        
        <div class="receipt-info">
            <div class="info-row">
                <span>Receipt #:</span>
                <span><?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($sale['sale_date'])); ?></span>
            </div>
            <div class="info-row">
                <span>Cashier:</span>
                <span><?php echo htmlspecialchars($sale['cashier_name']); ?></span>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['qty']; ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₹<?php echo number_format($sale['total_amount'], 2); ?></span>
            </div>
            <div class="total-row">
                <span>Tax:</span>
                <span>₹0.00</span>
            </div>
            <div class="total-row">
                <span>Cash:</span>
                <span>₹<?php echo number_format($sale['cash'], 2); ?></span>
            </div>
            <div class="total-row grand">
                <span>Change:</span>
                <span>₹<?php echo number_format($sale['change_amount'], 2); ?></span>
            </div>
        </div>
        
        <div class="footer">
            <div>Thank you for your purchase!</div>
            <div>Please keep this receipt for returns</div>
            <div>VAT No: 123456789</div>
        </div>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">Print Receipt</button>
        <a href="../cashier/pos.php" class="back-btn">Back to POS</a>
        <a href="../dashboard.php" class="back-btn" style="background: #6b7280;">Dashboard</a>
    </div>
</body>
</html>