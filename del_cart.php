<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['product_id'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID missing']);
        exit();
    }

    $email = $_SESSION['email'];

    // Find user id
    $stmt_user = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user = $result_user->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    $customer_id = $user['user_id'];
    $product_id = (int) $data['product_id'];

    // Delete from carts table
    $stmt = $conn->prepare("DELETE FROM carts WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $customer_id, $product_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
