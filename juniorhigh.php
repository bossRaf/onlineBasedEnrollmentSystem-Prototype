<?php
session_start();
require_once "includes/auth.php";
require_once "includes/db.php";

$pdo = connect();

$stmt = $pdo->query("
    SELECT s.student_id,
           s.first_name,
           s.last_name,
           s.grade_level,
           s.grade_number
    FROM students s
    WHERE s.grade_level = 'Junior High'
    ORDER BY s.last_name ASC
");

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "templates/header.php";
?>

<div class="container my-5">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h4 class="mt-3">Junior High School Students</h4>
            <div class="d-flex align-items-center gap-2">
        
        		<!-- Fixed width search bar -->
        		<input type="text"
               		id="searchInput"
               		class="form-control"
               		style="width: 300px;"
               		placeholder="Search by Student ID or Last Name"> &nbsp; &nbsp; 

        			<!-- Back button aligned horizontally -->
        			<a href="index.php" class="btn btn-light btn-sm">
            			Back
        			</a>
    		</div>
        </div>

        <div class="card-body">

            <?php if ($students): ?>
                <table class="table table-bordered table-striped" id="studentsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Grade Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?= $s['student_id'] ?></td>
                                <td><?= $s['last_name'] . ", " . $s['first_name'] ?></td>
                                <td>Grade <?= $s['grade_number'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div id="noResult" class="alert alert-warning d-none">
                    No matching students found.
                </div>

            <?php else: ?>
                <div class="alert alert-info">No Junior High School students found.</div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.getElementById("searchInput").addEventListener("input", function() {

    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#studentsTable tbody tr");
    let noResult = document.getElementById("noResult");
    let found = false;

    rows.forEach(row => {
        let studentId = row.cells[0].textContent.toLowerCase();
        let lastName  = row.cells[1].textContent.split(",")[0].toLowerCase();

        if (studentId.includes(filter) || lastName.includes(filter)) {
            row.style.display = "";
            found = true;
        } else {
            row.style.display = "none";
        }
    });

    if (!found && filter !== "") {
        noResult.classList.remove("d-none");
    } else {
        noResult.classList.add("d-none");
    }

});
</script>

<?php include "templates/footer.php"; ?>

