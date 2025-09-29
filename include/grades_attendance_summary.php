<?php
    require "../db/connect.php";

    $recordsPerPage = 10;

    // -----------------------
    // Grades Table Pagination
    // -----------------------
    $gradesPage = isset($_GET['grades_page']) && is_numeric($_GET['grades_page']) ? (int)$_GET['grades_page'] : 1;
    $gradesOffset = ($gradesPage - 1) * $recordsPerPage;

    // Count total grade rows
    $countGradeSql = "SELECT COUNT(DISTINCT s.id) 
                    FROM student_classes sc
                    JOIN students s ON sc.student_id = s.id
                    WHERE sc.class_id = :class_id";
    $countGradeStmt = $pdo->prepare($countGradeSql);
    $countGradeStmt->execute([':class_id' => $_SESSION["class_id"]]);
    $totalGradeRows = $countGradeStmt->fetchColumn();
    $totalGradePages = ceil($totalGradeRows / $recordsPerPage);

    // Fetch grade records
    $sqlGrades = "SELECT 
                    s.id AS student_id,
                    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    sc.class_id,
                      ROUND((
            COALESCE(AVG(CASE WHEN g.activity_type = 'Exam' THEN g.percentage END), 0) * 0.6
            +
            COALESCE(AVG(CASE WHEN g.activity_type <> 'Exam' THEN g.percentage END), 0) * 0.4
        ), 1) AS overall_grade_percentage,

        MAX(g.grade) AS grade
                FROM student_classes sc
                JOIN students s ON sc.student_id = s.id
                LEFT JOIN grades g ON g.student_class_id = sc.id
                WHERE sc.class_id = :class_id
                GROUP BY s.id, sc.class_id
                LIMIT :limit OFFSET :offset";

    $stmtGrades = $pdo->prepare($sqlGrades);
    $stmtGrades->bindValue(':class_id', $_SESSION["class_id"], PDO::PARAM_INT);
    $stmtGrades->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmtGrades->bindValue(':offset', $gradesOffset, PDO::PARAM_INT);
    $stmtGrades->execute();
    $grades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

    // ---------------------------
    // Attendance Table Pagination
    // ---------------------------
    $attendancePage = isset($_GET['attendance_page']) && is_numeric($_GET['attendance_page']) ? (int)$_GET['attendance_page'] : 1;
    $attendanceOffset = ($attendancePage - 1) * $recordsPerPage;

    // Count total attendance rows
    $countAttendanceSql = "SELECT COUNT(DISTINCT s.id)
                        FROM student_classes sc
                        JOIN students s ON sc.student_id = s.id
                        WHERE sc.class_id = :class_id";
    $countAttendanceStmt = $pdo->prepare($countAttendanceSql);
    $countAttendanceStmt->execute([':class_id' => $_SESSION["class_id"]]);
    $totalAttendanceRows = $countAttendanceStmt->fetchColumn();
    $totalAttendancePages = ceil($totalAttendanceRows / $recordsPerPage);

    // Fetch attendance records
    $sqlAttendance = "SELECT 
                        s.id AS student_id,
                        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                        sc.class_id,
                        ROUND(
                            (SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) * 100.0) / COUNT(a.id),
                            1   
                        ) AS attendance_rate
                    FROM student_classes sc
                    JOIN students s ON sc.student_id = s.id
                    LEFT JOIN attendance a ON a.student_class_id = sc.id
                    WHERE sc.class_id = :class_id
                    GROUP BY s.id, sc.class_id
                    LIMIT :limit OFFSET :offset";

    $stmtAttendance = $pdo->prepare($sqlAttendance);
    $stmtAttendance->bindValue(':class_id', $_SESSION["class_id"], PDO::PARAM_INT);
    $stmtAttendance->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmtAttendance->bindValue(':offset', $attendanceOffset, PDO::PARAM_INT);
    $stmtAttendance->execute();
    $attendance = $stmtAttendance->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ========================= -->
