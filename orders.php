<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
include 'includes/header.php';
include 'config.php';

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all orders with customer details
$orders = $conn->query("
    SELECT o.*, c.name AS customer_name, c.phone, c.email 
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
")->fetch_all(MYSQLI_ASSOC);

// Fetch all order items grouped by order_id
$orderItems = [];
$itemsResult = $conn->query("
    SELECT oi.*, p.name, p.product_image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
");
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[$row['order_id']][] = $row;
}
?>

<style>
.order-management {
    background: #f8f9fa;
    min-height: 70vh;
}

.order-table {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.order-table table {
    margin-bottom: 0;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.selected-order {
    background-color: #e9f5ff !important;
    position: relative;
}

.selected-order::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background-color: #0d6efd;
}

.cart-table {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    display: none;
}

.product-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    width: 85px;
    text-align: center;
}

.bg-pending { background-color: #ffc107; color: #000; }
.bg-shipped { background-color: #0dcaf0; color: #000; }
.bg-complete { background-color: #198754; color: #fff; }
.bg-cancelled { background-color: #dc3545; color: #fff; }

.order-details-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 1rem;
}
.body{
    margin-top: 50px;
}
.order-details-body {
    padding: 1rem;
}

.detail-item {
    margin-bottom: 0.5rem;
}
.detail-item strong {
    display: inline-block;
    width: 140px;
}
</style>

<?php include 'navs/navadmin.php'; ?>

<div class="hardel-app">
  <main class="body-pd order-management">
    <div class="container-fluid pt-4">
        <h3 class="mb-4">Order Management</h3>

        <!-- Orders Table -->
        <div class="order-table">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr data-order-id="<?= $order['order_id'] ?>">
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></td>
                        <td>
                            <span class="status-badge bg-<?= $order['order_status'] ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                        </td>
                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Cart Items Table -->
        <div class="cart-table" id="cartTable">
            <div class="order-details-header">
                <h5 class="mb-0">Order Details - #<span id="orderNumber"></span></h5>
            </div>
            <div class="order-details-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <div class="detail-item"><strong>Name:</strong> <span id="customerName"></span></div>
                        <div class="detail-item"><strong>Email:</strong> <span id="customerEmail"></span></div>
                        <div class="detail-item"><strong>Phone:</strong> <span id="customerPhone"></span></div>
                    </div>
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <div class="detail-item"><strong>Payment Method:</strong> <span id="paymentMethod"></span></div>
                        <div class="detail-item"><strong>Delivery Start:</strong> <span id="deliveryStart"></span></div>
                        <div class="detail-item"><strong>Shipping Fee:</strong> ₱<span id="shippingFee"></span></div>
                    </div>
                </div>

                <h6 class="mb-3">Order Items</h6>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="cartItems">
                        <!-- Cart items will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
  </main>
</div>

<script>
const orderData = <?= json_encode($orders) ?>;
const orderItems = <?= json_encode($orderItems) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const orderRows = document.querySelectorAll('tr[data-order-id]');
    const cartTable = document.getElementById('cartTable');
    const cartItemsBody = document.getElementById('cartItems');
    const orderNumberSpan = document.getElementById('orderNumber');
    
    // Customer info elements
    const customerName = document.getElementById('customerName');
    const customerEmail = document.getElementById('customerEmail');
    const customerPhone = document.getElementById('customerPhone');
    const paymentMethod = document.getElementById('paymentMethod');
    const deliveryStart = document.getElementById('deliveryStart');
    const shippingFee = document.getElementById('shippingFee');

    orderRows.forEach(row => {
        row.addEventListener('click', function() {
            // Remove previous selection
            orderRows.forEach(r => r.classList.remove('selected-order'));
            this.classList.add('selected-order');

            // Get order ID
            const orderId = this.dataset.orderId;
            
            // Find order details
            const order = orderData.find(o => o.order_id == orderId);
            
            // Update order info
            orderNumberSpan.textContent = orderId;
            customerName.textContent = order.customer_name;
            customerEmail.textContent = order.email;
            customerPhone.textContent = order.phone;
            paymentMethod.textContent = order.payment_method.toUpperCase();
            shippingFee.textContent = parseFloat(order.shipping_fee).toFixed(2);
            deliveryStart.textContent = order.delivery_start 
                ? new Date(order.delivery_start).toLocaleString()
                : 'Not started';

            // Clear previous items
            cartItemsBody.innerHTML = '';

            // Add new items
            if (orderItems[orderId]) {
                orderItems[orderId].forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${item.product_image}" 
                                     class="product-img me-3">
                                ${item.name}
                            </div>
                        </td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>₱${(item.price * item.quantity).toFixed(2)}</td>
                    `;
                    cartItemsBody.appendChild(row);
                });

                // Show cart table
                cartTable.style.display = 'block';
            }
        });
    });
});
</script>