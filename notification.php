<?php
session_start();
require_once "includes/db.php";
require_once "includes/auth.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$pdo = connect();
$currentAdminId = $_SESSION['admin_id'];


//   HANDLE MARK AS READ

if (isset($_GET['mark_read'])) {
    $notifId = (int) $_GET['mark_read'];

    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE notification_id = ? 
        AND recipient_admin_id = ?
    ");
    $stmt->execute([$notifId, $currentAdminId]);

    header("Location: notification.php");
    exit();
}


//   HANDLE APPROVE (USE applications.php LOGIC)

if (isset($_GET['approve'])) {
    $applicationId = (int) $_GET['approve'];

    header("Location: applications.php?action=approve&id=" . $applicationId);
    exit();
}

//   FETCH ALL NOTIFICATIONS
$stmt = $pdo->prepare("
    SELECT n.*, 
           ea.first_name, ea.last_name
    FROM notifications n
    LEFT JOIN enrollment_applications ea 
        ON n.application_id = ea.application_id
    WHERE n.recipient_admin_id = ?
    AND (ea.status != 'approved' OR ea.status IS NULL)
    ORDER BY n.created_at DESC
");


$stmt->execute([$currentAdminId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "templates/header.php";
?>

<div class="container my-5">
    <div class="card shadow border-0">
        <div class="card-header text-white" style="background-color: #4CAF50;">
            <h5 class="mb-0">Notifications</h5>

            <a href="index.php" class="btn btn-light btn-sm float-end">
                Back
            </a>
        </div>

        <div class="card-body">

            <?php if (count($notifications) > 0): ?>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Applicant</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th width="250">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($notifications as $notif): ?>

                            <tr class="<?= $notif['is_read'] ? '' : 'table-warning'; ?>">
                                <td>
                                    <?= htmlspecialchars($notif['first_name'] . ' ' . $notif['last_name']); ?>
                                </td>
                                <td><?= $notif['created_at']; ?></td>
                                <td>
                                    <?= $notif['is_read']
                                        ? '<span class="badge bg-secondary">Read</span>'
                                        : '<span class="badge bg-danger">Unread</span>'; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">

                                        <?php if (!$notif['is_read']): ?>
                                            <a href="notification.php?mark_read=<?= $notif['notification_id']; ?>"
                                               class="btn btn-sm btn-outline-dark">
                                                Mark as Read
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($notif['application_id']): ?>
                                            <a href="notification.php?approve=<?= $notif['application_id']; ?>"
                                               class="btn btn-sm btn-success">
                                                Approve
                                            </a>

                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="alert alert-info">
                    No notifications available.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include "templates/footer.php"; ?>
