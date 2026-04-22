<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'config.php';

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Dashboard Statistics
$stats = [
    'total_sales' => $conn->query("SELECT SUM(total_amount) AS total FROM orders")->fetch_assoc()['total'],
    'total_orders' => $conn->query("SELECT COUNT(order_id) AS total FROM orders")->fetch_assoc()['total'],
    'total_customers' => $conn->query("SELECT COUNT(customer_id) AS total FROM customer")->fetch_assoc()['total'],
    'total_drivers' => $conn->query("SELECT COUNT(driver_id) AS total FROM driver")->fetch_assoc()['total'],
    'pending_orders' => $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE order_status = 'pending'")->fetch_assoc()['total'],
    'low_stock' => $conn->query("SELECT COUNT(product_id) AS total FROM products WHERE stock_quantity < 10")->fetch_assoc()['total']
];

// Recent Data
$recent_orders = $conn->query("
    SELECT o.order_id, o.total_amount, o.order_status, c.name AS customer_name
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$low_stock = $conn->query("
    SELECT name, stock_quantity, product_image 
    FROM products 
    WHERE stock_quantity < 10 
    ORDER BY stock_quantity ASC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Sales Trend Data (Last 7 Days)
$sales_trend = $conn->query("
    SELECT 
        DATE(order_date) AS date,
        SUM(total_amount) AS daily_sales,
        COUNT(order_id) AS orders_count
    FROM orders
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(order_date)
    ORDER BY date ASC
")->fetch_all(MYSQLI_ASSOC);

// Order Status Distribution
$status_distribution = $conn->query("
    SELECT 
        order_status,
        COUNT(order_id) AS count,
        SUM(total_amount) AS total
    FROM orders
    GROUP BY order_status
")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
        }
        
        body {
            background-color: #f5f6fa;
            min-height: 100vh;
            
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), #1a2533);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            margin-top: 50px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .recent-orders {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .low-stock-item {
            transition: all 0.3s ease;
        }
        
        .low-stock-item:hover {
            background-color: #fff3cd !important;
        }
    </style>
</head>
<body>

<?php include 'navs/navadmin.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <h1 class="display-4 mb-4">Admin Dashboard</h1>
        <div class="d-flex align-items-center gap-4">
            <div class="avatar">
                <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
            </div>
            <div>
                <h3><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></h3>
                <p class="mb-0"><?= htmlspecialchars($_SESSION['email']) ?></p>
            </div>
        </div>
    </div>
</div>


    <div class="container">
        <!-- Quick Stats -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <h5 class="text-muted">Total Sales</h5>
                    <h2>₱<?= number_format($stats['total_sales'], 2) ?></h2>
                    <span class="text-success"><i class="bi bi-currency-dollar"></i> All Time Sales</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <h5 class="text-muted">Total Orders</h5>
                    <h2><?= number_format($stats['total_orders']) ?></h2>
                    <span class="text-primary"><i class="bi bi-cart"></i> All Orders</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <h5 class="text-muted">Active Customers</h5>
                    <h2><?= number_format($stats['total_customers']) ?></h2>
                    <span class="text-info"><i class="bi bi-people"></i> Registered Users</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <h5 class="text-muted">Pending Orders</h5>
                    <h2><?= number_format($stats['pending_orders']) ?></h2>
                    <span class="text-warning"><i class="bi bi-clock-history"></i> Needs Action</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="chart-container">
                    <h5 class="mb-4">Sales Trend (Last 7 Days)</h5>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-container">
                    <h5 class="mb-4">Order Status Distribution</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Data Row -->
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="recent-orders">
                    <h5 class="mb-4">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['order_id'] ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="status-badge 
                                            <?= $order['order_status'] == 'complete' ? 'bg-success' : 
                                               ($order['order_status'] == 'pending' ? 'bg-warning' : 
                                               'bg-danger') ?>">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="recent-orders">
                    <h5 class="mb-4">Low Stock Alerts</h5>
                    <div class="list-group">
                        <?php foreach ($low_stock as $product): ?>
                        <a href="products.php" class="list-group-item list-group-item-action low-stock-item">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($product['product_image']) ?>" 
                                     class="rounded me-3" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                    <small class="text-danger">
                                        Stock: <?= $product['stock_quantity'] ?> remaining
                                    </small>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
                            <!-- Bootstrap JS Bundle (includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($sales_trend, 'date')) ?>,
                datasets: [{
                    label: 'Daily Sales',
                    data: <?= json_encode(array_column($sales_trend, 'daily_sales')) ?>,
                    borderColor: '#3498db',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(52, 152, 219, 0.1)'
                }]
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($status_distribution, 'order_status')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($status_distribution, 'count')) ?>,
                    backgroundColor: [
                        '#f1c40f', '#2ecc71', '#e74c3c', '#3498db'
                    ]
                }]
            }
        });
    </script>
</body>
</html>