<!-- Grade Summary Table -->
<!-- ========================= -->
<div class="card mt-4">
    <div class="card-header py-3">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Grade Summary</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">Student ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Percentage</th>
                        <th scope="col">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grades)): ?>
                        <?php foreach($grades as $student): ?>
                            <tr>
                                <td><?= $student["student_id"] ?></td>
                                <td class="align-middle"><?= $student["student_name"] ?></td>
                                <?php
                                    $percentage = $student["overall_grade_percentage"];
                                    if ($percentage >= 80) {
                                        $colorClass = "text-success"; 
                                    } elseif ($percentage >= 70) {
                                        $colorClass = "text-warning"; 
                                    } else {
                                        $colorClass = "text-danger"; 
                                    }
                                ?>
                                <td class="align-middle <?= $colorClass; ?>">
                                    <?= isset($percentage) ? $percentage . "%" : "0%"; ?>
                                </td>
                                <td class="align-middle <?= $colorClass; ?>">
                                    <?= $student["grade"] ?? ""; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No grade records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Export Button -->
            <form action="../backend/export_pdf.php" method="post" target="_blank">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="export_grades_pdf" class="btn btn-success">
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Grades Pagination -->
        <nav aria-label="Grade pagination">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?= ($gradesPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="?grades_page=<?= max(1, $gradesPage - 1) ?>&attendance_page=<?= $attendancePage ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalGradePages; $i++): ?>
                    <li class="page-item <?= ($gradesPage == $i) ? 'active' : '' ?>">
                        <a class="page-link text-success <?= ($gradesPage == $i) ? 'bg-success text-white border-success' : '' ?>" 
                        href="?grades_page=<?= $i ?>&attendance_page=<?= $attendancePage ?>">
                        <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($gradesPage >= $totalGradePages) ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="?grades_page=<?= min($totalGradePages, $gradesPage + 1) ?>&attendance_page=<?= $attendancePage ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- ========================= -->
<!-- Attendance Summary Table -->
<!-- ========================= -->
<div class="card mt-4">
    <div class="card-header py-3">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Attendance Records Summary</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">Student ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Attendance Rate</th>
                        <th scope="col">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendance)): ?>
                        <?php foreach($attendance as $student): ?>
                            <?php
                                $attendanceRate = $student["attendance_rate"];

                                // set color
                                if ($attendanceRate >= 80) {
                                    $attColorClass = "text-success"; 
                                    $rateLabel = "Excellent";
                                } elseif ($attendanceRate >= 70) {
                                    $attColorClass = "text-warning"; 
                                    $rateLabel = "Good";
                                } else {
                                    $attColorClass = "text-danger"; 
                                    $rateLabel = "Poor";
                                }
                            ?>
                            <tr>
                                <td><?= $student["student_id"] ?></td>
                                <td><?= $student["student_name"] ?></td>
                                <td class="align-middle <?= $attColorClass; ?>">
                                    <?= $attendanceRate !== null ? $attendanceRate . "%" : "0%" ?>
                                </td>
                                <td class="align-middle <?= $attColorClass; ?>">
                                    <?= $rateLabel ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>

            <!-- Export Button -->
            <form action="../backend/export_pdf.php" method="post" target="_blank">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="export_attendance_pdf" class="btn btn-success">
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Pagination -->
        <nav aria-label="Attendance pagination">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?= ($attendancePage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="?attendance_page=<?= max(1, $attendancePage - 1) ?>&grades_page=<?= $gradesPage ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalAttendancePages; $i++): ?>
                    <li class="page-item <?= ($attendancePage == $i) ? 'active' : '' ?>">
                        <a class="page-link text-success <?= ($attendancePage == $i) ? 'bg-success text-white border-success' : '' ?>" 
                        href="?attendance_page=<?= $i ?>&grades_page=<?= $gradesPage ?>">
                        <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item text-dark <?= ($attendancePage >= $totalAttendancePages) ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="?attendance_page=<?= min($totalAttendancePages, $attendancePage + 1) ?>&grades_page=<?= $gradesPage ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
