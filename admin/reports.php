<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('admin');

// Date filters
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Validate dates
if ($start_date > $end_date) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

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
            position: sticky;
            top: 0;
            z-index: 100;
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
            transition: all 0.3s ease;
        }
        
        .nav-btn.primary {
            background: #667eea;
            color: white;
        }
        
        .nav-btn.secondary {
            background: #6b7280;
            color: white;
        }
        
        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title::before {
            content: "📊";
            font-size: 24px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-title::before {
            content: "📈";
            font-size: 18px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
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
            transition: border-color 0.3s;
        }
        
        input[type="date"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-top: 4px solid #667eea;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }
        
        .summary-card .label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .report-table th {
            background: #f8fafc;
            text-align: left;
            padding: 16px 20px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .report-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
            font-size: 14px;
        }
        
        .report-table tr:last-child td {
            border-bottom: none;
        }
        
        .report-table tr:hover {
            background: #f9fafb;
        }
        
        .report-table tr:nth-child(even) {
            background: #fcfcfc;
        }
        
        .report-table tr:nth-child(even):hover {
            background: #f9fafb;
        }
        
       .text-right {
    text-align: left;
    font-family: inherit;
    font-weight: 500;
}
        
        .text-center {
            text-align: center;
            color: #6b7280;
            font-style: italic;
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
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-family: monospace;
            font-weight: 500;
        }
        
        .date-column {
            min-width: 120px;
            white-space: nowrap;
        }
        
        .number-column {
            min-width: 100px;
        }
        
        .rank-column {
            width: 60px;
            text-align: center;
            color: #667eea;
            font-weight: bold;
        }
        
        .product-column {
            min-width: 200px;
        }
        
        .barcode-column {
            min-width: 150px;
        }
        
        .no-data {
            padding: 40px !important;
            text-align: center;
            color: #9ca3af;
        }
        
        .no-data::before {
            content: "📭";
            font-size: 24px;
            display: block;
            margin-bottom: 10px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        /*additional*/
        /* ===== EXPORT BUTTONS STYLES ===== */
.export-buttons {
    display: flex;
    gap: 12px;
    margin-left: 20px;
}

.export-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.export-btn.csv {
    background: #3b82f6; /* Green for CSV */
}

.export-btn.pdf {
    background: #f97316; /* Red for PDF */
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0.9;
}
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .container {
                padding: 0 15px;
                margin: 20px auto;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .card {
                padding: 20px;
            }
            
            .summary-card .value {
                font-size: 28px;
            }
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
                    <button type="submit" class="btn btn-primary">
                        <span>📊</span> Generate Report
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value"><?php echo CURRENCY_SYMBOL . number_format($summary['total_sales'] ?? 0, 2); ?></div>
                <div class="label">Total Sales</div>
            </div>
            
            <div class="summary-card">
                <div class="value"><?php echo $summary['total_transactions'] ?? 0; ?></div>
                <div class="label">Transactions</div>
            </div>
            
            <div class="summary-card">
                <div class="value"><?php echo CURRENCY_SYMBOL . number_format($summary['avg_transaction'] ?? 0, 2); ?></div>
                <div class="label">Average Transaction</div>
            </div>
            
            <div class="summary-card">
                <div class="value"><?php echo CURRENCY_SYMBOL . number_format($summary['total_cash'] ?? 0, 2); ?></div>
                <div class="label">Total Cash Received</div>
            </div>
        </div>
        
        <!-- Daily Sales Report -->
        <div class="card">
            <div class="table-header">
    <h2 class="card-title">Daily Sales Report</h2>
    <div class="export-buttons">
        <a href="export.php?type=csv&report=daily&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn csv">
            <span>📊</span> CSV
        </a>
        <a href="export.php?type=pdf&report=daily&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn pdf">
            <span>📄</span> PDF
        </a>
    </div>
</div>
            
            <div class="table-container">
                <table class="report-table" id="daily-sales">
                    <thead>
                        <tr>
                            <th class="date-column">Date</th>
                            <th class="text-right number-column">Transactions</th>
                            <th class="text-right number-column">Total Sales</th>
                            <th class="text-right number-column">Cash Received</th>
                            <th class="text-right number-column">Change Given</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daily_report)): ?>
                            <tr>
                                <td colspan="5" class="no-data">No sales data for selected period</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daily_report as $row): ?>
                                <tr>
                                    <td class="date-column">
                                        <strong><?php echo date('d M Y', strtotime($row['sale_day'])); ?></strong>
                                        <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                            <?php echo date('D', strtotime($row['sale_day'])); ?>
                                        </div>
                                    </td>
                                    <td class="text-right"><?php echo number_format($row['transactions']); ?></td>
                                    <td class="text-right positive">
    <strong><?php echo CURRENCY_SYMBOL . number_format($row['total_sales'], 2); ?></strong>
</td>
<td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($row['total_cash'], 2); ?></td>
<td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($row['total_change'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($daily_report)): ?>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td>TOTAL</td>
<td class="text-right"><?php echo number_format(array_sum(array_column($daily_report, 'transactions'))); ?></td>
<td class="text-right positive"><?php echo CURRENCY_SYMBOL . number_format(array_sum(array_column($daily_report, 'total_sales')), 2); ?></td>
<td class="text-right"><?php echo CURRENCY_SYMBOL . number_format(array_sum(array_column($daily_report, 'total_cash')), 2); ?></td>
<td class="text-right"><?php echo CURRENCY_SYMBOL . number_format(array_sum(array_column($daily_report, 'total_change')), 2); ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <!-- Best Selling Products -->
        <div class="card">
            <div class="table-header">
    <h2 class="card-title">Best Selling Products</h2>
    <div class="export-buttons">
        <a href="export.php?type=csv&report=products&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn csv">
            <span>📊</span> CSV
        </a>
        <a href="export.php?type=pdf&report=products&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn pdf">
            <span>📄</span> PDF
        </a>
    </div>
</div>
            
            <div class="table-container">
                <table class="report-table" id="best-sellers">
                    <thead>
                        <tr>
                            <th class="rank-column">#</th>
                            <th class="product-column">Product</th>
                            <th class="barcode-column">Barcode</th>
                            <th class="text-right number-column">Quantity Sold</th>
                            <th class="text-right number-column">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($best_sellers)): ?>
                            <tr>
                                <td colspan="5" class="no-data">No sales data for selected period</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($best_sellers as $index => $product): ?>
                                <tr>
                                    <td class="rank-column">
                                        <div style="background: #e0e7ff; color: #667eea; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto;">
                                            <?php echo $index + 1; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="product-badge"><?php echo $product['barcode']; ?></span>
                                    </td>
                                    <td class="text-right">
                                        <span style="background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 15px; font-weight: 500;">
                                            <?php echo number_format($product['total_qty']); ?> units
                                        </span>
                                    </td>
                                    <td class="text-right positive"><strong><?php echo CURRENCY_SYMBOL . number_format($product['total_amount'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Simple export functionality (would need server-side implementation for full functionality)
        function exportTable(tableId) {
            alert('Export functionality would be implemented here for the ' + tableId + ' table.');
            // In a real implementation, this would trigger a server-side PDF/Excel export
        }

        // Set max date to today for end_date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const endDateInput = document.getElementById('end_date');
            const startDateInput = document.getElementById('start_date');
            
            endDateInput.max = today;
            startDateInput.max = today;
            
            // Prevent selecting end date before start date
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            });
            
            // Initialize min date on page load
            if (startDateInput.value) {
                endDateInput.min = startDateInput.value;
            }
        });
    </script>
</body>
</html>