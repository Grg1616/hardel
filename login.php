<?php
ob_start();
session_start();
include 'includes/header.php';
include 'config.php';


// Check if user is already logged in
if (isset($_SESSION['email']) || isset($_SESSION['user_name'])) {
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
    $Username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

    $query = "SELECT * FROM user WHERE email = ? OR user_name = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $Username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['user_type'] === 'driver') {
                header("Location: driver.php");
                exit();
            } elseif ($row['user_type'] === 'customer') {
                header("Location: welcome.php");
                exit();
            } elseif ($row['user_type'] === 'admin') {
                header("Location: admindash.php");
                exit();
            }
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $input = trim($_POST['email']);
    $password = $_POST['password'];

    // Check user by email or username
    $query = "SELECT * FROM user WHERE email = ? OR user_name = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "ss", $input, $input);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Compare password (assuming it's hashed)
            if (password_verify($password, $row['password'])) {
                $_SESSION['email'] = $row['email'];
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['user_type'] = $row['user_type']; 
                
                // Redirect based on user type
                if ($row['user_type'] === 'driver') {
                    header("Location: driver.php");
                } elseif ($row['user_type'] === 'customer') {
                    header("Location: welcome.php");
                } elseif ($row['user_type'] === 'admin') {
                    header("Location: admindash.php");
                }
                exit();
            } else {
                $_SESSION['passError'] = "Incorrect password.";
            }
        } else {
            $_SESSION['userError'] = "Account not found.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle sign-in form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    $query = "SELECT * FROM user WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['signupError'] = "Email is already registered.";
        } else {
            $code = rand(1000, 9999);

            // Store the code in verify table
            $stmt = $conn->prepare("INSERT INTO verify (email, code) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $code);
            $stmt->execute();

            // Set session and redirect
            $_SESSION['email'] = $email;
            header("Location: signup.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}


mysqli_close($conn);
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background: #f6f6f6;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .form-toggle {
            cursor: pointer;
            color: #3f3d56;
            text-decoration: underline;
        }

        .social-icons i {
            font-size: 1.5rem;
            margin: 0 10px;
            color: #3f3d56;
            cursor: pointer;
        }

        .btn-dark-gray {
            background-color: #3f3d56;
            color: white;
        }
        .btn-dark-gray:hover {
            background-color: #2f2d46;
            color: white;
        }
    </style>
</head>
<body>



<div class="auth-card">
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="index.php" style="text-decoration: none;">
            <img src="img/logo.png" alt="Hardel Logo" style="height: 100px;">
        </a>


    <h4 class="text-center mb-3" id="formTitle">Sign In</h4>

    <?php if (isset($_SESSION['passError'])) : ?>
        <div class="alert alert-danger" id="error-message"><?php echo $_SESSION['passError']; ?></div>
        <?php unset($_SESSION['passError']); ?>
    <?php elseif (isset($_SESSION['userError'])) : ?>
        <div class="alert alert-danger" id="error-message"><?php echo $_SESSION['userError']; ?></div>
        <?php unset($_SESSION['userError']); ?>
    <?php elseif (isset($_SESSION['signupError'])) : ?>
        <div class="alert alert-danger" id="error-message"><?php echo $_SESSION['signupError']; ?></div>
        <?php unset($_SESSION['signupError']); ?>
    <?php endif; ?>

    <!-- Sign In Form -->
    <form method="POST" action="" id="signinForm">
        <div class="mb-3">
            <input type="text" name="email" class="form-control" placeholder="Email or Username" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-dark-gray w-100" name="signin">Sign In</button>
    </form>

    <!-- Sign Up Form -->
    <form method="POST" action="" id="signupForm" style="display: none;">
    <div class="mb-3">
        <input type="text" name="email" class="form-control" placeholder="Email Address" required>
    </div>
    <button type="submit" class="btn btn-dark-gray w-100" name="signup">Sign Up</button>
</form>


    <div class="text-center mt-3">
        <span id="toggleText">Don't have an account? <span class="form-toggle" onclick="toggleForm()">Sign Up</span></span>
    </div>

    <hr class="my-4">

    <div class="text-center mb-2">Or connect with</div>
    <div class="text-center social-icons">
        <i class="fa-brands fa-facebook-f"></i>
        <i>|</i>
        <i class="fa-brands fa-google"></i>
    </div>
</div>

<script>
    const signupForm = document.getElementById("signupForm");
    const signinForm = document.getElementById("signinForm");
    const formTitle = document.getElementById("formTitle");
    const toggleText = document.getElementById("toggleText");

    function toggleForm() {
        if (signupForm.style.display === "none") {
            signupForm.style.display = "block";
            signinForm.style.display = "none";
            formTitle.textContent = "Create Account";
            toggleText.innerHTML = 'Already have an account? <span class="form-toggle" onclick="toggleForm()">Sign In</span>'; 
        } else {
            signupForm.style.display = "none";
            signinForm.style.display = "block";
            formTitle.textContent = "Sign In";
            toggleText.innerHTML = 'Don\'t have an account? <span class="form-toggle" onclick="toggleForm()">Sign Up</span>';
        }
    }

    window.onload = function () {
        const errorMsg = document.getElementById("error-message");
        if (errorMsg) {
            setTimeout(() => {
                errorMsg.style.display = "none";
            }, 3000);
        }
    };
    function redirect() {
        window.location.href = "verify.php";
    }
    window.onload = function () {
    const errorMsg = document.getElementById("error-message");
    if (errorMsg) {
        setTimeout(() => {
            errorMsg.style.display = "none";
        }, 3000);
    }

    // Check for signup query in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('signup') === 'true') {
        toggleForm(); // show signup form
    }
};

</script>

</body>
</html>
