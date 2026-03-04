<?php
session_start();
require_once "includes/auth.php";

requireLogin();

if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: allAdministrators.php");
    exit();
}

$id = (int) $_GET['id'];
$user = getAdministratorById($id);

if (!$user) {
    $_SESSION['message'] = ['success' => false, 'message' => 'Administrator not found.'];
    header("Location: allAdministrators.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = updateAdministrator($id, $_POST);

    $_SESSION['message'] = $result;

    header("Location: allAdministrators.php");
    exit();
}

include "templates/header.php";
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Edit Administrator</h5>
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row mb-3">
                    <div class="col fw-bold">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control"
                               value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>

                    <div class="col fw-bold">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" class="form-control"
                               value="<?= htmlspecialchars($user['middle_name']) ?>">
                    </div>

                    <div class="col fw-bold">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                               value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col fw-bold">
                        <label>Suffix</label>
                        <input type="text" name="suffix" class="form-control"
                               value="<?= htmlspecialchars($user['suffix']) ?>">
                    </div>

                    <div class="col fw-bold">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="col fw-bold">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control"
                               value="<?= htmlspecialchars($user['contact_number']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col fw-bold">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="Admin" <?= $user['role']=='Admin'?'selected':'' ?>>Admin</option>
                            <option value="Registrar" <?= $user['role']=='Registrar'?'selected':'' ?>>Registrar</option>
                        </select>
                    </div>

                    <div class="col fw-bold">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Save Changes</button>
                <a href="allAdministrators.php" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>
</div>

<?php include "templates/footer.php"; ?>



