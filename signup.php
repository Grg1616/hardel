<?php
session_start();
include "config.php";
include 'includes/header.php';

$error = '';
$email = $_SESSION['email'] ?? '';

// Redirect if no email in session
if (empty($email)) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validation
    if (empty($email)) {
        $error = "Email is required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email or username exists
        $stmt = $conn->prepare("SELECT email FROM user WHERE email = ? OR user_name = ?");
        if (!$stmt) {
            $error = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email or username already registered!";
            } else {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // 1. Insert into user table
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $user_type = 'customer'; // Set user type
                    $insert_user = $conn->prepare("INSERT INTO user (user_name, email, password, user_type) VALUES (?, ?, ?, ?)");
                    $insert_user->bind_param("ssss", $username, $email, $hashed_password, $user_type);
                    
                    if (!$insert_user->execute()) {
                        throw new Exception("User registration failed.");
                    }
                    
                    // 2. Get the new user ID
                    $user_id = $conn->insert_id;
                    
                    // 3. Insert minimal customer record
                    $insert_customer = $conn->prepare("INSERT INTO customer (user_id) VALUES (?)");
                    $insert_customer->bind_param("i", $user_id);
                    
                    if (!$insert_customer->execute()) {
                        throw new Exception("Customer profile initialization failed.");
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    
                    // Get complete user data
                    $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    
                    // Set session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $user['user_id'];
                    $_SESSION["user_name"] = $user['user_name'];
                    $_SESSION["email"] = $user['email'];
                    $_SESSION["user_type"] = $user['user_type'];
                    
                    header("Location: edituserpage.php");
                    exit();
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Registration failed. Please try again.";
                }
                
                if (isset($insert_user)) $insert_user->close();
                if (isset($insert_customer)) $insert_customer->close();
            }
            $stmt->close();
        }
    }
}
?>

<style>
    body { font-family: Arial; background: #f4f4f4; }
    .signup-container {
        width: 320px; margin: auto; margin-top: 60px;
        padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 10px gray;
    }
    input { 
        width: 100%; 
        padding: 10px; 
        margin: 8px 0; 
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .error { 
        color: red; 
        margin-bottom: 15px;
        text-align: center;
    }
    .btn-dark-gray {
        background-color: #3f3d56;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
    }
    .btn-dark-gray:hover {
        background-color: #2f2d46;
    }
</style>
</head>
<body>

<div class="signup-container">
    <img src="img/logo.png" alt="" class="logo" style="width: 100px; height: 100px; margin: auto; display: block;" />
    <h2 style="color: rgb(34, 39, 34); text-align: center;">Create Account</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password (min 8 characters)" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        <input type="submit" value="Register" class="btn btn-dark-gray"/>
    </form>
</div>