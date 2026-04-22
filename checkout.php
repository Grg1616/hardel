<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Get customer ID
$customer_id = null;
if (isset($_SESSION['email'])) {
    $stmt = $conn->prepare("SELECT c.customer_id FROM customer c JOIN user u ON c.user_id = u.user_id WHERE u.email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $customer_data = $result->fetch_assoc();
        $customer_id = $customer_data['customer_id'];
    }
}

if (!$customer_id) {
    die("Error: Could not find customer information.");
}

// Check if form was submitted (order placed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

            $stmt = $conn->prepare("SELECT province, municipalities, barangay, street 
            FROM customer 
            WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer_address = $result->fetch_assoc();

        $required_fields = ['province', 'municipalities', 'barangay', 'street'];
        foreach ($required_fields as $field) {
        if (empty($customer_address[$field])) {
        $_SESSION['address_error'] = "Please complete your delivery address before placing an order";
        header("Location: edituserpage.php");
        exit();
        }
        }

    // Get the selected items from POST data
    $selected_items = json_decode($_POST['selected_items'], true);
    $payment_method = $_POST['payment_method'] ?? 'cod';
    
    if (empty($selected_items)) {
        die("Error: No products selected for checkout.");
    }
    
    // Calculate totals
    $subtotal = 0;
    $shipping = 50.00; // Fixed shipping fee
    $product_details = [];
    
    foreach ($selected_items as $item) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $item['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            die("Error: Product not found.");
        }
        
        $product = $result->fetch_assoc();
        $product['quantity'] = $item['quantity'];
        $product_details[] = $product;
        $subtotal += $product['price'] * $item['quantity'];
    }
    
    $total = $subtotal + $shipping;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Create the order
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, order_status, payment_method, subtotal, shipping_fee, total_amount) 
                                VALUES (?, NOW(), 'Pending', ?, ?, ?, ?)");
        $stmt->bind_param("isddd", $customer_id, $payment_method, $subtotal, $shipping, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // 2. Add order items
        foreach ($product_details as $product) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                    VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $product['product_id'], $product['quantity'], $product['price']);
            $stmt->execute();
            
            // 3. Update product stock
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $stmt->bind_param("ii", $product['quantity'], $product['product_id']);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to welcome.php with success message
        $_SESSION['order_success'] = true;
        header("Location: welcome.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "Error placing order: " . $e->getMessage();
    }
}

// Get selected items from POST
$selected_items = [];
if (isset($_POST['selected_items'])) {
    $selected_items = json_decode($_POST['selected_items'], true);
}

if (empty($selected_items)) {
    die("Error: No products selected for checkout.");
}

// Fetch product details for display
$product_details = [];
$subtotal = 0;
$shipping = 50.00;

foreach ($selected_items as $item) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $item['product_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Error: Product not found.");
    }
    
    $product = $result->fetch_assoc();
    $product['quantity'] = $item['quantity'];
    $product_details[] = $product;
    $subtotal += $product['price'] * $item['quantity'];
}

$total = $subtotal + $shipping;
?>

<!-- Rest of your HTML remains the same -->

