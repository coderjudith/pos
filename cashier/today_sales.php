<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('cashier');

// Get today's sales for this cashier
$stmt = $pdo->prepare("
    SELECT s.* 
    FROM sales s
    WHERE DATE(s.sale_date) = CURDATE() 
    AND s.cashier_id = ?
    ORDER BY s.sale_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$sales = $stmt->fetchAll();

// Calculate totals
$total_sales = 0;
$total_transactions = count($sales);
foreach ($sales as $sale) {
    $total_sales += $sale['total_amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Today's Sales</title>
    <style>
        /* Add styles similar to admin/reports.php */
    </style>
</head>
<body>
    <h1>Today's Sales Report</h1>
    <p>Cashier: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <p>Date: <?php echo date('d M Y'); ?></p>
    
    <div style="display: flex; gap: 20px; margin: 20px 0;">
        <div style="background: #d1fae5; padding: 15px; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold;">₹<?php echo number_format($total_sales, 2); ?></div>
            <div>Total Sales</div>
        </div>
        <div style="background: #dbeafe; padding: 15px; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold;"><?php echo $total_transactions; ?></div>
            <div>Transactions</div>
        </div>
    </div>
    
    <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Time</th>
                <th>Total</th>
                <th>Cash</th>
                <th>Change</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo date('H:i:s', strtotime($sale['sale_date'])); ?></td>
                <td>₹<?php echo number_format($sale['total_amount'], 2); ?></td>
                <td>₹<?php echo number_format($sale['cash'], 2); ?></td>
                <td>₹<?php echo number_format($sale['change_amount'], 2); ?></td>
                <td><a href="../actions/receipt.php?sale_id=<?php echo $sale['id']; ?>" target="_blank">View Receipt</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px;">
        <a href="../dashboard.php">Back to Dashboard</a> | 
        <a href="pos.php">New Sale</a>
    </p>
</body>
</html>