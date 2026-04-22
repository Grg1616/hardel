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

// Get the logged-in user's email
// Get the logged-in user's email
$email = $_SESSION['email'];

// Fetch the customer ID based on the user email
$stmt_user = $conn->prepare("
    SELECT c.customer_id 
    FROM customer c 
    JOIN user u ON c.user_id = u.user_id 
    WHERE u.email = ?
");
$stmt_user->bind_param("s", $email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$customer = $result_user->fetch_assoc();

if ($customer) {
    $customer_id = $customer['customer_id'];

    $stmt = $conn->prepare("
        SELECT p.product_id, p.name, p.product_image, c.quantity, c.price
        FROM carts c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total_price = 0;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
            $total_price += $row['price'] * $row['quantity'];
        }
    }
} else {
    $cart_items = [];
    $total_price = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_item'])) {
        $product_id = $_POST['product_id'];
        
        $stmt_delete = $conn->prepare("DELETE FROM carts WHERE customer_id = ? AND product_id = ?");
        $stmt_delete->bind_param("ii", $customer_id, $product_id);
        
        if ($stmt_delete->execute()) {
            header("Location: cart.php");
            exit();
        } else {
            echo "Error deleting item: " . $conn->error;
        }
    }
    // Add this new section to handle quantity updates
    elseif (isset($_POST['update_quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        
        $stmt_update = $conn->prepare("UPDATE carts SET quantity = ? WHERE customer_id = ? AND product_id = ?");
        $stmt_update->bind_param("iii", $quantity, $customer_id, $product_id);
        
        if ($stmt_update->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit();
    }
}

?>

<?php 
include 'includes/header.php';
include 'navs/usernav.php';  ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .cart-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 8px;
        }
        .cart-item-details {
            flex: 1;
        }
        .item-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .item-price, .item-subtotal {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            font-size: 18px;
            text-align: center;
            line-height: 30px;
            background: #ddd;
            border: none;
            border-radius: 4px;
            margin: 0 5px;
            cursor: pointer;
        }
        .quantity-number {
            min-width: 30px;
            text-align: center;
            font-size: 16px;
        }
        .select-item {
            width: 24px;
            height: 24px;
            border: 2px solid #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            cursor: pointer;
        }
        .select-item.checked {
            background-color: #007bff;
            color: white;
        }
        .total-section {
            text-align: right;
            margin-top: 30px;
            font-size: 20px;
            font-weight: bold;
        }
        .checkout-btn {
            display: block;
            margin-left: auto;
            margin-top: 20px;
            background-color: #007bff;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }
        .checkout-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>Your Shopping Cart</h2>
        
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>

        

        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item" data-price="<?= $item['price'] ?>" data-product-id="<?= $item['product_id'] ?>">
                <div class="select-item"></div>
                <img src="<?= htmlspecialchars($item['product_image'] ?: 'uploads/default.png') ?>" alt="Product Image">
                <div class="cart-item-details">
                    <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="item-price">Price: ₱<?= number_format($item['price'], 2) ?></div>
                    <div class="quantity-control">
                        <button class="quantity-btn minus">-</button>
                        <div class="quantity-number"><?= htmlspecialchars($item['quantity']) ?></div>
                        <button class="quantity-btn plus">+</button>
                    </div>
                    <div class="item-subtotal">Subtotal: ₱<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                </div>

                <!-- 👇 ADD DELETE BUTTON HERE -->
                <button class="delete-btn" style="background: none; border: none; color: red; font-size: 20px; margin-left: 10px; cursor: pointer;">🗑️</button>

            </div>
        <?php endforeach; ?>

        <div class="total-section">
            Total Price: ₱<span id="total-price">0.00</span>
        </div>

        <button class="checkout-btn" id="checkout-btn">Proceed to Checkout</button>

    <?php endif; ?>
    <form id="checkout-form" method="post" action="checkout.php" style="display: none;">
    <input type="hidden" name="selected_items" id="selected-items-input">
</form>
</div>

<script>
    const cartItems = document.querySelectorAll('.cart-item');
    const totalPriceElement = document.getElementById('total-price');

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const selectBtn = item.querySelector('.select-item');
            if (selectBtn.classList.contains('checked')) {
                const price = parseFloat(item.dataset.price);
                const quantity = parseInt(item.querySelector('.quantity-number').innerText);
                total += price * quantity;
            }
        });
        totalPriceElement.innerText = total.toFixed(2);
    }

    cartItems.forEach(item => {
        const selectBtn = item.querySelector('.select-item');
        const plusBtn = item.querySelector('.plus');
        const minusBtn = item.querySelector('.minus');
        const quantityNumber = item.querySelector('.quantity-number');
        const subtotalElement = item.querySelector('.item-subtotal');
        const price = parseFloat(item.dataset.price);
        const deleteBtn = item.querySelector('.delete-btn'); // 👈 Select delete button

        selectBtn.addEventListener('click', () => {
            selectBtn.classList.toggle('checked');
            updateTotal();
        });

        plusBtn.addEventListener('click', () => {
            let quantity = parseInt(quantityNumber.innerText);
            quantity++;
            quantityNumber.innerText = quantity;
            subtotalElement.innerText = "Subtotal: ₱" + (price * quantity).toFixed(2);
            updateTotal();
        });

        minusBtn.addEventListener('click', () => {
            let quantity = parseInt(quantityNumber.innerText);
            if (quantity > 1) {
                quantity--;
                quantityNumber.innerText = quantity;
                subtotalElement.innerText = "Subtotal: ₱" + (price * quantity).toFixed(2);
                updateTotal();
            }
        });

        deleteBtn.addEventListener('click', () => {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const productId = item.dataset.productId;
        
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `delete_item=true&product_id=${productId}`
        })
        .then(response => {
            if (response.ok) {
                // Remove the item from the UI immediately
                item.remove();
                updateTotal();
            } else {
                alert('Error removing item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong');
        });
    }
});

document.getElementById('checkout-btn').addEventListener('click', function() {
        const selectedItems = [];
        
        document.querySelectorAll('.cart-item').forEach(item => {
            const selectBtn = item.querySelector('.select-item');
            if (selectBtn.classList.contains('checked')) {
                const productId = item.dataset.productId;
                const quantity = parseInt(item.querySelector('.quantity-number').innerText);
                
                selectedItems.push({
                    product_id: productId,
                    quantity: quantity
                });
            }
        });
        
        if (selectedItems.length === 0) {
            alert('Please select at least one item to checkout');
            return;
        }
        
        // Set the selected items in the hidden form and submit
        document.getElementById('selected-items-input').value = JSON.stringify(selectedItems);
        document.getElementById('checkout-form').submit();
    });

    });

</script>

</body>
</html>