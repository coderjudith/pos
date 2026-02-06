// Global utility functions for POS system

/**
 * Format currency with Indian Rupee symbol
 * @param {number} amount - The amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Calculate totals from cart items
 * @param {Object} cart - Cart object from session
 * @returns {Object} Object containing subtotal, tax, and total
 */
function calculateCartTotals(cart) {
    let subtotal = 0;
    let taxRate = 0.00; // Can be changed based on requirements
    
    for (const item in cart) {
        subtotal += parseFloat(cart[item].subtotal);
    }
    
    const tax = subtotal * taxRate;
    const total = subtotal + tax;
    
    return {
        subtotal: subtotal,
        tax: tax,
        total: total
    };
}

/**
 * Show notification message
 * @param {string} message - Message to display
 * @param {string} type - Type of notification (success, error, warning, info)
 * @param {number} duration - Duration in milliseconds (default: 3000)
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">${message}</div>
        <button class="notification-close">&times;</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${getNotificationColor(type)};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    // Add close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        margin-left: 15px;
    `;
    
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
    
    // Add to document
    document.body.appendChild(notification);
    
    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

/**
 * Get color based on notification type
 */
function getNotificationColor(type) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    return colors[type] || colors.info;
}

/**
 * Add CSS animations for notifications
 */
function addNotificationStyles() {
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Validate barcode format
 * @param {string} barcode - Barcode to validate
 * @returns {boolean} True if valid
 */
function isValidBarcode(barcode) {
    // Basic validation - can be extended based on barcode types
    return barcode.length >= 8 && barcode.length <= 20 && /^\d+$/.test(barcode);
}

/**
 * Confirm action with custom message
 * @param {string} message - Confirmation message
 * @returns {Promise<boolean>} True if confirmed
 */
function confirmAction(message) {
    return new Promise((resolve) => {
        const confirmed = window.confirm(message);
        resolve(confirmed);
    });
}

/**
 * Debounce function for performance
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format date to display format
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date string
 */
function formatDate(date) {
    const d = new Date(date);
    return d.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Initialize notification styles when DOM is loaded
document.addEventListener('DOMContentLoaded', addNotificationStyles);

// Export functions for use in other scripts (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        formatCurrency,
        calculateCartTotals,
        showNotification,
        isValidBarcode,
        confirmAction,
        debounce,
        formatDate
    };
}
function newSale() {
    if (Object.keys(cart).length > 0) {
        if (confirm('Start new sale? Current cart will be cleared.')) {
            fetch('../actions/clear_cart.php')
                .then(response => response.json())
                .then(data => {
                    loadCart();
                    document.getElementById('cashInput').value = '';
                    document.getElementById('changeDisplay').textContent = 'Change: ₹0.00';
                    document.getElementById('barcodeInput').focus();
                    document.getElementById('newSaleBtn').style.display = 'none';
                });
        }
    }
}