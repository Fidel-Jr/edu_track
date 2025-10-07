<?php
require "../db/connect.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
    header("Location: ../welcome.php");
    exit;
}
require "../backend/dashboard_functions.php";

$classId = $_SESSION["class_id"];
$date = isset($_GET['date']) ? $_GET['date'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'attendance';
$results = [];
$recordsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;
$totalRows = 0;

if ($date && ($type === 'attendance' || $type === 'grades')) {
    if ($type === 'attendance') {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance a JOIN student_classes sc ON a.student_class_id = sc.id JOIN class c ON sc.class_id = c.id WHERE a.date = :date AND c.id = :class_id");
        $countStmt->execute(['date' => $date, 'class_id' => $classId]);
        $totalRows = $countStmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT a.id, a.date, a.status, s.id AS student_id, s.first_name, s.last_name, c.course_name
            FROM attendance a
            JOIN student_classes sc ON a.student_class_id = sc.id
            JOIN students s ON sc.student_id = s.id
            JOIN class c ON sc.class_id = c.id
            WHERE a.date = :date AND c.id = :class_id
            LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM grades g JOIN student_classes sc ON g.student_class_id = sc.id JOIN class c ON sc.class_id = c.id WHERE g.date = :date AND c.id = :class_id");
        $countStmt->execute(['date' => $date, 'class_id' => $classId]);
        $totalRows = $countStmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT g.id, g.date, g.activity_type, g.activity_name, g.maximum_score, g.score, s.id AS student_id, s.first_name, s.last_name, c.course_name
            FROM grades g
            JOIN student_classes sc ON g.student_class_id = sc.id
            JOIN students s ON sc.student_id = s.id
            JOIN class c ON sc.class_id = c.id
            WHERE g.date = :date AND c.id = :class_id
            LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records Search</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Pagination Styles */
        .pagination .page-link {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid #dee2e6;
        }

        .pagination .page-link:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
            border-color: #dee2e6;
        }

        .pagination .page-link:focus {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .page-item.disabled .page-link {
            background-color: white;
            color: #6c757d;
            border-color: #dee2e6;
        }
        
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <?php include 'navbar.php'; ?>
            <div class="container-fluid p-5">
                <h2 class="mb-4">Search Records</h2>
                <form class="row g-3 mb-4" method="GET">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="attendance" <?= $type === 'attendance' ? 'selected' : '' ?>>Attendance</option>
                            <option value="grades" <?= $type === 'grades' ? 'selected' : '' ?>>Grades</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn w-100" style="background: var(--primary-color); color: #fff;"><i class="bi bi-search"></i> Search</button>
                    </div>
                </form>
                <?php if ($date && ($type === 'attendance' || $type === 'grades')): ?>
                    <?php if ($type === 'attendance'): ?>
                        <form method="POST" action="../backend/bulk_delete_attendance.php" id="bulkDeleteAttendanceForm">
                            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle border rounded">
                                <thead style="background: var(--primary-color); color: #fff;">
                                    <tr>
                                        <th><input type="checkbox" id="selectAllAttendance"></th>
                                        <th>Date</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Class</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="<?= $row['id'] ?>"></td>
                                            <td><?= htmlspecialchars($row['date']) ?></td>
                                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                            <td><?= htmlspecialchars($row['status']) ?></td>
                                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                                            <td>
                                                <a href="edit_attendance.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                                <a href="../backend/delete_attendance.php?id=<?= $row['id'] ?>&date=<?= $row["date"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this attendance record?')"><i class="bi bi-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($results)): ?>
                                        <tr><td colspan="7" class="text-center text-muted">No attendance records found for this date.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="submit" class="btn" style="background: var(--primary-color); color: #fff;" onclick="return confirm('Delete selected attendance records?')"><i class="bi bi-trash"></i>Delete All</button>
                            <?php
                                $totalPages = ceil($totalRows / $recordsPerPage);
                                if ($totalPages > 1):
                                    $adjacents = 2;
                                    $start = max(1, $page - $adjacents);
                                    $end = min($totalPages, $page + $adjacents);
                            ?>
                            <nav aria-label="Attendance pagination">
                                <ul class="pagination mb-0">
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?date=<?= urlencode($date) ?>&type=attendance&page=<?= max(1, $page-1) ?>">Previous</a>
                                    </li>
                                    <?php if ($start > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?date=<?= urlencode($date) ?>&type=attendance&page=1">1</a>
                                        </li>
                                        <?php if ($start > 2): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                            <a class="page-link" href="?date=<?= urlencode($date) ?>&type=attendance&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($end < $totalPages): ?>
                                        <?php if ($end < $totalPages - 1): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?date=<?= urlencode($date) ?>&type=attendance&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?date=<?= urlencode($date) ?>&type=attendance&page=<?= min($totalPages, $page+1) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="../backend/bulk_delete_grades.php" id="bulkDeleteGradesForm">
                            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle border rounded">
                                <thead style="background: var(--primary-color); color: #fff;">
                                    <tr>
                                        <th><input type="checkbox" id="selectAllGrades"></th>
                                        <th>Date</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Activity</th>
                                        <th>Type</th>
                                        <th>Max Score</th>
                                        <th>Score</th>
                                        <th>Class</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="<?= $row['id'] ?>"></td>
                                            <td><?= htmlspecialchars($row['date']) ?></td>
                                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                            <td><?= htmlspecialchars($row['activity_name']) ?></td>
                                            <td><?= htmlspecialchars($row['activity_type']) ?></td>
                                            <td><?= htmlspecialchars($row['maximum_score']) ?></td>
                                            <td><?= htmlspecialchars($row['score']) ?></td>
                                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                                            <td>
                                                <a href="edit_grade.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                                <a href="../backend/delete_grade.php?id=<?= $row['id'] ?>&date=<?= $row["date"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this grade record?')"><i class="bi bi-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($results)): ?>
                                        <tr><td colspan="10" class="text-center text-muted">No grade records found for this date.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="submit" class="btn" style="background: var(--primary-color); color: #fff;" onclick="return confirm('Delete selected grade records?')"><i class="bi bi-trash"></i> Delete All</button>
                            <?php
                                $totalPages = ceil($totalRows / $recordsPerPage);
                                if ($totalPages > 1):
                                    $adjacents = 2;
                                    $start = max(1, $page - $adjacents);
                                    $end = min($totalPages, $page + $adjacents);
                            ?>
                            <nav aria-label="Grades pagination">
                                <ul class="pagination mb-0">
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" style="color: var(--primary-color);" href="?date=<?= urlencode($date) ?>&type=grades&page=<?= max(1, $page-1) ?>">Previous</a>
                                    </li>
                                    <?php if ($start > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" style="color: var(--primary-color);" href="?date=<?= urlencode($date) ?>&type=grades&page=1">1</a>
                                        </li>
                                        <?php if ($start > 2): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                            <a class="page-link" href="?date=<?= urlencode($date) ?>&type=grades&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($end < $totalPages): ?>
                                        <?php if ($end < $totalPages - 1): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" style="color: var(--primary-color);" href="?date=<?= urlencode($date) ?>&type=grades&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                        <a class="page-link" style="color: var(--primary-color);" href="?date=<?= urlencode($date) ?>&type=grades&page=<?= min($totalPages, $page+1) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Select all checkboxes for attendance
    document.addEventListener('DOMContentLoaded', function() {
        var selectAllAttendance = document.getElementById('selectAllAttendance');
        if (selectAllAttendance) {
            selectAllAttendance.addEventListener('change', function() {
                document.querySelectorAll('#bulkDeleteAttendanceForm input[type="checkbox"][name="ids[]"]').forEach(function(cb) {
                    cb.checked = selectAllAttendance.checked;
                });
            });
        }
        var selectAllGrades = document.getElementById('selectAllGrades');
        if (selectAllGrades) {
            selectAllGrades.addEventListener('change', function() {
                document.querySelectorAll('#bulkDeleteGradesForm input[type="checkbox"][name="ids[]"]').forEach(function(cb) {
                    cb.checked = selectAllGrades.checked;
                });
            });
        }
    });
    </script>
     <script src="../assets/js/main.js"></script>
</body>
</html>
