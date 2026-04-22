<?php
session_start();
require_once 'config.php';
include 'includes/header.php';

$email = $_SESSION['email'] ?? '';
$masked_email = '';
if (!empty($email)) {
    $parts = explode('@', $email);
    $local = $parts[0];
    $domain = $parts[1] ?? '';
    $masked_local = substr($local, 0, 3) . str_repeat('*', max(strlen($local) - 3, 0));
    $masked_email = $masked_local . '@' . $domain;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $digit1 = $_POST['digit1'] ?? '';
    $digit2 = $_POST['digit2'] ?? '';
    $digit3 = $_POST['digit3'] ?? '';
    $digit4 = $_POST['digit4'] ?? '';
    $code = $digit1 . $digit2 . $digit3 . $digit4;

    if (!empty($email) && strlen($code) === 4) {
        $stmt = $conn->prepare("SELECT * FROM verify WHERE email = ? AND code = ? AND created_at > NOW() - INTERVAL 15 MINUTE");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $_SESSION['email'] = $email;
    
    // Delete the used verification code (security best practice)
            $delete_stmt = $conn->prepare("DELETE FROM verify WHERE email = ? AND code = ?");
            $delete_stmt->bind_param("ss", $email, $code);
            $delete_stmt->execute();
            $delete_stmt->close();
            header("Location: signup.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid or expired code.";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['error'] = "Please enter a complete 4-digit code.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-3">
                    <div class="text-center mb-5">
                        <!-- Verification icon -->
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"  class="bi bi-shield-check text-primary" viewBox="0 0 16 16">
                                <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                                <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                            </svg>
                        </div>
                        
                        <h2 class="mb-3 fw-bold">Verify Your Identity</h2>
                        <p class="text-muted">We sent a verification code to<br>
                            <span class="text-dark fw-semibold"><?php echo htmlspecialchars($masked_email); ?></span>
                        </p>
                    </div>

                    <!-- Error message positioned here -->
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <?php echo $_SESSION['error']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="d-flex justify-content-center gap-3 mb-5">
                            <?php foreach(['digit1', 'digit2', 'digit3', 'digit4'] as $field): ?>
                                <input type="text" name="<?php echo $field; ?>" maxlength="1" 
                                       pattern="[0-9]*" inputmode="numeric"
                                       class="form-control form-control-lg text-center code-input fs-3"
                                       style="height: 70px; width: 70px; border-radius: 12px;"
                                       required>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-dark-gray w-100 py-2 fs-5 fw-semibold">
                            CONTINUE
                            <span class="ms-2">&rarr;</span>
                        </button>

                        <div class="text-center">
                            <p class="text-muted">Didn't receive the code? 
                                <a href="#" class="text-decoration-none fw-semibold text-primary">
                                    Resend Code
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .code-input {
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    
    .code-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
        outline: none;
    }
    
    .card {
        border-radius: 20px;
    }
    
    .alert {
        border-radius: 12px;
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

<script>
    // Enhanced input handling
    document.querySelectorAll('.code-input').forEach((input, index, inputs) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1) {
                if (index < inputs.length - 1) inputs[index + 1].focus()
                else inputs[index].blur()
            }
            
            // Auto-validate numbers
            if (!/^\d*$/.test(e.target.value)) {
                e.target.value = e.target.value.replace(/\D/g, '')
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                inputs[index - 1].focus()
            }
        });
    });
</script>