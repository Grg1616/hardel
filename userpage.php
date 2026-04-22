<?php
session_start();
include 'config.php';
include 'includes/header.php';


if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// First get the user_id from the user table
$sql = "SELECT user_id FROM user WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows !== 1) {
    echo "<div class='alert alert-danger'>User account not found.</div>";
    exit();
}

$userAccount = $userResult->fetch_assoc();
$user_id = $userAccount['user_id'];

// Now get customer profile data from `customer` table using the user_id
$sql = "SELECT * FROM customer WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>User profile not found.</div>";
    exit();
}

$addressComponents = [
    'street' => $user['street'] ?? '',
    'barangay' => $user['barangay'] ?? '',
    'municipality' => $user['municipalities'] ?? '',
    'province' => $user['province'] ?? 'Batangas'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<?php include 'navs/usernav.php'; ?>
<div class="container">
    <h2 class="mb-4">User Profile</h2>

    <!-- Email (readonly) -->
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
    </div>

    <!-- Name (readonly) -->
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" disabled>
    </div>

    <!-- Phone (readonly) -->
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" disabled>
    </div>

    <!-- Address (readonly) -->
    <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text" class="form-control" 
               value="<?= htmlspecialchars(
                   $addressComponents['province'] . ', ' . 
                   $addressComponents['municipality'] . ', ' . 
                   $addressComponents['barangay'] . ', ' . 
                   $addressComponents['street']
               ) ?>" 
               disabled>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="edituserpage.php" class="btn btn-primary">Edit</a>
        <a href="welcome.php" class="btn btn-secondary">Back</a>
    </div>
</div>
</body>
</html>