<?php include 'includes/header.php'; ?>
<?php include 'navs/usernav.php'; ?>
    <style>
        .product-img {
            max-height: 150px;
            object-fit: contain;
        }
        .quantity-control {
            display: flex;
            align-items: center;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
            margin: 0 10px;
        }
        .payment-method {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-method.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .payment-method.disabled {
            cursor: not-allowed;
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        .payment-method.disabled:hover {
            border-color: #dee2e6;
            background-color: #f8f9fa;
        }
        .payment-method i {
            font-size: 24px;
            margin-right: 10px;
        }
        .summary-card {
            position: sticky;
            top: 20px;
        }
        .coming-soon {
            font-size: 0.8rem;
            color: #dc3545;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <h2 class="mb-4">Checkout</h2>
        
        <form method="POST" action="checkout.php">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            
            <div class="row">
                <!-- Product and Payment Details -->
                <div class="col-lg-8">
                <div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Product Details</h5>
    </div>
    <div class="card-body">
        <?php foreach ($product_details as $product): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <img src="<?= htmlspecialchars($product['product_image'] ?: 'uploads/default.png') ?>" 
                         class="img-fluid product-img rounded" alt="Product Image">
                </div>
                <div class="col-md-6">
                    <h5><?= htmlspecialchars($product['name']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($product['category']) ?></p>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                </div>
                <div class="col-md-3">
                    <div class="d-flex flex-column">
                        <div class="mb-3">
                            <label class="form-label">Quantity:</label>
                            <input type="number" class="form-control quantity-input" 
                                   value="<?= $product['quantity'] ?>" readonly>
                        </div>
                        <div class="fw-bold h5 text-end">
                            ₱<?= number_format($product['price'] * $product['quantity'], 2) ?>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add hidden input for selected items -->
<input type="hidden" name="selected_items" value="<?= htmlspecialchars(json_encode($selected_items)) ?>">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-method selected" onclick="selectPayment('cod')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cod" checked>
                                    <label class="form-check-label fw-bold" for="cod">
                                        <i class="bi bi-cash"></i> Cash on Delivery (COD)
                                    </label>
                                    <p class="ms-4 mt-2 mb-0 text-muted">Pay with cash upon delivery</p>
                                </div>
                            </div>
                            
                            <div class="payment-method disabled" onclick="return false;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="gcash" value="gcash" disabled>
                                    <label class="form-check-label fw-bold" for="gcash">
                                        <i class="bi bi-phone"></i> GCash
                                    </label>
                                    <p class="ms-4 mt-2 mb-0 text-muted">Pay using your GCash account</p>
                                    <span class="ms-4 coming-soon">Coming Soon</span>
                                </div>
                            </div>
                            
                            <div class="payment-method disabled" onclick="return false;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="card" value="card" disabled>
                                    <label class="form-check-label fw-bold" for="card">
                                        <i class="bi bi-credit-card"></i> Credit/Debit Card
                                    </label>
                                    <p class="ms-4 mt-2 mb-0 text-muted">Pay with your credit or debit card</p>
                                    <span class="ms-4 coming-soon">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card summary-card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₱<span id="subtotalDisplay"><?= number_format($subtotal, 2) ?></span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping Fee:</span>
                                <span>₱<span id="shippingDisplay"><?= number_format($shipping, 2) ?></span></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold h5">
                                <span>Total:</span>
                                <span>₱<span id="totalDisplay"><?= number_format($total, 2) ?></span></span>
                            </div>
                            <button type="submit" name="place_order" class="btn btn-success w-100 mt-3 py-2">
                                Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function updateQuantity(change) {
            const quantityInput = document.getElementById('quantity');
            let currentQuantity = parseInt(quantityInput.value) || 1;
            let newQuantity = currentQuantity + change;
            const maxQuantity = <?= $product['stock_quantity'] ?>;
            
            // Validate quantity
            if (newQuantity < 1) newQuantity = 1;
            if (newQuantity > maxQuantity) newQuantity = maxQuantity;
            
            quantityInput.value = newQuantity;
            updateTotals(newQuantity);
        }
        
        function updateTotals(quantity) {
            const price = <?= $product['price'] ?>;
            const shipping = 50.00;
            const subtotal = price * quantity;
            const total = subtotal + shipping;
            
            // Update the displayed totals
            document.querySelector('.card-body .fw-bold.h5.text-end').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
            document.getElementById('totalDisplay').textContent = total.toFixed(2);
        }
        
        function selectPayment(method) {
            // Only allow selection of enabled methods
            if (method === 'cod') {
                // Remove selected class from all payment methods
                document.querySelectorAll('.payment-method').forEach(el => {
                    el.classList.remove('selected');
                });
                
                // Add selected class to clicked method
                event.currentTarget.classList.add('selected');
                
                // Update the radio button
                document.getElementById(method).checked = true;
            }
        }
        
        // Update totals when quantity input changes
        document.getElementById('quantity').addEventListener('change', function() {
            const quantity = parseInt(this.value) || 1;
            const maxQuantity = <?= $product['stock_quantity'] ?>;
            
            if (quantity < 1) this.value = 1;
            if (quantity > maxQuantity) this.value = maxQuantity;
            
            updateTotals(quantity);
        });
    </script>

</body>
</html>