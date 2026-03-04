<?php
session_start();
require_once "includes/auth.php";
require_once "includes/db.php";

requireLogin();

$pdo = connect();

// Get current admin info
$stmt = $pdo->prepare("SELECT * FROM administrators WHERE admin_id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$message = "";
$messageType = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $result = updateAdminProfile($_SESSION['admin_id'], $username, $password);

    $message = $result['message'];
    $messageType = $result['success'] ? "success" : "danger";

    // Refresh admin data after update
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php include "templates/header.php"; ?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
    			<h5 class="mb-0">
        			<i class="fa-solid fa-id-badge me-2"></i> My Profile
    			</h5>
    			<a href="index.php" class="btn btn-light btn-sm">
        			<i class="fa-solid fa-arrow-left me-1"></i> Back
    			</a>
			</div>
        </div>

        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" onsubmit="return confirmUpdate();">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control"
                               value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Change your password here...">
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($admin['first_name']) ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($admin['middle_name']) ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($admin['last_name']) ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Suffix</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($admin['suffix']) ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control"
                               value="<?= htmlspecialchars($admin['email']) ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Contact Number</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($admin['contact_number']) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Role</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($admin['role']) ?>" readonly>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-save me-2"></i> Update
                </button>

            </form>
        </div>
    </div>
</div>

<script>
function confirmUpdate() {
    return confirm("Are you sure you want to update your profile?");
}
</script>

<?php include "templates/footer.php"; ?>
