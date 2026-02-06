<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_login();

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get all active products for barcode scanning
$products = $pdo->query("SELECT id, barcode, name, price, stock_qty FROM products WHERE status = 1")->fetchAll();
$products_by_barcode = [];
foreach ($products as $product) {
    $products_by_barcode[$product['barcode']] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Interface - <?php echo STORE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        html, body {
            height: 100%;
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
                linear-gradient(rgba(245, 247, 250, 0.95), rgba(245, 247, 250, 0.98)),
                url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
            opacity: 0.4;
        }
        
        .pos-header {
            background: 
                linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
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
            border-bottom: 3px solid rgba(255, 255, 255, 0.2);
        }
        
        .pos-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1;
        }
        
        .pos-header > * {
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
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 10px;
            border-radius: 5px;
            margin-left: 5px;
        }
        
        .pos-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .cashier-name {
            color: white;
            font-weight: 500;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-btn {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }
        
        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        }
        
        .nav-btn.logout {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .nav-btn.logout:hover {
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 25px;
            padding: 30px;
            max-width: 1600px;
            margin: 0 auto;
            min-height: calc(100vh - 120px);
        }
        
        .product-list {
            background: 
                linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98)),
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .product-list::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            z-index: 1;
        }
        
        .product-list > * {
            position: relative;
            z-index: 2;
        }
        
        .cart-section {
            background: 
                linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98)),
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=20');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        
        .cart-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            z-index: 1;
        }
        
        .cart-section > * {
            position: relative;
            z-index: 2;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #333;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title::before {
            content: '';
            width: 6px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
        }
        
        .barcode-input {
            width: 100%;
            padding: 18px;
            font-size: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            letter-spacing: 3px;
            background: white;
            transition: all 0.3s;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .barcode-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }
        
        .barcode-input::placeholder {
            color: #999;
            font-weight: normal;
            letter-spacing: normal;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            padding: 5px;
        }
        
        .product-card {
            background: 
                linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.95));
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .product-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px 12px 0 0;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 16px;
            line-height: 1.3;
        }
        
        .product-price {
            color: #667eea;
            font-weight: 800;
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .product-stock {
            font-size: 13px;
            color: #666;
            padding: 4px 10px;
            background: #f0f4ff;
            border-radius: 12px;
            display: inline-block;
            margin-top: 8px;
            font-weight: 500;
        }
        
        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 350px;
            margin-bottom: 25px;
            padding-right: 10px;
        }
        
        .cart-items::-webkit-scrollbar {
            width: 6px;
        }
        
        .cart-items::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .cart-items::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .cart-item:hover {
            background: white;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .item-info {
            flex-grow: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }
        
        .item-price {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .item-qty {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0 15px;
        }
        
        .qty-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qty-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .qty-btn.remove {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            width: 32px;
            height: 32px;
            font-size: 16px;
        }
        
        .qty-display {
            font-weight: 700;
            min-width: 36px;
            text-align: center;
            font-size: 16px;
            color: #333;
            background: #f0f4ff;
            padding: 6px;
            border-radius: 6px;
        }
        
        .item-total {
            font-weight: 800;
            color: #333;
            min-width: 90px;
            text-align: right;
            font-size: 16px;
        }
        
        .cart-totals {
            background: linear-gradient(135deg, #f0f4ff 0%, #f8fafc 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }
        
        .total-row.grand-total {
            font-size: 26px;
            font-weight: 800;
            color: #667eea;
            border-top: 2px solid rgba(102, 126, 234, 0.3);
            padding-top: 15px;
            margin-top: 15px;
            border-bottom: none;
        }
        
        .cash-input {
            width: 100%;
            padding: 18px;
            font-size: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: right;
            background: white;
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .cash-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            transform: translateY(-1px);
        }
        
        .change-display {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
            padding: 18px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 12px;
            border: 2px solid #10b981;
            color: #065f46;
            transition: all 0.3s;
        }
        
        .change-display.insufficient {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #ef4444;
            color: #7f1d1d;
        }
        
        .new-sale-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 18px;
            font-size: 18px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            margin-bottom: 15px;
            transition: all 0.3s;
            width: 100%;
            display: block;
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
        }
        
        .new-sale-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
        }
        
        .new-sale-btn:active {
            transform: translateY(-1px);
        }
        
        .new-sale-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .checkout-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 20px;
            font-size: 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 800;
            transition: all 0.3s;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .checkout-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }
        
        .checkout-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .empty-cart {
            text-align: center;
            color: #9ca3af;
            padding: 60px 20px;
            font-style: italic;
            font-size: 16px;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px;
            border: 2px dashed #d1d5db;
        }
        
        .empty-cart::before {
            content: '🛒';
            font-size: 40px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        /* Sweet Alert Style Popup */
        .custom-alert {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .alert-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: alertSlideIn 0.4s ease-out;
            border: 2px solid rgba(102, 126, 234, 0.1);
        }
        
        @keyframes alertSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .alert-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .alert-icon.success {
            color: #10b981;
        }
        
        .alert-icon.error {
            color: #ef4444;
        }
        
        .alert-icon.warning {
            color: #f59e0b;
        }
        
        .alert-icon.info {
            color: #3b82f6;
        }
        
        .alert-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }
        
        .alert-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .alert-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .alert-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 120px;
        }
        
        .alert-btn.confirm {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .alert-btn.confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .alert-btn.cancel {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .alert-btn.cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .alert-btn.neutral {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }
        
        /* Notification Toast */
        .toast {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 20px 25px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 1001;
            animation: toastSlideIn 0.3s ease-out;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .toast.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.9) 0%, rgba(5, 150, 105, 0.9) 100%);
        }
        
        .toast.error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9) 0%, rgba(220, 38, 38, 0.9) 100%);
        }
        
        .toast.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.9) 0%, rgba(217, 119, 6, 0.9) 100%);
        }
        
        .toast.info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(37, 99, 235, 0.9) 100%);
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .toast-icon {
            font-size: 24px;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
            margin-left: 15px;
        }
        
        .toast-close:hover {
            opacity: 1;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .pos-container {
                grid-template-columns: 1fr 400px;
                gap: 20px;
                padding: 20px;
            }
        }
        
        @media (max-width: 992px) {
            .pos-container {
                grid-template-columns: 1fr;
                max-width: 800px;
            }
            
            .cart-section {
                max-height: 600px;
            }
        }
        
        @media (max-width: 768px) {
            .pos-header {
                flex-direction: column;
                gap: 15px;
                padding: 15px 20px;
                text-align: center;
            }
            
            .pos-info {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .section-title {
                font-size: 20px;
            }
            
            .cart-item {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .item-qty {
                order: 3;
                width: 100%;
                justify-content: center;
            }
            
            .toast {
                left: 20px;
                right: 20px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <!-- Custom Alert Modal -->
    <div class="custom-alert" id="customAlert">
        <div class="alert-content">
            <div class="alert-icon" id="alertIcon">⚠️</div>
            <h2 class="alert-title" id="alertTitle">Alert Title</h2>
            <p class="alert-message" id="alertMessage">Alert message goes here</p>
            <div class="alert-buttons" id="alertButtons">
                <button class="alert-btn confirm" onclick="closeAlert(true)">Confirm</button>
                <button class="alert-btn cancel" onclick="closeAlert(false)">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="pos-header">
        <div class="logo"><?php echo STORE_NAME; ?> <span>CASHIER MODE</span></div>
        <div class="pos-info">
            <span class="cashier-name">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
            <a href="../actions/logout.php" class="nav-btn logout">🚪 Logout</a>
        </div>
    </div>
    
    <!-- Main POS Interface -->
    <div class="pos-container">
        <!-- Products Section -->
        <div class="product-list">
            <h2 class="section-title">📦 Available Products</h2>
            <input type="text" 
                   class="barcode-input" 
                   id="barcodeInput" 
                   placeholder="🔍 Scan barcode or type barcode number"
                   autofocus>
            
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" 
                         onclick="addToCart('<?php echo $product['barcode']; ?>')"
                         data-barcode="<?php echo $product['barcode']; ?>">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-stock">📦 Stock: <?php echo $product['stock_qty']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Cart Section -->
        <div class="cart-section">
            <h2 class="section-title">🛒 Shopping Cart</h2>
            
            <div class="cart-items" id="cartItems">
                <!-- Cart items will be loaded here via JavaScript -->
            </div>
            
            <div class="cart-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
                </div>
                <div class="total-row">
                    <span>VAT (12%):</span>
                    <span id="tax"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Amount:</span>
                    <span id="total"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
                </div>
            </div>
            
            <input type="number" 
                   class="cash-input" 
                   id="cashInput" 
                   placeholder="💰 Enter cash amount"
                   min="0" 
                   step="0.01">
            
            <div class="change-display" id="changeDisplay">
                💵 Change: <?php echo CURRENCY_SYMBOL; ?>0.00
            </div>
            
            <!-- NEW SALE BUTTON -->
            <button class="new-sale-btn" onclick="newSale()" id="newSaleBtn" style="display: none;">
                🆕 START NEW SALE
            </button>
            
            <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
                ✅ PROCESS CHECKOUT
            </button>
        </div>
    </div>

    <script>
    // Enhanced notification system
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">
                    ${type === 'success' ? '✅' : 
                      type === 'error' ? '❌' : 
                      type === 'warning' ? '⚠️' : 'ℹ️'}
                </span>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    // Enhanced alert system
    let alertResolve;
    function showAlert(title, message, type = 'warning', showCancel = true) {
        return new Promise((resolve) => {
            const alert = document.getElementById('customAlert');
            const icon = document.getElementById('alertIcon');
            const alertTitle = document.getElementById('alertTitle');
            const alertMessage = document.getElementById('alertMessage');
            const alertButtons = document.getElementById('alertButtons');
            
            // Set icon based on type
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            
            icon.className = `alert-icon ${type}`;
            icon.textContent = icons[type] || '⚠️';
            alertTitle.textContent = title;
            alertMessage.textContent = message;
            
            // Configure buttons
            if (showCancel) {
                alertButtons.innerHTML = `
                    <button class="alert-btn confirm" onclick="closeAlert(true)">Confirm</button>
                    <button class="alert-btn cancel" onclick="closeAlert(false)">Cancel</button>
                `;
            } else {
                alertButtons.innerHTML = `
                    <button class="alert-btn neutral" onclick="closeAlert(true)">OK</button>
                `;
            }
            
            alert.style.display = 'flex';
            alertResolve = resolve;
        });
    }

    function closeAlert(result) {
        document.getElementById('customAlert').style.display = 'none';
        if (alertResolve) {
            alertResolve(result);
        }
    }

    // Products data from PHP
    const products = <?php echo json_encode($products_by_barcode); ?>;
    
    // Cart functions
    function loadCart() {
        fetch('../actions/get_cart.php')
            .then(response => response.json())
            .then(cart => {
                displayCart(cart);
                calculateTotals(cart);
            })
            .catch(error => {
                console.error('Error loading cart:', error);
                showToast('Error loading cart', 'error');
            });
    }
    
    function displayCart(cart) {
        const cartItems = document.getElementById('cartItems');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const newSaleBtn = document.getElementById('newSaleBtn');
        
        if (Object.keys(cart).length === 0) {
            cartItems.innerHTML = '<div class="empty-cart">Cart is empty<br>Scan or click products to add items</div>';
            checkoutBtn.disabled = true;
            newSaleBtn.style.display = 'none';
            return;
        }
        
        checkoutBtn.disabled = false;
        newSaleBtn.style.display = 'none';
        
        let html = '';
        for (const barcode in cart) {
            const item = cart[barcode];
            html += `
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price"><?php echo CURRENCY_SYMBOL; ?>${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <div class="item-total"><?php echo CURRENCY_SYMBOL; ?>${parseFloat(item.subtotal).toFixed(2)}</div>
                    <div class="item-qty">
                        <button class="qty-btn" onclick="updateQty('${barcode}', ${item.qty - 1})">−</button>
                        <span class="qty-display">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty('${barcode}', ${item.qty + 1})">+</button>
                    </div>
                    <button class="qty-btn remove" onclick="removeFromCart('${barcode}')" title="Remove item">×</button>
                </div>
            `;
        }
        cartItems.innerHTML = html;
    }
    
    function calculateTotals(cart) {
        let subtotal = 0;
        for (const barcode in cart) {
            subtotal += parseFloat(cart[barcode].subtotal);
        }
        
        const vatRate = 0.12;
        const vat = subtotal * vatRate;
        const total = subtotal + vat;
        
        document.getElementById('subtotal').textContent = '<?php echo CURRENCY_SYMBOL; ?>' + subtotal.toFixed(2);
        document.getElementById('tax').textContent = '<?php echo CURRENCY_SYMBOL; ?>' + vat.toFixed(2);
        document.getElementById('total').textContent = '<?php echo CURRENCY_SYMBOL; ?>' + total.toFixed(2);
        
        calculateChange(total);
        
        return {
            subtotal: subtotal,
            vat: vat,
            total: total
        };
    }
    
    function calculateChange(total) {
        const cashInput = document.getElementById('cashInput');
        const cash = parseFloat(cashInput.value) || 0;
        const change = cash - total;
        
        const changeDisplay = document.getElementById('changeDisplay');
        changeDisplay.classList.remove('insufficient');
        
        if (change >= 0) {
            changeDisplay.textContent = '💵 Change: <?php echo CURRENCY_SYMBOL; ?>' + change.toFixed(2);
            changeDisplay.style.color = '#065f46';
        } else {
            changeDisplay.textContent = '⚠️ Insufficient: <?php echo CURRENCY_SYMBOL; ?>' + Math.abs(change).toFixed(2);
            changeDisplay.classList.add('insufficient');
        }
    }
    
    function addToCart(barcode) {
        fetch('../actions/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
                document.getElementById('barcodeInput').value = '';
                document.getElementById('barcodeInput').focus();
                showToast('Product added to cart', 'success');
            } else {
                showToast(data.message || 'Error adding product', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error', 'error');
        });
    }
    
    function updateQty(barcode, newQty) {
        if (newQty < 1) {
            removeFromCart(barcode);
            return;
        }
        
        fetch('../actions/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ barcode: barcode, qty: newQty })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
                showToast('Quantity updated', 'info');
            } else {
                showToast(data.message || 'Error updating quantity', 'error');
            }
        });
    }
    
    function removeFromCart(barcode) {
        showAlert('Remove Item', 'Are you sure you want to remove this item from cart?', 'warning')
            .then(confirmed => {
                if (confirmed) {
                    fetch('../actions/remove_from_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ barcode: barcode })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadCart();
                            showToast('Item removed from cart', 'info');
                        }
                    });
                }
            });
    }
    
    async function checkout() {
        const cash = parseFloat(document.getElementById('cashInput').value) || 0;
        const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('<?php echo CURRENCY_SYMBOL; ?>', '')) || 0;
        const vatRate = 0.12;
        const vatAmount = subtotal * vatRate;
        const total = subtotal + vatAmount;
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        if (total === 0) {
            showToast('Cart is empty! Add items first.', 'warning');
            return;
        }
        
        if (cash < total) {
            showToast(`Cash amount (<?php echo CURRENCY_SYMBOL; ?>${cash.toFixed(2)}) is less than total (<?php echo CURRENCY_SYMBOL; ?>${total.toFixed(2)})`, 'error');
            document.getElementById('cashInput').focus();
            return;
        }
        
        const change = cash - total;
        
        // Show beautiful confirmation dialog
        const confirmed = await showAlert(
            'CONFIRM CHECKOUT',
            `Subtotal: <?php echo CURRENCY_SYMBOL; ?>${subtotal.toFixed(2)}\n` +
            `VAT (12%): <?php echo CURRENCY_SYMBOL; ?>${vatAmount.toFixed(2)}\n` +
            `Total: <?php echo CURRENCY_SYMBOL; ?>${total.toFixed(2)}\n\n` +
            `Cash: <?php echo CURRENCY_SYMBOL; ?>${cash.toFixed(2)}\n` +
            `Change: <?php echo CURRENCY_SYMBOL; ?>${change.toFixed(2)}`,
            'info'
        );
        
        if (!confirmed) return;
        
        // Show loading state
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '⏳ PROCESSING...';
        
        try {
            const response = await fetch('../actions/checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    cash: cash,
                    subtotal: subtotal,
                    vat: vatAmount,
                    total: total
                })
            });
            
            const text = await response.text();
            const data = JSON.parse(text);
            
            if (data.success) {
                // SUCCESS
                showToast(`✅ Sale Completed! Receipt #${data.sale_id}`, 'success');
                
                // Show NEW SALE button
                document.getElementById('newSaleBtn').style.display = 'block';
                
                // Ask if user wants to view receipt
                const viewReceipt = await showAlert(
                    'SALE COMPLETED!',
                    `Transaction successful!\nReceipt #: ${data.sale_id}\nTotal: <?php echo CURRENCY_SYMBOL; ?>${total.toFixed(2)}`,
                    'success',
                    true
                );
                
                if (viewReceipt) {
                    window.open('../actions/receipt.php?sale_id=' + data.sale_id, '_blank');
                }
                
                // Clear cart display
                loadCart();
                
                // Reset form
                document.getElementById('cashInput').value = '';
                document.getElementById('changeDisplay').textContent = '💵 Change: <?php echo CURRENCY_SYMBOL; ?>0.00';
                document.getElementById('changeDisplay').classList.remove('insufficient');
                
            } else {
                // ERROR from server
                showToast('❌ Checkout failed: ' + data.message, 'error');
            }
        } catch (error) {
            // NETWORK ERROR or JSON parse error
            console.error('Checkout error:', error);
            showToast('❌ Network error. Please check connection.', 'error');
        } finally {
            // Reset checkout button
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '✅ PROCESS CHECKOUT';
        }
    }
    
    async function newSale() {
        const cart = <?php echo json_encode($_SESSION['cart'] ?? []); ?>;
        
        if (Object.keys(cart).length > 0) {
            const confirmed = await showAlert(
                'Start New Sale',
                'Current cart will be cleared. Are you sure?',
                'warning'
            );
            
            if (!confirmed) return;
            
            document.getElementById('newSaleBtn').innerHTML = '🔄 CLEARING...';
            document.getElementById('newSaleBtn').disabled = true;
            
            try {
                const response = await fetch('../actions/clear_cart.php');
                const data = await response.json();
                
                if (data.success) {
                    loadCart();
                    document.getElementById('newSaleBtn').style.display = 'none';
                    document.getElementById('newSaleBtn').innerHTML = '🆕 START NEW SALE';
                    document.getElementById('newSaleBtn').disabled = false;
                    document.getElementById('barcodeInput').focus();
                    showToast('New sale started!', 'success');
                }
            } catch (error) {
                showToast('Error clearing cart', 'error');
                document.getElementById('newSaleBtn').innerHTML = '🆕 START NEW SALE';
                document.getElementById('newSaleBtn').disabled = false;
            }
        } else {
            // Cart is already empty
            document.getElementById('newSaleBtn').style.display = 'none';
            document.getElementById('barcodeInput').focus();
        }
    }
    
    // Event listeners
    document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const barcode = this.value.trim();
            if (barcode && products[barcode]) {
                addToCart(barcode);
            } else if (barcode) {
                showToast(`Product not found with barcode: ${barcode}`, 'warning');
                this.value = '';
            }
            e.preventDefault();
        }
    });
    
    document.getElementById('cashInput').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('total').textContent.replace('<?php echo CURRENCY_SYMBOL; ?>', '')) || 0;
        calculateChange(total);
    });
    
    // Load cart on page load
    loadCart();
    
    // Auto-focus barcode input
    document.getElementById('barcodeInput').focus();
    
    // Handle Enter key on cash input
    document.getElementById('cashInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('checkoutBtn').click();
        }
    });
    </script>
</body>
</html>