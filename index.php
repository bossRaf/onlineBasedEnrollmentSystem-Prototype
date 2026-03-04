<?php
session_start();
require_once "includes/auth.php";
require_once "includes/helpers.php";
include "templates/header.php";

require_once "includes/db.php";

$unreadCount = 0;

if (isset($_SESSION['admin_id'])) {
    $pdo = connect();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE recipient_admin_id = ? 
        AND is_read = 0
    ");
    $stmt->execute([$_SESSION['admin_id']]);
    $unreadCount = $stmt->fetchColumn();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logoutUser();
}
?>

<?php if (!isset($_SESSION['admin_id'])): ?>
<!-- Visitor Landing Page -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h1>Welcome to Dimasalang National High School Online Enrollment!</h1>
        <p class="lead">Apply online to enroll in our Elementary, Junior High, or Senior High programs.</p>
        <a href="applyEnrollment.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-file-lines me-2"></i> Apply for Enrollment
        </a>
    </div>

    <div class="row text-center">
        <div class="col-md-4 mb-3">
           <div class="card bg-primary-subtle text-dark h-100 border-0 shadow-sm hover-card">
                <div class="card-body">
                    <i class="fa-solid fa-school fa-2x mb-2"></i>
                    <h5 class="card-title fw-bold">About Our School</h5>
                    <p class="card-text">Providing quality education from Elementary to Senior High.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success-subtle text-dark h-100 border-0 shadow-sm hover-card">
                <div class="card-body">
                    <i class="fa-solid fa-user-graduate fa-2x mb-2"></i>
                    <h5 class="card-title fw-bold">Grade Levels</h5>
                    <p class="card-text">Elementary, Junior High, and Senior High School programs available.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-warning-subtle text-dark h-100 border-0 shadow-sm hover-card">
                <div class="card-body">
                    <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                    <h5 class="card-title fw-bold">Simple Enrollment</h5>
                    <p class="card-text">Submit your application online and wait for approval from the school admin or registrar.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Admin or Registrar Dashboard View -->
<div class="d-flex">
    <!-- Sidebar -->
<div class="col-md-3 col-lg-2 bg-light vh-100 p-3 border-end">
    <h5 class="fw-bold mb-4">
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <i class="fa-solid fa-user-shield me-2"></i> Admin Staff
        <?php else: ?>
            <i class="fa-solid fa-user-tie me-2"></i> Registrar Staff
        <?php endif; ?>
    </h5>
    <ul class="nav flex-column">
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active bg-success text-white' : 'text-dark'; ?>" href="index.php">
    				<i class="fa-solid fa-tachometer-alt me-2"></i> Dashboard
				</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-dark" href="profile.php">
                    <i class="fa-solid fa-id-badge me-2"></i> Profile
                </a>
            </li>
        
            <li class="nav-item mb-2">
    			<a class="nav-link text-dark" href="applications.php">
        			<i class="fa-solid fa-file-lines me-2"></i> Applications
    			</a>
			</li>
        
        	<li class="nav-item mb-2">
    			<a class="nav-link text-dark" href="adminAllStudents.php">
        			<i class="fa-solid fa-school me-2"></i> All Students
    			</a>
			</li>
        
            <li class="nav-item mb-2">
                <a class="nav-link text-dark d-flex justify-content-between align-items-center" href="notification.php">
    				<span>
        				<i class="fa-solid fa-bell me-2"></i> Notifications
    				</span>

    				<?php if ($unreadCount > 0): ?>
        			<span class="badge bg-danger rounded-pill">
            		<?= $unreadCount; ?>
        			</span>
    				<?php endif; ?>
				</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-dark" href="manageUsers.php">
                    <i class="fa-solid fa-users-cog me-2"></i> Manage Users
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active bg-success text-white' : 'text-dark'; ?>" href="index.php">
    				<i class="fa-solid fa-tachometer-alt me-2"></i> Dashboard
				</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-dark" href="profile.php">
                    <i class="fa-solid fa-id-badge me-2"></i> Profile
                </a>
            </li>
        
        	<li class="nav-item mb-2">
    			<a class="nav-link text-dark" href="applications.php">
       				 <i class="fa-solid fa-file-lines me-2"></i> Applications
    			</a>
			</li>
       
        	<li class="nav-item mb-2">
    			<a class="nav-link text-dark" href="adminAllStudents.php">
        			<i class="fa-solid fa-school me-2"></i> All Students
    			</a>
			</li>
        
            <li class="nav-item mb-2">
                <a class="nav-link text-dark d-flex justify-content-between align-items-center" href="notification.php">
    				<span>
        				<i class="fa-solid fa-bell me-2"></i> Notifications
    				</span>

    				<?php if ($unreadCount > 0): ?>
        			<span class="badge bg-danger rounded-pill">
            		<?= $unreadCount; ?>
        			</span>
    				<?php endif; ?>
				</a>
            </li>
        <?php endif; ?>

        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="index.php?action=logout">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>

    <!-- Main Content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
        <?php include "administratorDashboard.php"; ?>
    </main>
</div>
<?php endif; ?>

<?php include "templates/footer.php"; ?>

