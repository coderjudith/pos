<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: cashier/pos.php');
            }
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo STORE_NAME; ?> - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }
        
        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .right-section {
            flex: 1.5;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-circle {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .logo-text {
            font-size: 48px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .store-name {
            font-size: 36px;
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .store-tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            font-weight: 300;
            margin-bottom: 5px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-title {
            text-align: center;
            color: #333;
            margin-bottom: 5px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .login-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #764ba2;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .demo-info {
            background: #f8f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 25px;
            font-size: 13px;
            color: #555;
            border-left: 4px solid #667eea;
        }
        
        .demo-info h3 {
            color: #667eea;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            font-size: 14px;
        }
        
        .demo-info p {
            margin-bottom: 8px;
            padding: 3px 0;
        }
        
        .demo-info strong {
            color: #333;
        }
        
        .right-content {
            max-width: 600px;
        }
        
        .welcome-title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .welcome-subtitle {
            font-size: 20px;
            font-weight: 300;
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 40px;
        }
        
        .feature-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .feature-item i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .feature-item h4 {
            font-size: 18px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .feature-item p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .footer-text {
            position: absolute;
            bottom: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }
        
        /* Responsive design */
        @media (max-width: 1024px) {
            body {
                flex-direction: column;
            }
            
            .left-section {
                flex: none;
                height: auto;
                padding: 30px;
            }
            
            .right-section {
                flex: none;
                height: auto;
                padding: 30px;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Left Section - Login Form -->
    <div class="left-section">
        <div class="logo-container">
            <div class="logo-circle">
                <div class="logo-text">MM</div>
            </div>
            <h1 class="store-name"><?php echo STORE_NAME; ?></h1>
            <p class="store-tagline">Point of Sale System</p>
        </div>
        
        <div class="login-container">
            <h2 class="login-title">Sign In</h2>
            <p class="login-subtitle">Access your account to continue</p>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter your username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="demo-info">
                <h3><i class="fas fa-key"></i> Demo Accounts</h3>
                <p><strong>Admin:</strong> admin / admin123</p>
                <p><strong>Cashier:</strong> cashier / cashier123</p>
            </div>
        </div>
        
        <div class="footer-text">
            &copy; 2026 <?php echo STORE_NAME; ?>. All rights reserved.
        </div>
    </div>
    
    <!-- Right Section - Background Image & Info -->
    <div class="right-section">
        <div class="right-content">
            <h1 class="welcome-title">Welcome to <?php echo STORE_NAME; ?></h1>
            <p class="welcome-subtitle">
                Manage your store inventory, process sales, and track customer transactions 
                with our powerful and intuitive Point of Sale system.
            </p>
            
            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-bolt"></i>
                    <h4>Fast Checkout</h4>
                    <p>Process transactions quickly with our streamlined POS interface</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <h4>Real-time Reports</h4>
                    <p>Get instant insights into sales and inventory performance</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-boxes"></i>
                    <h4>Inventory Control</h4>
                    <p>Track stock levels and receive automated low-stock alerts</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-user-shield"></i>
                    <h4>Secure Access</h4>
                    <p>Role-based access control ensures data security</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>