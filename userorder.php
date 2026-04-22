<?php
session_start();
include 'config.php'; // Make sure $conn = new mysqli(...) is defined in this file

// Check if the user is logged in and is of type 'customer'
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'customer') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get customer details using email
// Get complete customer details using email
$stmt = $conn->prepare("
    SELECT c.customer_id, c.name, c.phone, c.email, 
           c.province, c.municipalities, c.barangay, c.street,
           c.latitude, c.longitude
    FROM customer c 
    JOIN user u ON c.user_id = u.user_id 
    WHERE u.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    die("Customer not found.");
}

$customer_id = $customer['customer_id']; // Used for further queries

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $order_id, $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order && strtolower($order['order_status']) == 'pending') {
        $update = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE order_id = ?");
        $update->bind_param("i", $order_id);
        $update->execute();
        $success = "Order #$order_id has been cancelled successfully!";
    } else {
        $error = "Order cannot be cancelled at this stage.";
    }
}

// Fetch customer's orders
$stmt = $conn->prepare("
    SELECT o.*, d.first_name AS driver_first, d.last_name AS driver_last 
    FROM orders o
    LEFT JOIN driver d ON o.driver_id = d.driver_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'navs/usernav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>My Orders</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <span>Order #<?= $order['order_id'] ?></span>
                    <span class="badge bg-<?= 
                        $order['order_status'] == 'complete' ? 'success' : 
                        ($order['order_status'] == 'cancelled' ? 'danger' : 
                        'warning') ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Order Details</h5>
                            <p>Order Date: <?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></p>
                            <p>Total Amount: ₱<?= number_format($order['total_amount'], 2) ?></p>
                            <p>Status: <?= ucfirst($order['order_status']) ?></p>
                            <?php if ($order['driver_id']): ?>
                                <p>Driver: <?= $order['driver_first'] ?> <?= $order['driver_last'] ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <h5>Delivery Information</h5>
                            <p>Address: <?= 
                                $customer['street'] . ', ' . 
                                $customer['barangay'] . ', ' . 
                                $customer['municipalities'] . ', ' . 
                                $customer['province']
                            ?></p>
                        </div>
                    </div>

                    <!-- Fetch Order Items -->
                    <?php 
                    $stmt_items = $conn->prepare("
                        SELECT oi.*, p.name, p.product_image 
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE oi.order_id = ?
                    ");
                    $stmt_items->bind_param("i", $order['order_id']);
                    $stmt_items->execute();
                    $items_result = $stmt_items->get_result();
                    $items = [];
                    while ($item = $items_result->fetch_assoc()) {
                        $items[] = $item;
                    }
                    ?>

                    <h6 class="mt-3">Items:</h6>
                    <ul class="list-group">
                        <?php foreach ($items as $item): ?>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $item['product_image'] ?>" alt="<?= $item['name'] ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                    <div>
                                        <h6><?= $item['name'] ?></h6>
                                        <div>Quantity: <?= $item['quantity'] ?></div>
                                        <div>Price: ₱<?= number_format($item['price'], 2) ?></div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Cancel Button -->
                    <?php if ($order['order_status'] == 'complete'): ?>
                        <div class="mt-3 text-end">
                            <span class="badge bg-success">Order Completed</span>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="mt-3 text-end">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" name="cancel_order" 
                                    class="btn btn-danger btn-sm"
                                    <?= in_array($order['order_status'], ['shipped', 'cancelled']) ? 'disabled' : '' ?>>
                                <?= $order['order_status'] == 'cancelled' ? 'Cancelled' : 'Cancel Order' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
