<?php
require_once "includes/db.php";
$pdo = connect();


//  FETCH COUNTS

// Enrollment Applications
$applications = $pdo->query("SELECT COUNT(*) FROM enrollment_applications")
                     ->fetchColumn();

// Total Approved Students (students table)
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")
                     ->fetchColumn();

// Elementary
$elementary = $pdo->query("
    SELECT COUNT(*) FROM students 
    WHERE grade_level = 'Elementary'
")->fetchColumn();

// Junior High
$junior = $pdo->query("
    SELECT COUNT(*) FROM students 
    WHERE grade_level = 'Junior High'
")->fetchColumn();

// Senior High
$shs = $pdo->query("
    SELECT COUNT(*) FROM students 
    WHERE grade_level = 'Senior High'
")->fetchColumn();

// Administrators
$admins = $pdo->query("SELECT COUNT(*) FROM administrators")
              ->fetchColumn();

// Documents
$documents = $pdo->query("SELECT COUNT(*) FROM application_documents")
                 ->fetchColumn();

// Strands
$strands = $pdo->query("SELECT COUNT(*) FROM strands")
               ->fetchColumn();

// Rejected Applications
$notification = $pdo->query("
    SELECT COUNT(*) 
    FROM enrollment_applications
    WHERE status = 'rejected'
")->fetchColumn();

?>

<div class="container-fluid mt-4"> <!-- GAP FROM HEADER -->

    <div class="row g-4">

        <!-- Enrollment Applications -->
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-file-lines fa-lg mb-2"></i>
                    <div class="fw-bold">Enrollment Applications</div>
                    <div class="display-6 fw-bold"><?= $applications ?></div>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-user-graduate fa-lg mb-2"></i>
                    <div class="fw-bold">Students</div>
                    <div class="display-6 fw-bold"><?= $totalStudents ?></div>
                </div>
            </div>
        </div>

        <!-- Elementary -->
        <div class="col-md-4">
            <div class="card bg-info text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-school fa-lg mb-2"></i>
                    <div class="fw-bold">Elementary Students</div>
                    <div class="display-6 fw-bold"><?= $elementary ?></div>
                </div>
            </div>
        </div>

        <!-- Junior High -->
        <div class="col-md-4">
            <div class="card bg-warning text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-book fa-lg mb-2"></i>
                    <div class="fw-bold">Junior High Students</div>
                    <div class="display-6 fw-bold"><?= $junior ?></div>
                </div>
            </div>
        </div>

        <!-- Senior High -->
        <div class="col-md-4">
            <div class="card bg-dark text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-graduation-cap fa-lg mb-2"></i>
                    <div class="fw-bold">Senior High Students</div>
                    <div class="display-6 fw-bold"><?= $shs ?></div>
                </div>
            </div>
        </div>

        <!-- Administrators -->
        <div class="col-md-4">
            <div class="card bg-secondary text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-user-shield fa-lg mb-2"></i>
                    <div class="fw-bold">Administrators</div>
                    <div class="display-6 fw-bold"><?= $admins ?></div>
                </div>
            </div>
        </div>

        <!-- Application Documents -->
        <div class="col-md-4">
            <div class="card text-white shadow-sm h-100 text-center" style="background-color: #6f42c1;">
                <div class="card-body">
                    <i class="fa-solid fa-folder-open fa-lg mb-2"></i>
                    <div class="fw-bold">Application Documents</div>
                    <div class="display-6 fw-bold"><?= $documents ?></div>
                </div>
            </div>
        </div>

        <!-- Strands -->
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-layer-group fa-lg mb-2"></i>
                    <div class="fw-bold">Strands</div>
                    <div class="display-6 fw-bold"><?= $strands ?></div>
                </div>
            </div>
        </div>
        
        <!-- Rejected Applications -->
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow-sm h-100 text-center">
                <div class="card-body">
                    <i class="fa-solid fa-layer-group fa-lg mb-2"></i>
                    <div class="fw-bold">Rejected Applications</div>
                    <div class="display-6 fw-bold"><?= $notification ?></div>
                </div>
            </div>
        </div>

    </div>
</div>


