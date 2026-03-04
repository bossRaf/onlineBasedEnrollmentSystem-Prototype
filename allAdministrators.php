<?php
session_start();
require_once "includes/auth.php";
require_once "includes/db.php";

requireLogin();

if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Handle delete user account
if (isset($_POST['delete_id'])) {

    $id = (int) $_POST['delete_id'];

    $result = deleteAdministrator($id);

    $_SESSION['message'] = $result;

    header("Location: allAdministrators.php");
    exit();
}


if (isset($_GET['search']) && !empty($_GET['keyword'])) {
    $users = searchAdministrators($_GET['keyword']);
} else {
    $users = getAllAdministrators();
}

include "templates/header.php";
?>

<div class="container mt-5">

    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">All Administrators</h4>
        <div>
            <a href="manageUsers.php" class="btn btn-success btn-sm me-2">
                <i class="fa-solid fa-user-plus me-1"></i> Create New Account
            </a>
            <a href="index.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="keyword" class="form-control"
                   placeholder="Search by Admin ID or Last Name">
            <button type="submit" name="search" class="btn btn-outline-secondary">
                <i class="fa-solid fa-search"></i>
            </button>
        </div>
    </form>
	
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message']['success'] ? 'success' : 'danger' ?>">
        <?= $_SESSION['message']['message'] ?>
    </div>
    <?php unset($_SESSION['message']); ?>
	<?php endif; ?>
    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Admin/Registrar Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['admin_id'] ?></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['role'] ?></td>
                            <td><?= $user['status'] ?></td>
                            <td><?= $user['created_at'] ?></td>
                            <td>
    							<a href="editAdministrator.php?id=<?= $user['admin_id'] ?>"
       								class="btn btn-primary btn-sm">
        							Edit
   	 								</a>

    							<button type="button"
        							class="btn btn-danger btn-sm"
        							data-bs-toggle="modal"
        							data-bs-target="#deleteModal<?= $user['admin_id'] ?>">
   	 								Delete
								</button>
							</td>
                        </tr>
                    	<div class="modal fade" id="deleteModal<?= $user['admin_id'] ?>" tabindex="-1">
  							<div class="modal-dialog">
    							<div class="modal-content">
      								<div class="modal-header">
        								<h5 class="modal-title">Confirm Delete</h5>
        								<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      								</div>
      								<div class="modal-body">
        							Are you sure you want to delete this administrator?
      							</div>
      							<div class="modal-footer">
        							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

        							<form method="POST" action="allAdministrators.php">
          								<input type="hidden" name="delete_id" value="<?= $user['admin_id'] ?>">
          								<button type="submit" class="btn btn-danger">Delete</button>
        							</form>

      							</div>
    						</div>
  						</div>
					</div>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include "templates/footer.php"; ?>

