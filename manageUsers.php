<?php
session_start();
require_once "includes/auth.php";
require_once "includes/db.php";

requireLogin();

// Extra protection
if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$message = "";
$messageType = "";

if (isset($_POST['create_user'])) {

    $data = [
        'username' => trim($_POST['username']),
        'password' => trim($_POST['password']),
        'first_name' => trim($_POST['first_name']),
        'middle_name' => trim($_POST['middle_name']),
        'last_name' => trim($_POST['last_name']),
        'suffix' => trim($_POST['suffix']),
        'email' => trim($_POST['email']),
        'contact_number' => trim($_POST['contact_number']),
        'role' => $_POST['role']
    ];

    if (empty($data['username']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
        $message = "Please fill in all required fields.";
        $messageType = "danger";
    } else {
        $result = createAdministrator($data);
        $message = $result['message'];
        $messageType = $result['success'] ? "success" : "danger";
    }
}

include "templates/header.php";
?>

<div class="container mt-5">

    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Create New Account</h4>
        <div>
            <a href="allAdministrators.php" class="btn btn-success">
                <i class="fa-solid fa-users me-1"></i> All Administrators
            </a>
            <a href="index.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Admin">Admin</option>
                            <option value="Registrar">Registrar</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Suffix</label>
                        <input type="text" name="suffix" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control">
                    </div>
                </div>

                <button type="submit" name="create_user" class="btn btn-success">
                    <i class="fa-solid fa-user-plus me-1"></i> Create Account
                </button>

            </form>

        </div>
    </div>

</div>

<?php include "templates/footer.php"; ?>

