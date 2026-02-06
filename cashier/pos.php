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
    <title>POS Interface</title>
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
        
        .pos-header {
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
        
        .pos-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .cashier-name {
            font-weight: 500;
        }
        
        .nav-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .product-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .cart-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .barcode-input {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            letter-spacing: 2px;
        }
        
        .barcode-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .product-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .product-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .product-name {
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        
        .product-price {
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
        }
        
        .product-stock {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 400px;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-info {
            flex-grow: 1;
        }
        
        .item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #666;
            font-size: 14px;
        }
        
        .item-qty {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .qty-btn.remove {
            background: #ef4444;
        }
        
        .qty-display {
            font-weight: 500;
            min-width: 30px;
            text-align: center;
        }
        
        .item-total {
            font-weight: bold;
            color: #333;
            min-width: 80px;
            text-align: right;
        }
        
        .cart-totals {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .total-row.grand-total {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #e0e0e0;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .cash-input {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: right;
        }
        
        .change-display {
            font-size: 18px;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: #f0fdf4;
            border-radius: 5px;
        }
        
        .checkout-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 15px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .checkout-btn:hover {
            background: #059669;
        }
        
        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .empty-cart {
            text-align: center;
            color: #999;
            padding: 40px;
            font-style: italic;
        }
.new-sale-btn {
    background: #8b5cf6;
    color: white;
    border: none;
    padding: 15px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    transition: all 0.3s;
    width: 100%;
    display: block;
}

.new-sale-btn:hover {
    background: #7c3aed;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.new-sale-btn:active {
    transform: translateY(0);
}

.new-sale-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

    </style>
</head>
<body>
    <div class="pos-header">
        <div class="logo">POS - Cashier Mode</div>
        <div class="pos-info">
            <span class="cashier-name">Cashier: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../dashboard.php" class="nav-btn">Dashboard</a>
            <a href="../actions/logout.php" class="nav-btn" style="background: #ef4444;">Logout</a>
        </div>
    </div>
    
    <div class="pos-container">
        <div class="product-list">
            <h2 class="section-title">Products</h2>
            <input type="text" 
                   class="barcode-input" 
                   id="barcodeInput" 
                   placeholder="Scan barcode or type barcode number"
                   autofocus>
            
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" 
                         onclick="addToCart('<?php echo $product['barcode']; ?>')"
                         data-barcode="<?php echo $product['barcode']; ?>">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-stock">Stock: <?php echo $product['stock_qty']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="cart-section">
    <h2 class="section-title">Shopping Cart</h2>
    
    <div class="cart-items" id="cartItems">
        <!-- Cart items will be loaded here via JavaScript -->
    </div>
    
    <div class="cart-totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span id="subtotal"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
        </div>
        <<div class="total-row">
    <span>VAT (12%):</span>
    <span id="tax"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
</div>
        <div class="total-row grand-total">
            <span>Total:</span>
            <span id="total"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
        </div>
    </div>
    
    <input type="number" 
           class="cash-input" 
           id="cashInput" 
           placeholder="Enter cash amount"
           min="0" 
           step="0.01">
    
    <div class="change-display" id="changeDisplay">
        Change: <?php echo CURRENCY_SYMBOL; ?>0.00
    </div>
    
    <!-- NEW SALE BUTTON -->
    <button class="new-sale-btn" onclick="newSale()" id="newSaleBtn" style="display: none;">
        NEW SALE
    </button>
    
    <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
        PROCESS CHECKOUT
    </button>
</div>
    </div>

    <script>
    // Products data from PHP
    const products = <?php echo json_encode($products_by_barcode); ?>;
    
    // Cart functions
    function loadCart() {
        fetch('../actions/get_cart.php')
            .then(response => response.json())
            .then(cart => {
                displayCart(cart);
                calculateTotals(cart);
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
        newSaleBtn.style.display = 'none'; // Hide new sale when items in cart
        
        let html = '';
        for (const barcode in cart) {
            const item = cart[barcode];
            html += `
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price"><?php echo CURRENCY_SYMBOL; ?>${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <div class="item-qty">
                        <button class="qty-btn" onclick="updateQty('${barcode}', ${item.qty - 1})">-</button>
                        <span class="qty-display">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty('${barcode}', ${item.qty + 1})">+</button>
                    </div>
                    <div class="item-total"><?php echo CURRENCY_SYMBOL; ?>${parseFloat(item.subtotal).toFixed(2)}</div>
                    <button class="qty-btn remove" onclick="removeFromCart('${barcode}')">×</button>
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
    
    // Calculate VAT (12% for Philippines) - CHANGE THIS
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
        if (change >= 0) {
            changeDisplay.textContent = 'Change: <?php echo CURRENCY_SYMBOL; ?>' + change.toFixed(2);
            changeDisplay.style.color = '#10b981';
        } else {
            changeDisplay.textContent = 'Insufficient: <?php echo CURRENCY_SYMBOL; ?>' + Math.abs(change).toFixed(2);
            changeDisplay.style.color = '#ef4444';
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
            } else {
                alert('Error: ' + data.message);
            }
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
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
    
    function removeFromCart(barcode) {
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
            }
        });
    }
    
    function checkout() {
    const cash = parseFloat(document.getElementById('cashInput').value) || 0;
    
    // Get subtotal (before VAT) - change this line
    const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('<?php echo CURRENCY_SYMBOL; ?>', '')) || 0;
    
    // Calculate VAT (12% for Philippines) - add these lines
    const vatRate = 0.12;
    const vatAmount = subtotal * vatRate;
    
    // Calculate total (subtotal + VAT) - change this line
    const total = subtotal + vatAmount;
    
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (total === 0) {
        alert('Cart is empty! Add items first.');
        return;
    }
    
    if (cash < total) {
        alert('Cash amount (<?php echo CURRENCY_SYMBOL; ?>' + cash.toFixed(2) + ') is less than total amount (<?php echo CURRENCY_SYMBOL; ?>' + total.toFixed(2) + ').');
        document.getElementById('cashInput').focus();
        return;
    }
    
    const change = cash - total;
    
    // Update confirm message to show VAT breakdown
    if (confirm(`CONFIRM CHECKOUT?\n\nSubtotal: <?php echo CURRENCY_SYMBOL; ?>${subtotal.toFixed(2)}\nVAT (12%): <?php echo CURRENCY_SYMBOL; ?>${vatAmount.toFixed(2)}\nTotal: <?php echo CURRENCY_SYMBOL; ?>${total.toFixed(2)}\nCash: <?php echo CURRENCY_SYMBOL; ?>${cash.toFixed(2)}\nChange: <?php echo CURRENCY_SYMBOL; ?>${change.toFixed(2)}`)) {
        // Show loading state
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '⏳ PROCESSING...';
        
        console.log('Sending checkout with:', { 
            cash: cash, 
            subtotal: subtotal, 
            vat: vatAmount, 
            total: total 
        });
        
        // Send ALL values to server
        fetch('../actions/checkout.php', {
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
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            
            // First, get the response as TEXT to see what's coming back
            return response.text().then(text => {
                console.log('Raw response:', text);
                
                // Try to parse as JSON
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    console.error('Response was:', text);
                    
                    // Show the raw response in alert
                    throw new Error('Server returned invalid JSON. Raw response:\n' + text.substring(0, 500));
                }
            });
        })
        .then(data => {
            console.log('Parsed data:', data);
            
            if (data.success) {
                // SUCCESS
                alert(`✅ ${data.message || 'SALE COMPLETED!'}\n\nReceipt #: ${data.sale_id}\nTotal: <?php echo CURRENCY_SYMBOL; ?>${total.toFixed(2)}`);
                
                // Show NEW SALE button
                document.getElementById('newSaleBtn').style.display = 'block';
                
                // Ask if user wants to view receipt
                if (confirm('View receipt in new tab?')) {
                    window.open('../actions/receipt.php?sale_id=' + data.sale_id, '_blank');
                }
                
                // Clear cart display (but keep button visible)
                loadCart();
                
                // Reset form
                document.getElementById('cashInput').value = '';
                document.getElementById('changeDisplay').textContent = 'Change: <?php echo CURRENCY_SYMBOL; ?>0.00';
                document.getElementById('changeDisplay').style.color = '#10b981';
                
                // Reset checkout button
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = 'PROCESS CHECKOUT';
                
                // Focus back on barcode input
                document.getElementById('barcodeInput').focus();
                
            } else {
                // ERROR from server
                alert('❌ Checkout failed: ' + data.message);
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = 'PROCESS CHECKOUT';
            }
        })
        .catch(error => {
            // NETWORK ERROR or JSON parse error
            console.error('Checkout error:', error);
            alert('❌ Error: ' + error.message + '\n\nOpen browser console (F12) for details.');
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = 'PROCESS CHECKOUT';
        });
    }
}
    
    function newSale() {
        const newSaleBtn = document.getElementById('newSaleBtn');
        const cart = <?php echo json_encode($_SESSION['cart'] ?? []); ?>;
        
        if (Object.keys(cart).length > 0) {
            if (confirm('Start new sale? Current cart will be cleared.')) {
                // Show loading
                newSaleBtn.innerHTML = 'CLEARING...';
                newSaleBtn.disabled = true;
                
                fetch('../actions/clear_cart.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload empty cart
                            loadCart();
                            // Hide new sale button
                            newSaleBtn.style.display = 'none';
                            newSaleBtn.innerHTML = 'NEW SALE';
                            newSaleBtn.disabled = false;
                            // Focus on barcode input
                            document.getElementById('barcodeInput').focus();
                            alert('New sale started!');
                        }
                    })
                    .catch(error => {
                        alert('Error clearing cart');
                        newSaleBtn.innerHTML = 'NEW SALE';
                        newSaleBtn.disabled = false;
                    });
            }
        } else {
            // Cart is already empty
            newSaleBtn.style.display = 'none';
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
                alert('Product not found with barcode: ' + barcode);
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
</script>
</body>
</html>