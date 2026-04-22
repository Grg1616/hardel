<?php
session_start();
include 'config.php';

// Redirect if not logged in as driver
if (!isset($_SESSION['user_name']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user_name'];

// Get user ID from user table using email
$userQuery = $conn->prepare("SELECT user_id FROM user WHERE user_name = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $userResult->fetch_assoc();
$user_id = $user['user_id'];

// Get driver info using user_id
$driverQuery = $conn->prepare("SELECT * FROM driver WHERE user_id = ?");
$driverQuery->bind_param("i", $user_id);
$driverQuery->execute();
$driverResult = $driverQuery->get_result();

if ($driverResult->num_rows === 0) {
    echo "Driver profile not found.";
    exit();
}

$driver = $driverResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'navs/navdrivers.php'; ?>
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body text-center">
            <img src="<?= htmlspecialchars($driver['driver_image']) ?>" alt="Driver Image"
                 class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
            <h4 class="card-title">
                <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['middle_name'] . ' ' . $driver['last_name']) ?>
            </h4>
            <p class="text-muted mb-2">Phone: <?= htmlspecialchars($driver['phone']) ?></p>
            <a href="edit_driver_profile.php" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</div>

</body>
</html>
