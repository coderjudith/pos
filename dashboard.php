<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f7fa;
            min-height: 100vh;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(rgba(245, 247, 250, 0.9), rgba(245, 247, 250, 0.95)),
                url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
            opacity: 0.3;
        }
        
        .header {
            background: 
                linear-gradient(rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)),
                url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 20px 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1;
        }
        
        .header > * {
            position: relative;
            z-index: 2;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }
        
        .logo span {
            color: #ffd166;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-welcome {
            color: white;
            font-weight: 500;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        .user-role {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .welcome {
            background: 
                linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98)),
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            background-size: cover;
            background-position: center;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .welcome::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            z-index: 1;
        }
        
        .welcome > * {
            position: relative;
            z-index: 2;
        }
        
        .welcome h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 32px;
            font-weight: 700;
        }
        
        .welcome p {
            color: #666;
            font-size: 18px;
            max-width: 600px;
            line-height: 1.6;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .card {
            background: 
                linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98)),
                url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            background-size: cover;
            background-position: center;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            z-index: 1;
        }
        
        .card > * {
            position: relative;
            z-index: 2;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .card-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #667eea;
            filter: drop-shadow(0 4px 6px rgba(102, 126, 234, 0.2));
        }
        
        .card-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .card p {
            color: #666;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .card-link {
            display: inline-block;
            margin-top: 10px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .card-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .admin-only {
            background: 
                linear-gradient(rgba(254, 243, 199, 0.95), rgba(254, 243, 199, 0.98)),
                url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            border-left: 5px solid #f59e0b;
        }
        
        .cashier-only {
            background: 
                linear-gradient(rgba(219, 234, 254, 0.95), rgba(219, 234, 254, 0.98)),
                url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            border-left: 5px solid #3b82f6;
        }
        
        /* Add to the existing CSS in the <style> section: */

html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.dashboard {
    flex: 1;
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
    width: 100%;
}

.footer {
    margin-top: auto;
    background: 
        linear-gradient(rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95)),
        url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    color: white;
    text-align: center;
    padding: 25px 20px;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    position: relative;
    flex-shrink: 0;
}

        .footer {
            background: 
                linear-gradient(rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95)),
                url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 25px 20px;
            margin-top: 60px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }
        
        .footer-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #667eea;
        }
        
        .stats-card {
            background: 
                linear-gradient(rgba(240, 249, 255, 0.95), rgba(240, 249, 255, 0.98)),
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            border-left: 5px solid #0ea5e9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            padding: 10px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 8px;
            text-align: left;
        }
        
        .stat-label {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .sale-highlight {
            color: #059669;
        }
        
        .transaction-highlight {
            color: #3b82f6;
        }
        
        .time-highlight {
            color: #6b7280;
        }
        
        .date-highlight {
            color: #8b5cf6;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .welcome {
                padding: 25px;
            }
            
            .welcome h2 {
                font-size: 24px;
            }
            
            .cards {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">POS<span>System</span></div>
        <div class="user-info">
            <div class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
            <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
            <a href="actions/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="dashboard">
        <div class="welcome">
            <h2>Point of Sale Dashboard</h2>
            <p>Manage your store efficiently with our powerful POS system. Track sales, manage inventory, and process transactions seamlessly.</p>
        </div>
        
        <div class="cards">
            <?php if ($_SESSION['user_role'] === 'cashier'): ?>
                <!-- Cashier Dashboard -->
                <div class="card cashier-only">
                    <div class="card-icon">💰</div>
                    <div class="card-title">Point of Sale System</div>
                    <p>Start selling products, manage cart, process checkout</p>
                    <a href="cashier/pos.php" class="card-link">Launch POS</a>
                </div>
                
                <!-- Today's Stats Card -->
                <div class="card stats-card">
                    <div class="card-icon">📊</div>
                    <div class="card-title">Today's Sales</div>
                    <p>View your sales performance</p>
                    
                    <?php
                    // Get today's sales stats for this cashier
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(*) as transactions,
                            COALESCE(SUM(total_amount), 0) as total_sales
                        FROM sales 
                        WHERE DATE(sale_date) = CURDATE() 
                        AND cashier_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $stats = $stmt->fetch();
                    
                    // Get first sale time
                    $stmt = $pdo->prepare("
                        SELECT DATE_FORMAT(MIN(sale_date), '%h:%i %p') as first_sale
                        FROM sales 
                        WHERE DATE(sale_date) = CURDATE() 
                        AND cashier_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $first_sale = $stmt->fetch();
                    ?>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-label">Total Sales</div>
                            <div class="stat-value sale-highlight"><?php echo CURRENCY_SYMBOL . number_format($stats['total_sales'], 2); ?></div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-label">Transactions</div>
                            <div class="stat-value transaction-highlight"><?php echo $stats['transactions']; ?></div>
                        </div>
                        
                        <?php if ($first_sale['first_sale']): ?>
                        <div class="stat-item">
                            <div class="stat-label">First Sale</div>
                            <div class="stat-value time-highlight"><?php echo $first_sale['first_sale']; ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="stat-item">
                            <div class="stat-label">Date</div>
                            <div class="stat-value date-highlight"><?php echo date('d M Y'); ?></div>
                        </div>
                    </div>
                    
                    <?php if ($stats['transactions'] > 0): ?>
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed rgba(0,0,0,0.1);">
                        <a href="cashier/today_sales.php" style="color: #0ea5e9; text-decoration: none; font-size: 14px; font-weight: 500;">
                            📋 View detailed report →
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                <!-- Admin View -->
                <div class="card admin-only">
                    <div class="card-icon">📦</div>
                    <div class="card-title">Product Management</div>
                    <p>Add, edit, or remove products</p>
                    <a href="admin/products.php" class="card-link">Manage Products</a>
                </div>
                
                <div class="card admin-only">
                    <div class="card-icon">📊</div>
                    <div class="card-title">Sales Reports</div>
                    <p>View sales analytics and reports</p>
                    <a href="admin/reports.php" class="card-link">View Reports</a>
                </div>
                
                <div class="card">
                    <div class="card-icon">💰</div>
                    <div class="card-title">POS Interface</div>
                    <p>Access cashier mode</p>
                    <a href="cashier/pos.php" class="card-link">Open POS</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> POS System. All rights reserved.</p>
            <p>Version 1.0 | Designed for efficient store management</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
                <a href="#">Documentation</a>
            </div>
        </div>
    </div>
</body>
</html>