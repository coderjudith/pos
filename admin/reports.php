<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('admin');

// Date filters
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get sales report
$stmt = $pdo->prepare("
    SELECT DATE(sale_date) as sale_day, 
           COUNT(*) as transactions,
           SUM(total_amount) as total_sales,
           SUM(cash) as total_cash,
           SUM(change_amount) as total_change
    FROM sales 
    WHERE DATE(sale_date) BETWEEN ? AND ?
    GROUP BY DATE(sale_date)
    ORDER BY sale_day DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_report = $stmt->fetchAll();

// Get best selling products
$stmt = $pdo->prepare("
    SELECT p.name, 
           SUM(si.qty) as total_qty,
           SUM(si.subtotal) as total_amount,
           p.barcode
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.sale_date) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_qty DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$best_sellers = $stmt->fetchAll();

// Get overall summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(total_amount) as total_sales,
        SUM(cash) as total_cash,
        SUM(change_amount) as total_change,
        AVG(total_amount) as avg_transaction
    FROM sales 
    WHERE DATE(sale_date) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f7fa;
        }
        
        .header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-buttons {
            display: flex;
            gap: 10px;
        }
        
        .nav-btn {
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .nav-btn.primary {
            background: #667eea;
            color: white;
        }
        
        .nav-btn.secondary {
            background: #6b7280;
            color: white;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="date"] {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            align-self: end;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .summary-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .report-table th {
            background: #f8fafc;
            text-align: left;
            padding: 15px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .report-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .report-table tr:hover {
            background: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .positive {
            color: #10b981;
        }
        
        .negative {
            color: #ef4444;
        }
        
        .product-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #4f46e5;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Sales Reports</div>
        <div class="nav-buttons">
            <a href="../dashboard.php" class="nav-btn secondary">Dashboard</a>
            <a href="products.php" class="nav-btn secondary">Products</a>
            <a href="../actions/logout.php" class="nav-btn" style="background: #ef4444; color: white;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">Sales Analytics & Reports</h1>
        
        <!-- Date Filter -->
        <div class="card">
            <h2 class="card-title">Filter Reports</h2>
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="<?php echo $start_date; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="<?php echo $end_date; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value">₹<?php echo number_format($summary['total_sales'] ?? 0, 2); ?></div>
                <div class="label">Total Sales</div>
            </div>
            
            <div class="summary-card">
                <div class="value"><?php echo $summary['total_transactions'] ?? 0; ?></div>
                <div class="label">Transactions</div>
            </div>
            
            <div class="summary-card">
                <div class="value">₹<?php echo number_format($summary['avg_transaction'] ?? 0, 2); ?></div>
                <div class="label">Average Transaction</div>
            </div>
            
            <div class="summary-card">
                <div class="value">₹<?php echo number_format($summary['total_cash'] ?? 0, 2); ?></div>
                <div class="label">Total Cash Received</div>
            </div>
        </div>
        
        <!-- Daily Sales Report -->
        <div class="card">
            <h2 class="card-title">Daily Sales Report</h2>
            
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Transactions</th>
                            <th class="text-right">Total Sales</th>
                            <th class="text-right">Cash Received</th>
                            <th class="text-right">Change Given</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daily_report)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No sales data for selected period</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daily_report as $row): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($row['sale_day'])); ?></td>
                                    <td class="text-right"><?php echo $row['transactions']; ?></td>
                                    <td class="text-right">₹<?php echo number_format($row['total_sales'], 2); ?></td>
                                    <td class="text-right">₹<?php echo number_format($row['total_cash'], 2); ?></td>
                                    <td class="text-right">₹<?php echo number_format($row['total_change'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Best Selling Products -->
        <div class="card">
            <h2 class="card-title">Best Selling Products</h2>
            
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th>Barcode</th>
                            <th class="text-right">Quantity Sold</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($best_sellers)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No sales data for selected period</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($best_sellers as $index => $product): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <span class="product-badge"><?php echo $product['barcode']; ?></span>
                                    </td>
                                    <td class="text-right"><?php echo $product['total_qty']; ?></td>
                                    <td class="text-right">₹<?php echo number_format($product['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>