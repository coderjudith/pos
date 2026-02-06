<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('cashier');

// Get today's date
$today = date('Y-m-d');
$today_formatted = date('F d, Y'); // e.g., "December 25, 2023"

// Get today's sales for this cashier
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        COUNT(si.id) as item_count
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    WHERE DATE(s.sale_date) = ?
    AND s.cashier_id = ?
    GROUP BY s.id
    ORDER BY s.sale_date DESC
");
$stmt->execute([$today, $_SESSION['user_id']]);
$sales = $stmt->fetchAll();

// Calculate totals
$total_sales = 0;
$total_transactions = count($sales);
$total_items = 0;
$total_cash = 0;
$total_change = 0;

foreach ($sales as $sale) {
    $total_sales += $sale['total_amount'];
    $total_cash += $sale['cash'];
    $total_change += $sale['change_amount'];
    $total_items += $sale['item_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Sales Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left h1 {
            color: #333;
            margin-bottom: 5px;
            font-size: 28px;
        }
        
        .header-left .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .cashier-info {
            background: #f8fafc;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            color: #555;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            display: block;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card-primary {
            border-top: 5px solid #667eea;
        }
        
        .card-success {
            border-top: 5px solid #10b981;
        }
        
        .card-warning {
            border-top: 5px solid #f59e0b;
        }
        
        .card-info {
            border-top: 5px solid #3b82f6;
        }
        
        .card-purple {
            border-top: 5px solid #8b5cf6;
        }
        
        .sales-table-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .table-header h2 {
            color: #333;
            font-size: 22px;
        }
        
        .date-badge {
            background: #e0e7ff;
            color: #4f46e5;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .sales-table thead {
            background: #f8fafc;
        }
        
        .sales-table th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .sales-table td {
            padding: 18px 15px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
        }
        
        .sales-table tr:hover {
            background: #f9fafb;
        }
        
        .sales-table tr:last-child td {
            border-bottom: none;
        }
        
        .receipt-id {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1f2937;
        }
        
        .time-badge {
            background: #f3f4f6;
            color: #4b5563;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .amount {
            font-weight: bold;
            color: #059669;
        }
        
        .cash-amount {
            color: #3b82f6;
        }
        
        .change-amount {
            color: #8b5cf6;
        }
        
        .action-btn {
            padding: 8px 16px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            background: #059669;
        }
        
        .action-btn.secondary {
            background: #6b7280;
        }
        
        .action-btn.secondary:hover {
            background: #4b5563;
        }
        
        .action-btn.receipt {
            background: #f59e0b;
        }
        
        .action-btn.receipt:hover {
            background: #d97706;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #6b7280;
        }
        
        .empty-state p {
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        .footer-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #374151;
            border: 2px solid #e5e7eb;
        }
        
        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
        
        .btn-print {
            background: #10b981;
            color: white;
        }
        
        .btn-print:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            font-weight: 500;
            color: #374151;
        }
        
        .summary-label {
            color: #6b7280;
        }
        
        .summary-value {
            font-weight: bold;
            color: #059669;
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .sales-table {
                display: block;
                overflow-x: auto;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .footer-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-group {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .sales-table th,
            .sales-table td {
                padding: 12px 8px;
                font-size: 13px;
            }
        }
        
        .transaction-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Today's Sales Report</h1>
                <p class="subtitle">Detailed view of all transactions for today</p>
            </div>
            <div class="cashier-info">
                Cashier: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> • 
                Role: <span style="color: #667eea;"><?php echo ucfirst($_SESSION['user_role']); ?></span>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card card-primary">
                <span class="stat-icon">💰</span>
                <div class="stat-value">₱<?php echo number_format($total_sales, 2); ?></div>
                <div class="stat-label">Total Sales</div>
            </div>
            
            <div class="stat-card card-success">
                <span class="stat-icon">📊</span>
                <div class="stat-value"><?php echo $total_transactions; ?></div>
                <div class="stat-label">Transactions</div>
            </div>
            
            <div class="stat-card card-warning">
                <span class="stat-icon">📦</span>
                <div class="stat-value"><?php echo $total_items; ?></div>
                <div class="stat-label">Items Sold</div>
            </div>
            
            <div class="stat-card card-purple">
                <span class="stat-icon">💵</span>
                <div class="stat-value">₱<?php echo number_format($total_cash, 2); ?></div>
                <div class="stat-label">Cash Received</div>
            </div>
        </div>
        
        <!-- Sales Table -->
        <div class="sales-table-container">
            <div class="table-header">
                <h2>Transaction Details</h2>
                <div class="date-badge">
                    📅 <?php echo $today_formatted; ?>
                </div>
            </div>
            
            <?php if (empty($sales)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Sales Today</h3>
                    <p>You haven't made any sales yet today. Start selling to see transactions here.</p>
                    <a href="pos.php" class="btn btn-primary">
                        💰 Start Selling
                    </a>
                </div>
            <?php else: ?>
                <table class="sales-table">
    <thead>
        <tr>
            <th>Receipt #</th>
            <th>Time</th>
            <th>Items</th>
            <th>Amount</th>
            <th>Cash</th>
            <th>Change</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $sale): ?>
            <?php
            $sale_time = date('g:i A', strtotime($sale['sale_date']));
            $receipt_id = str_pad($sale['id'], 6, '0', STR_PAD_LEFT);
            ?>
            <tr>
                <td>
                    <span class="receipt-id">#<?php echo $receipt_id; ?></span>
                </td>
                <td>
                    <span class="time-badge"><?php echo $sale_time; ?></span>
                </td>
                <td>
                    <span style="color: #6b7280;">
                        <?php echo $sale['item_count']; ?> item<?php echo $sale['item_count'] != 1 ? 's' : ''; ?>
                    </span>
                </td>
                <td>
                    <span class="amount">₱<?php echo number_format($sale['total_amount'], 2); ?></span>
                </td>
                <td>
                    <span class="cash-amount">₱<?php echo number_format($sale['cash'], 2); ?></span>
                </td>
                <td>
                    <span class="change-amount">₱<?php echo number_format($sale['change_amount'], 2); ?></span>
                </td>
                <td>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <!-- 1. Print Receipt (Primary Action) -->
                        <a href="../actions/receipt.php?sale_id=<?php echo $sale['id']; ?>" 
                           target="_blank" 
                           class="action-btn receipt"
                           title="Print receipt for this sale">
                            🖨️ Print
                        </a>
                        
                        <!-- 2. View Details (Modal/Detailed View) -->
                        <button onclick="showSaleDetails(<?php echo $sale['id']; ?>)" 
                                class="action-btn secondary"
                                title="View sale details">
                            📋 Details
                        </button>
                        
                        <!-- 3. Email/Save Receipt (Optional) -->
                        <button onclick="saveReceipt(<?php echo $sale['id']; ?>)" 
                                class="action-btn"
                                style="background: #3b82f6;"
                                title="Save receipt as PDF">
                            💾 Save
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                
                <!-- Summary -->
                <div class="summary-row">
                    <div class="summary-label">Today's Summary</div>
                    <div class="summary-value">
                        <?php echo $total_transactions; ?> sales • ₱<?php echo number_format($total_sales, 2); ?> total
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Footer Actions -->
            <div class="footer-actions">
                <div class="btn-group">
                    <a href="pos.php" class="btn btn-primary">
                        💰 New Sale
                    </a>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        ← Dashboard
                    </a>
                </div>
                
                <?php if (!empty($sales)): ?>
                <button onclick="window.print()" class="btn btn-print">
                    🖨️ Print Report
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function viewDetails(saleId) {
            if (confirm('View detailed sale information?')) {
                // You can implement a modal or redirect to detailed view
                window.open('../actions/receipt.php?sale_id=' + saleId, '_blank');
            }
        }
        
        // Auto-refresh every 5 minutes (optional)
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutes
        
        // Print styles
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body {
                    background: white !important;
                    padding: 0 !important;
                }
                .header, .stats-cards, .footer-actions, .btn-group {
                    display: none !important;
                }
                .sales-table-container {
                    box-shadow: none !important;
                    padding: 0 !important;
                }
                .table-header {
                    text-align: center;
                }
                .date-badge {
                    background: none !important;
                    color: black !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    <!-- Sale Details Modal -->
<div id="saleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; width: 90%; max-width: 600px; border-radius: 15px; padding: 30px; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #333;">Sale Details</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">×</button>
        </div>
        <div id="modalContent">
            <!-- Content will be loaded here -->
        </div>
        <div style="margin-top: 25px; text-align: center;">
            <button onclick="closeModal()" style="padding: 10px 25px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
// Modal functionality
function showSaleDetails(saleId) {
    // Show loading
    document.getElementById('modalContent').innerHTML = '<div style="text-align: center; padding: 40px;">Loading...</div>';
    document.getElementById('saleModal').style.display = 'flex';
    
    // Fetch sale details via AJAX
    fetch(`../actions/get_sale_details.php?sale_id=${saleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Build detailed view
                let html = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                            <div>
                                <strong style="color: #666;">Receipt #:</strong>
                                <span style="font-weight: bold; color: #333; margin-left: 10px;">${data.receipt_id}</span>
                            </div>
                            <div>
                                <strong style="color: #666;">Time:</strong>
                                <span style="margin-left: 10px;">${data.sale_time}</span>
                            </div>
                        </div>
                        
                        <h3 style="color: #333; margin-bottom: 15px;">Items Purchased:</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Item</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Qty</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Price</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                // Add items
                data.items.forEach(item => {
                    html += `
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #f3f4f6;">${item.name}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #f3f4f6;">${item.qty}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #f3f4f6;">₱${parseFloat(item.price).toFixed(2)}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #f3f4f6;">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                // Add totals
                html += `
                            </tbody>
                        </table>
                        
                        <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Subtotal:</span>
                                <span style="font-weight: bold;">₱${parseFloat(data.subtotal).toFixed(2)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>VAT (12%):</span>
                                <span>₱${parseFloat(data.vat).toFixed(2)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 18px;">
                                <span><strong>Total:</strong></span>
                                <span style="font-weight: bold; color: #059669;">₱${parseFloat(data.total).toFixed(2)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Cash Received:</span>
                                <span>₱${parseFloat(data.cash).toFixed(2)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Change Given:</span>
                                <span style="color: #8b5cf6;">₱${parseFloat(data.change).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('modalContent').innerHTML = html;
            } else {
                document.getElementById('modalContent').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #ef4444;">
                        Error loading sale details.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalContent').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #ef4444;">
                    Network error. Please try again.
                </div>
            `;
        });
}

function closeModal() {
    document.getElementById('saleModal').style.display = 'none';
}

// Save receipt as PDF (simulated)
function saveReceipt(saleId) {
    if (confirm('Save receipt as PDF?')) {
        // Open receipt in new tab for printing/saving
        const newWindow = window.open(`../actions/receipt.php?sale_id=${saleId}`, '_blank');
        
        // After a delay, trigger print/save
        setTimeout(() => {
            if (newWindow) {
                newWindow.focus();
                // Note: Actual PDF save would require more complex implementation
                // This is a simplified version
            }
        }, 1000);
    }
}

// Close modal on ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal when clicking outside
document.getElementById('saleModal').addEventListener('click', (e) => {
    if (e.target.id === 'saleModal') {
        closeModal();
    }
});
</script>
</body>
</html>