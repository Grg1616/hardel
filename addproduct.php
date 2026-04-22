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


// Handle Edit/Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        $id = $_POST['product_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['message' => 'Product deleted successfully.']);
        exit;
    }

    if ($_POST['action'] === 'edit') {
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $desc = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category = $_POST['category'];

        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock_quantity=?, category=? WHERE product_id=?");
        $stmt->bind_param("ssdisi", $name, $desc, $price, $stock, $category, $id);
        $stmt->execute();
        echo json_encode(['message' => 'Product updated successfully.']);
        exit;
    }
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['product_description'];
    $price = $_POST['product_price'];
    $stock = $_POST['product_stock'];
    $category = $_POST['product_category'];
    $image = $_FILES['product_image'];
    $imagePath = null;

    if ($image['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $imagePath = 'uploads/' . $filename;
        move_uploaded_file($image['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, category, product_image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdiss", $name, $desc, $price, $stock, $category, $imagePath);
    $stmt->execute();

    echo json_encode(['message' => 'Product added successfully.']);
    exit;
}

// Fetch products from DB
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>
    <style>
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        #popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        z-index: 100;
        width: 80%;
        max-width: 500px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99;
    }
    #productDetailPopup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    z-index: 100;
    width: 80%;
    max-width: 600px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
}
    </style>

<body>
    
<?php include 'navs/navadmin.php'?>
<div class="container py-5 mt-5">
    <h3 class="mb-3">Manage Products</h3>

    <!-- Button to Add Product -->
    <button class="btn btn-primary mb-3" onclick="showPopup()">Add Product</button>

    <!-- Table for Displaying Products -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Category</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="productTable">
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td>₱<?= number_format($product['price'], 2) ?></td>
                    <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                    <td><?= htmlspecialchars($product['category']) ?></td>
                    <td><img src="<?= htmlspecialchars($product['product_image']) ?: 'uploads/default.png' ?>" class="product-image" alt="Product Image"></td>
                    <td>
                        <button class="btn btn-secondary" onclick='showDetails(<?= htmlspecialchars(json_encode($product)) ?>)'>Details</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="overlay" onclick="hidePopup(); hideDetails();"></div>

<!-- Add Product Popup -->
<div id="popup">
    <h3>Add Product</h3>
    <form id="productForm" enctype="multipart/form-data">
        <input type="hidden" name="product_id" id="product_id">
        <div class="form-group">
            <input type="text" class="form-control" name="product_name" id="product_name" placeholder="Name" required>
        </div>
        <div class="form-group">
            <textarea class="form-control" name="product_description" id="product_description" placeholder="Description" required></textarea>
        </div>
        <div class="form-group">
            <input type="number" class="form-control" name="product_price" id="product_price" step="0.01" placeholder="Price" required>
        </div>
        <div class="form-group">
            <input type="number" class="form-control" name="product_stock" id="product_stock" placeholder="Quantity" required>
        </div>
        <div class="form-group">
            <select class="form-control" name="product_category" id="product_category" required>
                <option value="">-- Select Category --</option>
                <option>Appliances</option>
                <option>Automotive</option>
                <option>Building Materials</option>
                <option>Electrical</option>
                <option>Furniture</option>
                <option>Home Interior</option>
                <option>Houseware</option>
                <option>Outdoor Living</option>
                <option>Paints & Sundries</option>
                <option>Plumbing</option>
                <option>Sanitary Waves</option>
                <option>Tiles</option>
                <option>Tools</option>
                <option>Hardware</option>
            </select>
        </div>
        <div class="form-group">
            <label for="product_image">Image:</label>
            <input type="file" class="form-control" name="product_image" id="product_image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-success">Add Product</button>
        <button type="button" class="btn btn-secondary" onclick="hidePopup()">Cancel</button>
    </form>
</div>

<!-- Product Detail Popup -->
<div id="productDetailPopup">
    <h3>Product Details</h3>
    <img id="detailImage" src="" alt="Product Image" class="product-image mb-3">
    
    <div class="detail-item">
        <strong>Name:</strong> <span id="detailNameDisplay"></span>
    </div>
    
    <div class="detail-item">
        <strong>Description:</strong> <span id="detailDescDisplay"></span>
    </div>
    
    <div class="detail-item">
        <strong>Category:</strong> <span id="detailCatDisplay"></span>
    </div>
    
    <div class="detail-item">
        <strong>Price:</strong> <span id="detailPriceDisplay"></span>
    </div>
    
    <div class="detail-item">
        <strong>Stock Quantity:</strong> <span id="detailStockDisplay"></span>
    </div>

    <div class="button-group mt-3">
        <button class="btn btn-warning" onclick="toggleEdit()">Edit</button>
        <button class="btn btn-danger" onclick="deleteProduct(currentProductId)">Delete</button>
        <button id="saveButton" class="btn btn-success" style="display: none;" onclick="saveChanges()">Save</button>
        <button class="btn btn-secondary" onclick="hideDetails()">Close</button>
    </div>
</div>

<script>
let currentProductId = null;

function showPopup() {
        document.getElementById('popup').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
        // Reset form when showing popup
        document.getElementById('productForm').reset();
        document.getElementById('product_id').value = '';
        document.getElementById('productForm button[type="submit"]').innerText = 'Add Product';
    }

    function hidePopup() {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

function showDetails(product) {
    currentProductId = product.product_id;
    const popup = document.getElementById('productDetailPopup');
    
    // Set display content
    popup.querySelector('#detailImage').src = product.product_image || 'uploads/default.png';
    popup.querySelector('#detailNameDisplay').textContent = product.name;
    popup.querySelector('#detailDescDisplay').textContent = product.description;
    popup.querySelector('#detailCatDisplay').textContent = product.category;
    popup.querySelector('#detailPriceDisplay').textContent = `₱${parseFloat(product.price).toFixed(2)}`;
    popup.querySelector('#detailStockDisplay').textContent = product.stock_quantity;

    // Reset to view mode
    const saveButton = popup.querySelector('#saveButton');
    saveButton.style.display = 'none';
    popup.querySelectorAll('.edit-input').forEach(el => el.remove());

    // Show popup
    popup.style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}
function hideDetails() {
    document.getElementById('productDetailPopup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// Improved toggleEdit function
function toggleEdit() {
    const popup = document.getElementById('productDetailPopup');
    const elements = {
        name: popup.querySelector('#detailNameDisplay'),
        desc: popup.querySelector('#detailDescDisplay'),
        category: popup.querySelector('#detailCatDisplay'),
        price: popup.querySelector('#detailPriceDisplay'),
        stock: popup.querySelector('#detailStockDisplay')
    };

    if (!popup.querySelector('#detailNameInput')) {
        // Switch to edit mode
        Object.entries(elements).forEach(([key, el]) => {
            const input = key === 'category' ? createCategorySelect(el.textContent) : 
                        key === 'price' ? createPriceInput(el.textContent) :
                        createGenericInput(el.textContent, key);
            input.classList.add('edit-input');
            el.parentNode.replaceChild(input, el);
        });

        popup.querySelector('#saveButton').style.display = 'inline-block';
    } else {
        // Switch back to view mode (if needed)
        showDetails(currentProduct);
    }
}

// Helper functions for input creation
function createCategorySelect(value) {
    const select = document.createElement('select');
    select.className = 'form-control';
    select.id = 'detailCatInput';
    const categories = ['Appliances', 'Automotive', 'Building Materials', 'Electrical', 
                       'Furniture', 'Home Interior', 'Houseware', 'Outdoor Living', 
                       'Paints & Sundries', 'Plumbing', 'Sanitary Waves', 'Tiles', 
                       'Tools', 'Hardware'];
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        option.selected = cat === value;
        select.appendChild(option);
    });
    return select;
}

function createPriceInput(value) {
    const input = document.createElement('input');
    input.type = 'number';
    input.step = '0.01';
    input.value = parseFloat(value.replace('₱', '')).toFixed(2);
    input.className = 'form-control';
    input.id = 'detailPriceInput';
    return input;
}

function createGenericInput(value, type) {
    const input = document.createElement(type === 'desc' ? 'textarea' : 'input');
    input.type = type === 'price' ? 'number' : 'text';
    input.value = value;
    input.className = 'form-control';
    input.id = `detail${type.charAt(0).toUpperCase() + type.slice(1)}Input`;
    return input;
}

// Update the saveChanges() function to collect input values
function saveChanges() {
    const name = document.getElementById('detailNameInput').value;
    const desc = document.getElementById('detailDescInput').value;
    const category = document.getElementById('detailCatInput').value;
    const price = parseFloat(document.getElementById('detailPriceInput').value).toFixed(2);
    const stock = parseInt(document.getElementById('detailStockInput').value);

    $.ajax({
        url: 'addproduct.php',
        type: 'POST',
        data: { 
            action: 'edit',
            product_id: currentProductId,
            name: name,
            description: desc,
            category: category,
            price: price,
            stock: stock
        },
        success: function(response) {
            alert(response.message);
            location.reload(); // Reload to reflect changes
        },
        error: function() {
            alert('Error updating product.');
        }
    });
}

function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        $.ajax({
            url: 'addproduct.php',
            type: 'POST',
            data: { action: 'delete', product_id: productId },
            success: function(response) {
                alert(response.message);
                location.reload(); // Reload the page to see the updated product list
            },
            error: function() {
                alert('Error occurred while deleting the product.');
            }
        });
    }
}

// Handle form submission for adding a new product
$(document).ready(function() {
    $('#productForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this);
        $.ajax({
            url: 'addproduct.php', // Your PHP script to handle actions
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert(response.message);
                location.reload(); // Reload the page to see the new product
            },
            error: function() {
                alert('Error occurred while adding the product.');
            }
        });
    });
});

// Close modal when clicking outside
document.getElementById('overlay').addEventListener('click', function() {
    hidePopup();
    hideDetails();
});

// Prevent modal close when clicking inside the popup
document.querySelectorAll('#popup, #productDetailPopup').forEach(function(element) {
    element.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
</body>
