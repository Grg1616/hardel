<?php
ob_start();
session_start();
include 'config.php';
include 'includes/header.php';

// Redirect if not logged in as driver
if (!isset($_SESSION['user_name']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: login.php");
    exit();
}


// Get driver details
$query = "SELECT u.user_name, d.* 
          FROM user u
          JOIN driver d ON u.user_id = d.user_id
          WHERE u.user_name = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error); // Add this line
}
$stmt->bind_param("s", $_SESSION['user_name']);
$stmt->execute();
$driver_data = $stmt->get_result()->fetch_assoc();

if (!$driver_data) {
    session_destroy();
    header("Location: login.php");
    exit();
}



// Handle delivery selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliveries'])) {
    $order_ids = array_map('intval', $_POST['deliveries']);
    
    if (!empty($order_ids)) {
        // Update orders to shipped status
        $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
        $query = "UPDATE orders SET 
                    order_status = 'shipped',
                    driver_id = ?
                  WHERE order_id IN ($placeholders)";
        
        $stmt = $conn->prepare($query);
        $types = 'i' . str_repeat('i', count($order_ids));
        $params = array_merge([$driver_data['driver_id']], $order_ids);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            header("Location: map.php");
            exit();
        }
    }
    $_SESSION['error'] = "Please select at least one valid order";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch pending orders
$query = "
    SELECT 
        o.order_id,
        c.name AS customer_name,
        CONCAT(c.street, ', ', c.barangay, ', ', c.municipalities, ', ', c.province) AS full_address,
        c.latitude,
        c.longitude,
        o.total_amount,
        o.payment_method,
        o.order_date,
        o.order_status
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    WHERE o.order_status = 'pending'
    ORDER BY o.order_date ASC
";

$result = $conn->query($query);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Deliveries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);}
        .address-col { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
        .status-ready { color: #0d6efd; font-weight: 500;}
        .modal { z-index: 1060 !important; }
        .modal-body { max-height: 60vh; overflow-y: auto;}
        .modal-dialog { margin: 1.75rem auto;}
    </style>
</head>
<body>
<?php include 'navs/navdrivers.php'; ?>

<div class="container py-5">
<?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-truck"></i> Delivery</h1>
        <div><span class="badge bg-primary me-2"><?= count($orders) ?> pending deliveries</span></div>
    </div>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
        <div class="card p-3 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Select</th>
                            <th>Customer</th>
                            <th>Delivery Address</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="deliveries[]" value="<?= $order['order_id'] ?>" class="form-check-input">
                                    </td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td class="address-col" title="<?= htmlspecialchars($order['full_address']) ?>"><?= htmlspecialchars($order['full_address']) ?></td>
                                    <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                    <td><span class="status-ready"><i class="bi bi-box-seam"></i> <?= htmlspecialchars($order['order_status']) ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#itemsModal<?= $order['order_id'] ?>">
                                            <i class="bi bi-list-ul"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-check-circle fs-1"></i><br>No deliveries pending.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($orders)): ?>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-truck"></i> Deliver Now
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Order Items Modals -->
<?php foreach ($orders as $order): ?>
<?php
    $items_query = "
        SELECT 
            p.name, 
            p.product_image,
            oi.quantity, 
            oi.price
        FROM 
            order_items oi
        JOIN 
            products p ON oi.product_id = p.product_id
        WHERE 
            oi.order_id = ?
    ";
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $order['order_id']);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="modal fade" id="itemsModal<?= $order['order_id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-box-seam"></i> Order Items</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><strong>Order Date:</strong> <?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></p>

                <div id="map<?= $order['order_id'] ?>" style="height: 400px;"></div>

                <div class="table-responsive mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['product_image']): ?>
                                        <img src="<?= htmlspecialchars($item['product_image']) ?>" class="img-thumbnail me-2" width="50" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </div>
                                </td>
                                <td><?= $item['quantity'] ?></td>
                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
    <?php foreach ($orders as $order): ?>
    $('#itemsModal<?= $order['order_id'] ?>').on('shown.bs.modal', function () {
        var map<?= $order['order_id'] ?> = L.map('map<?= $order['order_id'] ?>').setView([<?= $order['latitude'] ?>, <?= $order['longitude'] ?>], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data © OpenStreetMap contributors'
        }).addTo(map<?= $order['order_id'] ?>);
        L.marker([<?= $order['latitude'] ?>, <?= $order['longitude'] ?>])
            .addTo(map<?= $order['order_id'] ?>)
            .bindPopup("<?= htmlspecialchars($order['customer_name']) ?><br><?= htmlspecialchars($order['full_address']) ?>")
            .openPopup();
        map<?= $order['order_id'] ?>.invalidateSize();
    });
    <?php endforeach; ?>


    document.querySelector('form').addEventListener('submit', function (e) {
        const checkboxes = document.querySelectorAll('input[name="deliveries[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert("Please select at least one order to deliver.");
        }
    });

</script>

</body>
</html>