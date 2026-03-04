<?php
session_start();
require_once "includes/auth.php";
require_once "includes/db.php";

// 🔒 Protect page
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: applications.php");
    exit();
}

$applicationId = (int) $_GET['id'];
$pdo = connect();

// Get application info
$stmt = $pdo->prepare("
    SELECT first_name, last_name
    FROM enrollment_applications
    WHERE application_id = :id
");
$stmt->execute(['id' => $applicationId]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    die("Application not found.");
}

// Get uploaded documents
$docStmt = $pdo->prepare("
    SELECT document_type, file_path
    FROM application_documents
    WHERE application_id = :id
");
$docStmt->execute(['id' => $applicationId]);
$documents = $docStmt->fetchAll(PDO::FETCH_ASSOC);

include "templates/header.php";
?>

<div class="container my-5">
    <div class="card shadow border-0">

        <div class="card-header navbar-dark bg-success text-white py-3">
            <h5 class="mb-0">
                Documents of <?= htmlspecialchars($application['first_name'] . " " . $application['last_name']) ?>
            </h5>

            <a href="applications.php" class="btn btn-light btn-sm float-end">
                Exit
            </a>
        </div>

        <div class="card-body">

            <?php if (count($documents) > 0): ?>

                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                        <div class="col-md-4 mb-4 text-center">
                            <p><strong><?= htmlspecialchars($doc['document_type']) ?></strong></p>
                            <img src="<?= htmlspecialchars($doc['file_path']) ?>"
                                 class="img-fluid img-thumbnail"
                                 style="max-height:300px;">
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>

                <div class="alert alert-info">
                    No documents uploaded for this application.
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include "templates/footer.php"; ?>

