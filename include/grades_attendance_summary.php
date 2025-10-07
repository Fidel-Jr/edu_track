<?php
    require "../db/connect.php";
    require "../backend/helpers.php";

    // --- Modular Functions ---
    function getPageInfo($param, $recordsPerPage = 10) {
        $page = isset($_GET[$param]) && is_numeric($_GET[$param]) ? (int)$_GET[$param] : 1;
        $offset = ($page - 1) * $recordsPerPage;
        return [$page, $offset];
    }

    function getTotalRows($pdo, $sql, $classId) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':class_id' => $classId]);
        return $stmt->fetchColumn();
    }

    function fetchGradeSummary($pdo, $classId, $limit, $offset) {
        $sql = "SELECT 
                    s.id AS student_id,
                    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    sc.class_id,
                    ROUND((
                        (CASE 
                            WHEN COUNT(CASE WHEN g.activity_type = 'Exam' THEN 1 END) > 0 
                            THEN AVG(CASE WHEN g.activity_type = 'Exam' THEN g.percentage END) 
                            ELSE 0 
                        END) * 0.6
                        +
                        (CASE 
                            WHEN COUNT(CASE WHEN g.activity_type <> 'Exam' THEN 1 END) > 0 
                            THEN AVG(CASE WHEN g.activity_type <> 'Exam' THEN g.percentage END) 
                            ELSE 0 
                        END) * 0.4
                    ), 1) AS overall_grade_percentage,
                    MAX(g.grade) AS grade
                FROM student_classes sc
                JOIN students s ON sc.student_id = s.id
                LEFT JOIN grades g ON g.student_class_id = sc.id
                WHERE sc.class_id = :class_id
                GROUP BY s.id, sc.class_id
                HAVING 1=1
                ORDER BY s.last_name, s.first_name
                LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    function fetchAttendanceSummary($pdo, $classId, $limit, $offset) {
        $sql = "SELECT 
                    s.id AS student_id,
                    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    sc.class_id,
                    ROUND((SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) * 100.0) / COUNT(a.id), 1) AS attendance_rate
                FROM student_classes sc
                JOIN students s ON sc.student_id = s.id
                LEFT JOIN attendance a ON a.student_class_id = sc.id
                WHERE sc.class_id = :class_id
                GROUP BY s.id, sc.class_id
                ORDER BY s.last_name, s.first_name
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Main Script ---
    $recordsPerPage = 10;
    $classId = $_SESSION["class_id"];

    // Grades
    list($gradesPage, $gradesOffset) = getPageInfo('grades_page', $recordsPerPage);
    $totalGradeRows = getTotalRows($pdo, "SELECT COUNT(DISTINCT s.id) FROM student_classes sc JOIN students s ON sc.student_id = s.id WHERE sc.class_id = :class_id", $classId);
    $totalGradePages = ceil($totalGradeRows / $recordsPerPage);
    $grades = fetchGradeSummary($pdo, $classId, $recordsPerPage, $gradesOffset);

    // Attendance
    list($attendancePage, $attendanceOffset) = getPageInfo('attendance_page', $recordsPerPage);
    $totalAttendanceRows = getTotalRows($pdo, "SELECT COUNT(DISTINCT s.id) FROM student_classes sc JOIN students s ON sc.student_id = s.id WHERE sc.class_id = :class_id", $classId);
    $totalAttendancePages = ceil($totalAttendanceRows / $recordsPerPage);
    $attendance = fetchAttendanceSummary($pdo, $classId, $recordsPerPage, $attendanceOffset);
?>
<style>
    /* Pagination link focus style */
    .pagination .page-link:focus,
    .pagination .page-link:active {
        background-color: green !important;
        color: white !important;
        border-color: green !important;
        outline: none !important; /* remove default focus outline */
    }
</style>
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
                                    list($letter, $colorClass) = getGradeAndColor($percentage);
                                ?>

                                <td class="align-middle <?= $colorClass; ?>">
                                    <?= isset($percentage) ? $percentage . "%" : "0%"; ?>
                                </td>
                                <td class="align-middle <?= $colorClass; ?>">
                                    <?= $letter ?>
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
                <!-- Previous button -->
                <li class="page-item <?= ($gradesPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?grades_page=<?= max(1, $gradesPage - 1) ?>&attendance_page=<?= $attendancePage ?>" style="<?= ($gradesPage > 1 ? 'color: var(--primary-color);' : 'color: #333;') ?>">Previous</a>
                </li>

                <!-- First page -->
                <?php if ($gradesPage > 3): ?>
                    <li class="page-item">
                        <a class="page-link text-success" href="?grades_page=1&attendance_page=<?= $attendancePage ?>">1</a>
                    </li>
                    <?php if ($gradesPage > 4): ?>
                        <li class="page-item disabled">
                            <span class="page-link text-dark">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page numbers around current page -->
                <?php for ($i = max(1, $gradesPage - 2); $i <= min($totalGradePages, $gradesPage + 2); $i++): ?>
                    <li class="page-item <?= ($gradesPage == $i) ? 'active' : '' ?>">
                        <a class="page-link text-success <?= ($gradesPage == $i) ? 'bg-success text-white border-success' : '' ?>" 
                        href="?grades_page=<?= $i ?>&attendance_page=<?= $attendancePage ?>">
                        <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Last page -->
                <?php if ($gradesPage < $totalGradePages - 2): ?>
                    <?php if ($gradesPage < $totalGradePages - 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link text-dark">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link text-success" href="?grades_page=<?= $totalGradePages ?>&attendance_page=<?= $attendancePage ?>"><?= $totalGradePages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next button -->
                <li class="page-item <?= ($gradesPage >= $totalGradePages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?grades_page=<?= min($totalGradePages, $gradesPage + 1) ?>&attendance_page=<?= $attendancePage ?>" style="<?= ($gradesPage < $totalGradePages ? 'color: var(--primary-color);' : 'color: #333;') ?>">Next</a>
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
                <!-- Previous button -->
                <li class="page-item <?= ($attendancePage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?attendance_page=<?= max(1, $attendancePage - 1) ?>&grades_page=<?= $gradesPage ?>" style="<?= ($attendancePage > 1 ? 'color: var(--primary-color);' : 'color: #333;') ?>">Previous</a>
                </li>

                <!-- First page -->
                <?php if ($attendancePage > 3): ?>
                    <li class="page-item">
                        <a class="page-link text-success" href="?attendance_page=1&grades_page=<?= $gradesPage ?>">1</a>
                    </li>
                    <?php if ($attendancePage > 4): ?>
                        <li class="page-item disabled">
                            <span class="page-link text-dark">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page numbers around current page -->
                <?php for ($i = max(1, $attendancePage - 2); $i <= min($totalAttendancePages, $attendancePage + 2); $i++): ?>
                    <li class="page-item <?= ($attendancePage == $i) ? 'active' : '' ?>">
                        <a class="page-link text-success <?= ($attendancePage == $i) ? 'bg-success text-white border-success' : '' ?>" 
                        href="?attendance_page=<?= $i ?>&grades_page=<?= $gradesPage ?>">
                        <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Last page -->
                <?php if ($attendancePage < $totalAttendancePages - 2): ?>
                    <?php if ($attendancePage < $totalAttendancePages - 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link text-dark">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link text-success" href="?attendance_page=<?= $totalAttendancePages ?>&grades_page=<?= $gradesPage ?>"><?= $totalAttendancePages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next button -->
                <li class="page-item <?= ($attendancePage >= $totalAttendancePages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?attendance_page=<?= min($totalAttendancePages, $attendancePage + 1) ?>&grades_page=<?= $gradesPage ?>" style="<?= ($attendancePage < $totalAttendancePages ? 'color: var(--primary-color);' : 'color: #333;') ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
