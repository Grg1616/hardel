<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email']) || !isset($_GET['order_id']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Verify the order belongs to the current user
$stmt = $conn->prepare("
    SELECT o.* 
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    JOIN user u ON c.user_id = u.user_id
    WHERE o.order_id = ? AND u.email = ?
");
$stmt->bind_param("is", $order_id, $_SESSION['email']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or access denied.");
}

// Get order items
$items_stmt = $conn->prepare("
    SELECT oi.*, p.name, p.product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
include 'navs/usernav.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h2>Order Confirmation</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <h4 class="alert-heading">Thank you for your order!</h4>
                <p>Your order #<?= $order_id ?> has been placed successfully.</p>
                <p>A confirmation email has been sent to <?= htmlspecialchars($_SESSION['email']) ?>.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>Order Details</h4>
                    <p><strong>Order Number:</strong> #<?= $order_id ?></p>
                    <p><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                </div>
                <div class="col-md-6">
                    <h4>Payment Summary</h4>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                </div>
            </div>
            
            <hr>
            
            <h4>Order Items</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['product_image'] ?: 'uploads/default.png') ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover;" 
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td>₱<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="text-center mt-4">
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
                <a href="welcome.php" class="btn btn-outline-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>
