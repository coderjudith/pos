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
        }
        
        .header {
            background: white;
            padding: 20px;
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-role {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .card-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .card-link {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .card-link:hover {
            background: #5a67d8;
        }
        
        .admin-only {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        
        .cashier-only {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">POS System</div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <span class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></span>
            <a href="actions/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="dashboard">
        <div class="welcome">
            <h2>Point of Sale Dashboard</h2>
            <p>Manage your store efficiently with our POS system</p>
        </div>
        
        <div class="cards">
            <?php if ($_SESSION['user_role'] === 'cashier'): ?>
                <div class="card cashier-only">
                    <div class="card-icon">💰</div>
                    <div class="card-title">POS Interface</div>
                    <p>Start selling products</p>
                    <a href="cashier/pos.php" class="card-link">Open POS</a>
                </div>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
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
</body>
</html>