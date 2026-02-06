<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('admin');

// Handle product actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $barcode = $_POST['barcode'];
        $name = $_POST['name'];
        $price = floatval($_POST['price']);
        $stock_qty = intval($_POST['stock_qty']);
        $status = isset($_POST['status']) ? 1 : 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO products (barcode, name, price, stock_qty, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$barcode, $name, $price, $stock_qty, $status]);
        
        header('Location: products.php?success=Product added successfully');
        exit();
        
    } elseif ($action === 'edit' && $id) {
        $barcode = $_POST['barcode'];
        $name = $_POST['name'];
        $price = floatval($_POST['price']);
        $stock_qty = intval($_POST['stock_qty']);
        $status = isset($_POST['status']) ? 1 : 0;
        
        $stmt = $pdo->prepare("
            UPDATE products 
            SET barcode = ?, name = ?, price = ?, stock_qty = ?, status = ? 
            WHERE id = ?
        ");
        $stmt->execute([$barcode, $name, $price, $stock_qty, $status, $id]);
        
        header('Location: products.php?success=Product updated successfully');
        exit();
        
    } elseif ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: products.php?success=Product deleted successfully');
        exit();
    }
}

// Get all products
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

// Get product for editing
$edit_product = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $edit_product = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .products-table th {
            background: #f8fafc;
            text-align: left;
            padding: 15px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .products-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .products-table tr:hover {
            background: #f9fafb;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background: #f59e0b;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .stock-low {
            color: #ef4444;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Product Management</div>
        <div class="nav-buttons">
            <a href="../dashboard.php" class="nav-btn secondary">Dashboard</a>
            <a href="reports.php" class="nav-btn secondary">Reports</a>
            <a href="../actions/logout.php" class="nav-btn" style="background: #ef4444; color: white;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">Manage Products</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Add/Edit Product Form -->
        <div class="card">
            <h2 class="card-title">
                <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
            </h2>
            
            <form method="POST" action="?action=<?php echo $edit_product ? 'edit&id=' . $edit_product['id'] : 'add'; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="barcode">Barcode *</label>
                        <input type="text" 
                               id="barcode" 
                               name="barcode" 
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['barcode']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               step="0.01" 
                               min="0"
                               value="<?php echo $edit_product ? $edit_product['price'] : '0'; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_qty">Stock Quantity *</label>
                        <input type="number" 
                               id="stock_qty" 
                               name="stock_qty" 
                               min="0"
                               value="<?php echo $edit_product ? $edit_product['stock_qty'] : '0'; ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               id="status" 
                               name="status" 
                               value="1"
                               <?php echo $edit_product && $edit_product['status'] ? 'checked' : ''; ?>>
                        <label for="status">Active (available for sale)</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    
                    <?php if ($edit_product): ?>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Product List -->
        <div class="card">
            <h2 class="card-title">Product List</h2>
            
            <div class="table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></td>
                                <td class="<?php echo $product['stock_qty'] < 10 ? 'stock-low' : ''; ?>">
                                    <?php echo $product['stock_qty']; ?>
                                </td>
                                <td>
                                    <span class="status-<?php echo $product['status'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $product['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn-edit">
                                            Edit
                                        </a>
                                        <a href="?action=delete&id=<?php echo $product['id']; ?>" 
                                           class="btn-delete"
                                           onclick="return confirm('Delete this product?')">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>