<?php
ob_start();
session_start();
include 'includes/header.php';
include 'config.php';

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

function escape($val) {
    return htmlspecialchars($val);
}

// --- HANDLE ADD NEW DRIVER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    $first = $_POST['first_name'];
    $middle = $_POST['middle_name'];
    $last = $_POST['last_name'];
    $phone = $_POST['phone'];
    $user_name = $_POST['user_name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'] ?? ''; // Get email if provided

    $img_name = '';
    if (!empty($_FILES['driver_image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir);
        }
        $img_name = $target_dir . time() . "_" . basename($_FILES["driver_image"]["name"]);
        if (!move_uploaded_file($_FILES["driver_image"]["tmp_name"], $img_name)) {
            $_SESSION['error'] = "Failed to upload image";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Insert into users table
    $stmt_user = $conn->prepare("INSERT INTO user (user_name, email, password, user_type) VALUES (?, ?, ?, ?)");
    if (!$stmt_user) {
        die("Prepare failed: " . $conn->error);
    }
    $user_type = "driver";
    $stmt_user->bind_param("ssss", $user_name, $email, $password, $user_type);
    if (!$stmt_user->execute()) {
        die("Execute failed: " . $stmt_user->error);
    }

    // Insert into driver table
    $stmt_driver = $conn->prepare("INSERT INTO driver (first_name, middle_name, last_name, phone, driver_image) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt_driver) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_driver->bind_param("sssss", $first, $middle, $last, $phone, $img_name);
    if (!$stmt_driver->execute()) {
        die("Execute failed: " . $stmt_driver->error);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// --- HANDLE EDIT DRIVER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_driver'])) {
    $id = $_POST['driver_id'];
    $first = $_POST['first_name'];
    $middle = $_POST['middle_name'];
    $last = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'] ?? ''; // Get email if provided

    $img_name = $_POST['existing_image'];
    if (!empty($_FILES['driver_image']['name'])) {
        $target_dir = "uploads/";
        $img_name = $target_dir . time() . "_" . basename($_FILES["driver_image"]["name"]);
        move_uploaded_file($_FILES["driver_image"]["tmp_name"], $img_name);
    }

    $stmt = $conn->prepare("UPDATE driver SET first_name=?, middle_name=?, last_name=?, phone=?, driver_image=? WHERE driver_id=?");
    $stmt->bind_param("sssssi", $first, $middle, $last, $phone, $img_name, $id);
    $stmt->execute();
    
    // Also update the email in the users table if needed
    if (!empty($email)) {
        $stmt_user = $conn->prepare("UPDATE users SET email=? WHERE user_name=?");
        $stmt_user->bind_param("ss", $email, $user_name); // Note: You might need to get the user_name from somewhere
        $stmt_user->execute();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- HANDLE DELETE DRIVER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_driver'])) {
    $id = $_POST['driver_id'];
    $stmt = $conn->prepare("DELETE FROM driver WHERE driver_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- FETCH DRIVER LIST ---
$drivers = $conn->query("SELECT * FROM driver ORDER BY driver_id DESC");
ob_end_flush();
?>

    <style>
        .driver-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        
    </style>
</head>
<body>
<?php include 'navs/navadmin.php';?>
<div class="container py-5 mt-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Driver Management</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDriverModal">+ Add Driver</button>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th style="width: 180px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $modals = '';
            while ($d = $drivers->fetch_assoc()):
            ?>
            <tr>
                <td><img src="<?php echo escape($d['driver_image']); ?>" class="driver-img"></td>
                <td><?php echo escape($d['first_name'] . ' ' . $d['middle_name'] . ' ' . $d['last_name']); ?></td>
                <td><?php echo escape($d['phone']); ?></td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $d['driver_id']; ?>">Edit</button>
                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this driver?');">
                        <input type="hidden" name="driver_id" value="<?php echo $d['driver_id']; ?>">
                        <button type="submit" name="delete_driver" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php
           $modals .= '
           <div class="modal fade" id="editModal' . $d['driver_id'] . '" tabindex="-1" aria-hidden="true">
               <div class="modal-dialog modal-lg">
                   <form method="post" enctype="multipart/form-data" class="modal-content">
                       <input type="hidden" name="driver_id" value="' . $d['driver_id'] . '">
                       <input type="hidden" name="existing_image" value="' . escape($d['driver_image']) . '">
                       <div class="modal-header">
                           <h5 class="modal-title">Edit Driver</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                       </div>
                       <div class="modal-body">
                           <div class="row mb-2">
                               <div class="col">
                                   <input type="text" name="first_name" class="form-control" value="' . escape($d['first_name']) . '" required>
                               </div>
                               <div class="col">
                                   <input type="text" name="middle_name" class="form-control" value="' . escape($d['middle_name']) . '">
                               </div>
                               <div class="col">
                                   <input type="text" name="last_name" class="form-control" value="' . escape($d['last_name']) . '" required>
                               </div>
                           </div>
                           <div class="row mb-2">
                               <div class="col">
                                   <input type="text" name="phone" class="form-control" value="' . escape($d['phone']) . '" required>
                               </div>
                               <div class="col">
                                   <input type="email" name="email" class="form-control" placeholder="Email (optional)">
                               </div>
                               <div class="col">
                                   <input type="file" name="driver_image" class="form-control">
                               </div>
                           </div>
                       </div>
                       <div class="modal-footer">
                           <button class="btn btn-success" name="edit_driver">Save Changes</button>
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                       </div>
                   </form>
               </div>
           </div>';
            endwhile;
            ?>
        </tbody>
    </table>

    <!-- Output Edit Modals -->
    <?php echo $modals; ?>
</div>

<!-- ADD DRIVER MODAL -->
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDriverLabel">Add New Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="col">
                        <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
                    </div>
                    <div class="col">
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                    </div>
                    <div class="col">
                        <input type="text" name="user_name" class="form-control" placeholder="Username (for account)" required>
                    </div>
                    <div class="col">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <input type="email" name="email" class="form-control" placeholder="Email (optional)">
                    </div>
                    <div class="col">
                        <label>Driver Image (optional)</label>
                        <input type="file" name="driver_image" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" name="add_driver">Add Driver</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
