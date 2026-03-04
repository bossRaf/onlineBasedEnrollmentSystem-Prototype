<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "includes/auth.php";
require_once "includes/db.php";

// 🔒 Protect page
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$pdo = connect();
$message = "";


//  APPROVE APPLICATION

if (isset($_GET['action']) && $_GET['action'] === 'approve') {

    $applicationId = (int) $_GET['id'];

    try {
        $pdo->beginTransaction();

        // 1️⃣ Lock application
        $stmt = $pdo->prepare("
            SELECT *
            FROM enrollment_applications
            WHERE application_id = :id
            FOR UPDATE
        ");
        $stmt->execute(['id' => $applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            throw new Exception("Application not found.");
        }

        if ($application['status'] === 'approved') {
            throw new Exception("Application already approved.");
        }

        if (!empty($application['approved_student_id'])) {
            throw new Exception("Student record already exists for this application.");
        }

        // 2️⃣ Extract enrollment year
        $yearParts  = explode('-', $application['school_year']);
        $enrollYear = (int) $yearParts[0];

        // 3️⃣ Year sequence handling
        $stmt = $pdo->prepare("
            SELECT last_number_used
            FROM year_sequences
            WHERE seq_year = :year
            FOR UPDATE
        ");
        $stmt->execute(['year' => $enrollYear]);
        $sequence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sequence) {
            $nextNumber = $sequence['last_number_used'] + 1;

            $updateSeq = $pdo->prepare("
                UPDATE year_sequences
                SET last_number_used = :num
                WHERE seq_year = :year
            ");
            $updateSeq->execute([
                'num'  => $nextNumber,
                'year' => $enrollYear
            ]);
        } else {
            $nextNumber = 1;

            $insertSeq = $pdo->prepare("
                INSERT INTO year_sequences (seq_year, last_number_used)
                VALUES (:year, 1)
            ");
            $insertSeq->execute(['year' => $enrollYear]);
        }

        
        //   CHECK IF STUDENT EXISTS (Re-enrollment logic)
       
        $checkStudent = $pdo->prepare("
            SELECT internal_id, student_id
            FROM students
            WHERE first_name = :first_name
              AND last_name = :last_name
              AND birthdate = :birthdate
            LIMIT 1
        ");

        $checkStudent->execute([
            'first_name' => $application['first_name'],
            'last_name'  => $application['last_name'],
            'birthdate'  => $application['birthdate']
        ]);

        $existingStudent = $checkStudent->fetch(PDO::FETCH_ASSOC);

        if ($existingStudent) {

            // Re-enrolling student
            $studentInternalId = $existingStudent['internal_id'];
            $studentId         = $existingStudent['student_id'];
            
             // ✅ UPDATE student grade info when re-enrolling
    		$updateStudent = $pdo->prepare("
        		UPDATE students
        		SET grade_level = :grade_level,
            	grade_number = :grade_number,
            	strand_id = :strand_id
        		WHERE internal_id = :id
    		");

    		$updateStudent->execute([
        		'grade_level'  => $application['grade_level'],
        		'grade_number' => $application['grade_number'],
        		'strand_id'    => $application['strand_id'],
        		'id'           => $studentInternalId
    		]);

        } else {

            // Generate Student ID
            $studentId = "DNHS-" .
                $enrollYear . "-" .
                str_pad($nextNumber, 4, "0", STR_PAD_LEFT);

            $insertStudent = $pdo->prepare("
                INSERT INTO students (
    				student_id,
    				application_id,
    				first_name,
    				middle_name,
    				last_name,
    				suffix,
    				birthdate,
    				gender,
    				contact_number,
    				email,
    				address,
    				grade_level,
    				grade_number,
    				strand_id,
    				first_enrolled_year,
    				created_by
				)
                 VALUES (
                    :student_id,
                    :application_id,
                    :first_name,
                    :middle_name,
                    :last_name,
                    :suffix,
                    :birthdate,
                    :gender,
                    :contact_number,
                    :email,
                    :address,
                    :grade_level,
                    :grade_number,
                    :strand_id,
                    :first_enrolled_year,
                    :created_by
                )
            ");

            $insertStudent->execute([
                'student_id'         => $studentId,
                'application_id'     => $application['application_id'],
                'first_name'         => $application['first_name'],
                'middle_name'        => $application['middle_name'],
                'last_name'          => $application['last_name'],
                'suffix'             => $application['suffix'],
                'birthdate'          => $application['birthdate'],
                'gender'             => $application['gender'],
                'contact_number'     => $application['contact_number'],
                'email'              => $application['email'],
                'address'            => $application['address'],
                'grade_level'        => $application['grade_level'],
                'grade_number'		 => $application['grade_number'],
                'strand_id'          => $application['strand_id'],
                'first_enrolled_year'=> $enrollYear,
                'created_by'         => $_SESSION['admin_id']
            ]);

            $studentInternalId = $pdo->lastInsertId();
        }

        
        //   INSERT INTO ENROLLMENTS
        $insertEnrollment = $pdo->prepare("
            INSERT INTO enrollments (
                student_internal_id,
                application_id,
                school_year,
                grade_level,
                grade_number,
                strand_id
            ) VALUES (
                :student_internal_id,
                :application_id,
                :school_year,
                :grade_level,
                :grade_number,
                :strand_id
            )
        ");

        $insertEnrollment->execute([
            'student_internal_id' => $studentInternalId,
            'application_id'      => $application['application_id'],
            'school_year'         => $application['school_year'],
            'grade_level'         => $application['grade_level'],
            'grade_number'        => $application['grade_number'],
            'strand_id'           => $application['strand_id']
        ]);

        
        //   UPDATE APPLICATION
        $updateApp = $pdo->prepare("
            UPDATE enrollment_applications
            SET status = 'approved',
                reviewed_at = NOW(),
                reviewed_by = :admin_id,
                approved_student_id = :student_id
            WHERE application_id = :app_id
        ");

        $updateApp->execute([
            'admin_id'   => $_SESSION['admin_id'],
            'student_id' => $studentInternalId, // ✅ FIXED
            'app_id'     => $applicationId
        ]);

        $pdo->commit();

        $message = "
            <div class='alert alert-success'>
                Application approved successfully.<br>
                Student ID: <strong>$studentId</strong>
            </div>
        ";

    } catch (Exception $e) {

        $pdo->rollBack();

        $message = "
            <div class='alert alert-danger'>
                Error: {$e->getMessage()}
            </div>
        ";
    }
}

$stmt = $pdo->query("
    SELECT ea.*,
           s.strand_code
    FROM enrollment_applications ea
    LEFT JOIN strands s 
        ON ea.strand_id = s.strand_id
    WHERE ea.status = 'pending'
    ORDER BY ea.application_id DESC
");



// REJECT APPLICATION
if (isset($_GET['action']) && $_GET['action'] === 'reject') {

    $applicationId = (int) $_GET['id'];

    try {

        $pdo->beginTransaction();

        // Lock application
        $stmt = $pdo->prepare("
            SELECT first_name, last_name, status
            FROM enrollment_applications
            WHERE application_id = :id
            FOR UPDATE
        ");
        $stmt->execute(['id' => $applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            throw new Exception("Application not found.");
        }

        if ($application['status'] === 'rejected') {
            throw new Exception("Application already rejected.");
        }

        // Update application status
        $update = $pdo->prepare("
            UPDATE enrollment_applications
            SET status = 'rejected',
                reviewed_at = NOW(),
                reviewed_by = :admin_id
            WHERE application_id = :id
        ");

        $update->execute([
            'admin_id' => $_SESSION['admin_id'],
            'id'       => $applicationId
        ]);

        // Insert notification for current admin
        $messageText = "Application of {$application['first_name']} {$application['last_name']} was rejected.";

        $notify = $pdo->prepare("
            INSERT INTO notifications (
                recipient_admin_id,
                application_id,
                message
            ) VALUES (
                :recipient_admin_id,
                :application_id,
                :message
            )
        ");

        $notify->execute([
            'recipient_admin_id' => $_SESSION['admin_id'],
            'application_id'     => $applicationId,
            'message'            => $messageText
        ]);

        $pdo->commit();

        header("Location: notification.php");
        exit();

    } catch (Exception $e) {

        $pdo->rollBack();

        $message = "
            <div class='alert alert-danger'>
                Error: {$e->getMessage()}
            </div>
        ";
    }
}

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "templates/header.php";
?>

<div class="container my-5">
    <div class="card shadow border-0">

        <div class="card-header text-white py-3" style="background-color:#4CAF50;">
            <h4 class="mb-0">
                <i class="fa-solid fa-file-lines me-2"></i>
                Pending Enrollment Applications
            </h4>
            
            <a href="index.php" class="btn btn-light btn-sm float-end">
        		Back
    		</a>
        </div>

        <div class="card-body">

            <?= $message ?>

            <?php if (count($applications) > 0): ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Application Document</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Level</th>
                                <th>Grade</th>
                                <th>Strand</th>
                                <th width="160">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
    									<a href="viewApplication.php?id=<?= $app['application_id'] ?>"
       										class="btn btn-primary btn-sm">
        									View
    									</a>
									</td>
                                    <td><?= $app['first_name'] . " " . $app['last_name'] ?></td>
                                    <td><?= $app['gender'] ?></td>
                                    <td><?= $app['grade_level'] ?></td>
                                    <td><?= $app['grade_number'] ?></td>
                                    <td>
                                        <?= ($app['grade_level'] === 'Senior High' && !empty($app['strand_code']))
                                            ? $app['strand_code']
                                            : '-' ?>
                                    </td>
                                    <td>
                                        <a href="applications.php?action=approve&id=<?= $app['application_id'] ?>"
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Approve this application?')">
                                            Approve
                                        </a>

                                        <a href="applications.php?action=reject&id=<?= $app['application_id'] ?>"
                                           class="btn btn-danger btn-sm">
                                            Reject
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>

                <div class="alert alert-info">
                    No pending applications found.
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include "templates/footer.php"; ?>

