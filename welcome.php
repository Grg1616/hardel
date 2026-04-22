<?php
ob_start();
session_start();


// Authentication and Authorization Check
if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'navs/usernav.php'; 
require 'config.php';

$customer_id = null;
if (isset($_SESSION['email'])) {
    $stmt = $conn->prepare("SELECT c.customer_id
FROM customer c
JOIN user u ON c.user_id = u.user_id
WHERE u.email = ?;
");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $customer_data = $result->fetch_assoc();
        $customer_id = $customer_data['customer_id'];
    }
}

if (!$customer_id) {
    // Handle case where customer ID couldn't be found
    die("Error: Could not find customer information.");
}

$all_categories = [
    "Appliances", "Automotive", "Building Materials", "Electrical",
    "Furniture", "Home Interior", "Houseware", "Outdoor Living",
    "Paints & Sundries", "Plumbing", "Sanitary Waves", "Tiles",
    "Tools", "Hardware"
];

$selected_category = $_GET['category'] ?? null;

$products = [];
if ($selected_category) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM products");
}

if ($result && $result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

if (isset($_SESSION['order_success'])) {
    $order_success = true;
    unset($_SESSION['order_success']);
} else {
    $order_success = false;
}

?>
    <style>
        .cart-icon {
            font-size: 24px;
            color: #333;
            transition: color 0.3s;
        }
        .cart-icon:hover {
            color: rgb(2, 37, 1);
        }
        .buynow-icon {
            font-size: 24px;
            color: #333;
            transition: color 0.3s;
        }
        .buynow-icon:hover {
            color:rgb(80, 76, 77);
        }
        .sidebar .nav-link {
            color: #333;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgb(98, 100, 102);
            color: #000;
            font-weight: bold;
        }
        .hover-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .input-group-q {
            display: flex;
            align-items: center;
        }
        .form-control-q {
            width: 50px;
            text-align: center;
        }
        .btn-q {
            width: 36px;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-gray sidebar p-3 border-end vh-100">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="welcome.php" class="nav-link <?= !$selected_category ? 'active fw-bold' : '' ?>">All Categories</a>
                </li>
                <?php foreach ($all_categories as $cat): ?>
                    <li class="nav-item">
                        <a href="?category=<?= urlencode($cat) ?>" class="nav-link <?= ($cat === $selected_category) ? 'active fw-bold' : '' ?>">
                            <?= htmlspecialchars($cat) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-3">
            <h2><?= $selected_category ? htmlspecialchars($selected_category) : "" ?></h2>
            <div class="row g-4 mt-2">
                <?php if ($selected_category && empty($products)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No products found in this category.</div>
                    </div>
                <?php endif; ?>

                <?php foreach ($products as $product): ?>
                    <?php
                        $productData = htmlspecialchars(json_encode([
                            'product_id' => $product['product_id'],
                            'name' => $product['name'],
                            'description' => $product['description'],
                            'category' => $product['category'],
                            'price' => $product['price'],
                            'stock_quantity' => $product['stock_quantity'],
                            'product_image' => $product['product_image'] ?: 'uploads/default.png'
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card hover-card h-100 shadow-sm" onclick='showDetails(<?= $productData ?>)' style="cursor:pointer;">
                            <img src="<?= htmlspecialchars($product['product_image'] ?: 'uploads/default.png') ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Product Image">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                                <p class="card-text small text-muted"><?= htmlspecialchars($product['category']) ?></p>
                                <div class="fw-bold">₱<?= number_format($product['price'], 2) ?></div>
                            </div>
                            <div class="card-footer ">
                                <button class="btn btn-secondary border border-dark" onclick="event.stopPropagation(); addToCart(<?= $productData ?>)">Add to cart</button>
                                <button class="btn btn-success border border-secondary" onclick="event.stopPropagation(); buyNow(<?= $productData ?>)">Buy Now</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-2">
      <div class="modal-header">
        <h5 class="modal-title" id="productDetailLabel">Product Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="detailImage" src="" class="img-fluid rounded mb-3" style="max-height: 250px;" alt="Product Image">
        <p><strong>Name:</strong> <span id="detailNameDisplay"></span></p>
        <p><strong>Description:</strong> <span id="detailDescDisplay"></span></p>
        <p><strong>Category:</strong> <span id="detailCatDisplay"></span></p>
        <p><strong>Price:</strong> ₱<span id="detailPriceDisplay"></span></p>
        <p><strong>Stock Quantity:</strong> <span id="detailStockDisplay"></span></p>
      </div>
    </div>
  </div>
</div>

<!-- Add to Cart Quantity Modal -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-2">
      <div class="modal-header">
        <h5 class="modal-title" id="addToCartLabel">Add to Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Product:</strong> <span id="cartProductName"></span></p>
        <p><strong>Price:</strong> ₱<span id="cartProductPrice"></span></p>
        <div class="mb-3">
          <label for="cartQuantity" class="form-label">Quantity:</label>
          <div class="input-group input-group-q">
              <button class="btn btn-outline-secondary btn-q" type="button" onclick="changeQuantity(-1)">-</button>
              <input type="number" class="form-control form-control-q text-center" id="cartQuantity" value="0" min="1" max="100" onchange="validateQuantity()">
              <button class="btn btn-outline-secondary btn-q" type="button" onclick="changeQuantity(1)">+</button>
          </div>
          <small id="stockMessage" class="text-muted"></small>
        </div>
        <button class="btn btn-success w-100" onclick="confirmAddToCart()">Add to Cart</button>
      </div>
    </div>
  </div>
</div>

<!-- Buy Now Quantity Modal -->
<div class="modal fade" id="buyNowModal" tabindex="-1" aria-labelledby="buyNowLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-2">
      <div class="modal-header">
        <h5 class="modal-title" id="buyNowLabel">Buy Now</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Product:</strong> <span id="buyNowProductName"></span></p>
        <p><strong>Price:</strong> ₱<span id="buyNowProductPrice"></span></p>
        <div class="mb-3">
          <label for="buyNowQuantity" class="form-label">Quantity:</label>
          <div class="input-group input-group-q">
              <button class="btn btn-outline-secondary btn-q" type="button" onclick="changeBuyNowQuantity(-1)">-</button>
              <input type="number" class="form-control form-control-q text-center" id="buyNowQuantity" value="0" min="1" max="100" onchange="validateBuyNowQuantity()">
              <button class="btn btn-outline-secondary btn-q" type="button" onclick="changeBuyNowQuantity(1)">+</button>
          </div>
          <small id="buyNowStockMessage" class="text-muted"></small>
        </div>
        <button class="btn btn-success w-100 " onclick="confirmBuyNow()">Buy Now</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div class="toast-container">
  <div id="cartToast" class="toast align-items-center text-white bg-success" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle-fill me-2"></i>
        <span id="toastMessage">Product added to cart!</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="orderSuccessToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Order Placed</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Your order has been placed successfully!
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentProductToAdd = null;
let currentProductToBuy = null;
let maxStock = 0;
let buyNowMaxStock = 0;

function showDetails(product) {
    document.getElementById('detailImage').src = product.product_image || 'uploads/default.png';
    document.getElementById('detailNameDisplay').innerText = product.name;
    document.getElementById('detailDescDisplay').innerText = product.description || 'No description';
    document.getElementById('detailCatDisplay').innerText = product.category;
    document.getElementById('detailPriceDisplay').innerText = parseFloat(product.price).toFixed(2);
    document.getElementById('detailStockDisplay').innerText = product.stock_quantity;

    new bootstrap.Modal(document.getElementById('productDetailModal')).show();
}

function addToCart(product) {
    currentProductToAdd = product;
    maxStock = product.stock_quantity;
    
    document.getElementById('cartProductName').innerText = product.name;
    document.getElementById('cartProductPrice').innerText = parseFloat(product.price).toFixed(2);
    document.getElementById('cartQuantity').value = 1;
    document.getElementById('cartQuantity').max = maxStock;
    document.getElementById('stockMessage').innerText = `Available: ${maxStock}`;
    
    new bootstrap.Modal(document.getElementById('addToCartModal')).show();
}

function buyNow(product) {
    currentProductToBuy = product;
    buyNowMaxStock = product.stock_quantity;
    
    document.getElementById('buyNowProductName').innerText = product.name;
    document.getElementById('buyNowProductPrice').innerText = parseFloat(product.price).toFixed(2);
    document.getElementById('buyNowQuantity').value = 1;
    document.getElementById('buyNowQuantity').max = buyNowMaxStock;
    document.getElementById('buyNowStockMessage').innerText = `Available: ${buyNowMaxStock}`;
    
    new bootstrap.Modal(document.getElementById('buyNowModal')).show();
}

function changeQuantity(change) {
    const quantityInput = document.getElementById('cartQuantity');
    let currentQuantity = parseInt(quantityInput.value) || 1;
    let newQuantity = currentQuantity + change;
    
    // Ensure quantity stays within bounds
    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > maxStock) newQuantity = maxStock;
    
    quantityInput.value = newQuantity;
}

function changeBuyNowQuantity(change) {
    const quantityInput = document.getElementById('buyNowQuantity');
    let currentQuantity = parseInt(quantityInput.value) || 1;
    let newQuantity = currentQuantity + change;
    
    // Ensure quantity stays within bounds
    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > buyNowMaxStock) newQuantity = buyNowMaxStock;
    
    quantityInput.value = newQuantity;
}

function validateQuantity() {
    const quantityInput = document.getElementById('cartQuantity');
    let quantity = parseInt(quantityInput.value) || 1;
    
    if (quantity < 1) quantity = 1;
    if (quantity > maxStock) quantity = maxStock;
    
    quantityInput.value = quantity;
}

function validateBuyNowQuantity() {
    const quantityInput = document.getElementById('buyNowQuantity');
    let quantity = parseInt(quantityInput.value) || 1;
    
    if (quantity < 1) quantity = 1;
    if (quantity > buyNowMaxStock) quantity = buyNowMaxStock;
    
    quantityInput.value = quantity;
}

function confirmAddToCart() {
    const quantity = parseInt(document.getElementById('cartQuantity').value) || 1;
    
    if (!currentProductToAdd) {
        showToast('Error: No product selected', 'danger');
        return;
    }
    
    if (quantity < 1) {
        showToast('Quantity must be at least 1', 'danger');
        return;
    }
    
    if (quantity > maxStock) {
        showToast(`Only ${maxStock} items available`, 'danger');
        return;
    }
    
    // AJAX call to add/update cart
    $.ajax({
        url: 'add_to_cart.php',
        type: 'POST',
        data: {
            product_id: currentProductToAdd.product_id,
            quantity: quantity,
            customer_id: <?= $customer_id ?>
        },
        dataType: 'json', // Expect JSON response
        success: function(response) {
            if (response.success) {
                showToast(response.message || 'Product added to cart!');
                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('addToCartModal')).hide();
            } else {
                showToast(response.message || 'Error adding to cart', 'danger');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error: ' + error;
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                // If we can't parse the JSON, use the default error message
            }
            showToast(errorMessage, 'danger');
        }
    });
}

// In your existing code, replace the confirmBuyNow() function with this:
function confirmBuyNow() {
    const quantity = parseInt(document.getElementById('buyNowQuantity').value) || 1;
    
    if (!currentProductToBuy) {
        showToast('Error: No product selected', 'danger');
        return;
    }
    
    if (quantity < 1) {
        showToast('Quantity must be at least 1', 'danger');
        return;
    }
    
    if (quantity > buyNowMaxStock) {
        showToast(`Only ${buyNowMaxStock} items available`, 'danger');
        return;
    }
    
    // Create a form to submit the data via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'checkout.php';
    
    // Create hidden input for selected_items
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'selected_items';
    input.value = JSON.stringify([{
        product_id: currentProductToBuy.product_id,
        quantity: quantity
    }]);
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('cartToast');
    const toastMessage = document.getElementById('toastMessage');
    
    // Set message and color
    toastMessage.innerText = message;
    toast.className = `toast align-items-center text-white bg-${type}`;
    
    // Show the toast
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toast);
    toastBootstrap.show();
}

<?php if ($order_success): ?>
    $(document).ready(function() {
        var toast = new bootstrap.Toast(document.getElementById('orderSuccessToast'));
        toast.show();
    });
<?php endif; ?>

</script>

</body>
</html>