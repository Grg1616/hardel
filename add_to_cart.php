<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['email'])) {
        throw new Exception('Not logged in');
    }

    if (!isset($_POST['product_id']) || !isset($_POST['quantity']) || !isset($_POST['customer_id'])) {
        throw new Exception('Invalid request');
    }

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $customer_id = intval($_POST['customer_id']);

    // Check if product exists and has enough stock
    $stmt = $conn->prepare("SELECT stock_quantity, price FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }

    $product = $result->fetch_assoc();
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Not enough stock available');
    }

    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT * FROM carts WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Replace quantity instead of increment
        $stmt = $conn->prepare("UPDATE carts SET quantity = ? WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $customer_id, $product_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO carts (customer_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $price = $product['price'];
        $stmt->bind_param("iiid", $customer_id, $product_id, $quantity, $price);
    }
    

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Cart updated successfully';
    } else {
        throw new Exception('Error updating cart: ' . $conn->error);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();