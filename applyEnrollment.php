<?php
session_start();
require_once "includes/db.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
    $pdo = connect();
    $pdo->beginTransaction();

    // 1️⃣ Insert application
    $stmt = $pdo->prepare("
        INSERT INTO enrollment_applications (
            first_name, middle_name, last_name, suffix,
            birthdate, gender, contact_number, email, address,
            school_year, grade_level, grade_number, strand_id,
            previous_school, status
        ) VALUES (
            :first_name, :middle_name, :last_name, :suffix,
            :birthdate, :gender, :contact_number, :email, :address,
            :school_year, :grade_level, :grade_number, :strand_id,
            :previous_school, :status
        )
    ");

    $stmt->execute([
        'first_name' => $_POST['first_name'],
        'middle_name' => $_POST['middle_name'],
        'last_name' => $_POST['last_name'],
        'suffix' => $_POST['suffix'],
        'birthdate' => $_POST['birthdate'],
        'gender' => $_POST['gender'],
        'contact_number' => $_POST['contact_number'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'school_year' => $_POST['school_year'],
        'grade_level' => $_POST['grade_level'],
        'grade_number' => $_POST['grade_number'],
        'strand_id' => $_POST['strand_id'] ?? null,
        'previous_school' => $_POST['previous_school'],
        'status' => 'pending'
    ]);

    // 2️⃣ Get application_id
    $application_id = $pdo->lastInsertId();

    // 3️⃣ Handle file uploads
    $uploadDir = "img/"; // make sure this folder exists

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['documents']['name'][0])) {

        foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {

            $originalName = $_FILES['documents']['name'][$key];
            $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);

            $newFileName = uniqid("doc_") . "." . $fileExt;
            $filePath = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $filePath)) {

                // 4️⃣ Insert into application_documents
                $docStmt = $pdo->prepare("
                    INSERT INTO application_documents 
                    (application_id, document_type, file_path)
                    VALUES (:application_id, :document_type, :file_path)
                ");

                $docStmt->execute([
                    'application_id' => $application_id,
                    'document_type' => $_POST['document_type'][0] ?? 'Other',
                    'file_path' => $filePath
                ]);
            }
        }
    }

    $pdo->commit();

    $success = "Enrollment application submitted successfully! Please wait for administrator approval.";

	} catch (PDOException $e) {

    $pdo->rollBack();
    $error = "Failed to submit application. Please try again.";
    }
}

include "templates/header.php";

?>

<div class="container my-5">
    <div class="card shadow border-0">

        <!-- HEADER -->
        <div class="card-header text-white py-3" style="background-color: #4CAF50;">
            <h4 class="mb-0">
                Student Online Enrollment Application
            </h4>
            
            <a href="index.php" class="btn btn-light btn-sm float-end">
        		Back
    		</a>
            
            <small>
                Submit your application. The school administrator will review it.
            </small>
        </div>

        <div class="card-body p-4">
            <?php if ($success): ?>
    			<div class="alert alert-success">
        	<?php echo $success; ?>
    	</div>
			<?php endif; ?>

			<?php if ($error): ?>
    	<div class="alert alert-danger">
        	<?php echo $error; ?>
   		 </div>
			<?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <!-- ================= APPLICANT INFORMATION ================= -->
                <h5 class="fw-bold border-bottom pb-2 mb-4">
                    Applicant Information
                </h5>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" class="form-control" name="first_name" placeholder="Enter first name" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" class="form-control" name="middle_name" placeholder="Enter middle name (optional)">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" class="form-control" name="last_name" placeholder="Enter last name" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Suffix</label>
                        <input type="text" class="form-control" name="suffix" placeholder="e.g. Jr., Sr., III">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Birthdate</label>
                        <input type="date" class="form-control" name="birthdate" placeholder="Select birthdate" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="col-md-4 fw-bold">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" placeholder="Enter contact number (e.g. 09123456789)" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter email address (example@gmail.com)">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Complete Address</label>
                    <textarea class="form-control" rows="3" name="address" placeholder="Enter complete home address"></textarea>
                </div>

                <!-- ================= ACADEMIC DETAILS ================= -->
                <h5 class="fw-bold border-bottom pb-2 mb-4">
                    Academic Details
                </h5>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">School Year</label>
                        <input type="text" class="form-control"
                               name="school_year"
                               placeholder="e.g. 2026-2027"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Grade Level</label>
                        <select class="form-select" name="grade_level" required>
                            <option value="">Select</option>
                            <option>Elementary</option>
                            <option>Junior High</option>
                            <option>Senior High</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Grade Number</label>
                        <select class="form-select" name="grade_number" id="gradeNumber" required>
                            <option value="">Select</option>
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                            <option>5</option>
                            <option>6</option>
                            <option>7</option>
                            <option>8</option>
                            <option>9</option>
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                        </select>
                    </div>

                    <div class="col-md-3" id="strandField">
                        <label class="form-label fw-bold">
                            Strand (For Senior High Only)
                        </label>
                        <select class="form-select" name="strand_id">
                            <option value="" selected disabled>Select Strand</option>

    						<option value="1">STEM - Science, Technology, Engineering and Mathematics (Academic)</option>
    						<option value="2">ABM - Accountancy, Business and Management (Academic)</option>
    						<option value="3">HUMSS - Humanities and Social Sciences (Academic)</option>
    						<option value="4">GAS - General Academic Strand (Academic)</option>
    						<option value="5">ICT - Information and Communications Technology (TVL)</option>
    						<option value="6">HE - Home Economics (TVL)</option>
    						<option value="7">IA - Industrial Arts (TVL)</option>
    						<option value="8">SMAW - Shielded Metal Arc Welding (TVL)</option>
    						<option value="9">ARTS - Arts and Design Track (Arts and Design)</option>
    						<option value="10">SPORTS - Sports Track (Sports)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Previous School</label>
                    <input type="text" class="form-control" name="previous_school" placeholder="Enter previous school name">
                </div>

                <!-- ================= DOCUMENT SUBMISSION ================= -->
                <h5 class="fw-bold border-bottom pb-2 mb-4">
                    Required Documents
                </h5>

                <div class="alert alert-info">
                    Upload all required documents. You may select multiple files at once.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Document Type</label>
                    <select class="form-select" name="document_type[]" required>
                        <option value="">Select</option>
                        <option>Birth Certificate</option>
                        <option>Report Card</option>
                        <option>Form 137</option>
                        <option>Good Moral Certificate</option>
                        <option>2x2 Picture</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Upload Files</label>
                    <input type="file"
                           class="form-control"
                           name="documents[]"
                           multiple
                           accept=".jpg,.jpeg,.png,.pdf"
                           required>
                </div>

                <!-- Hidden status field -->
                <input type="hidden" name="status" value="pending">

                <!-- CONFIRMATION -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" required>
                    <label class="form-check-label">
                        I confirm that the information provided is accurate.
                    </label>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        Submit Enrollment Application
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php include "templates/footer.php"; ?>

