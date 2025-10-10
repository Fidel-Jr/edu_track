<?php
    require "../db/connect.php";
    session_start();

    if (isset($_POST['delete_student_id'])) {
        $studentId = $_POST['delete_student_id'];
        $classId = $_SESSION["class_id"];

        // Remove from student_classes
        $stmt = $pdo->prepare("DELETE FROM student_classes WHERE student_id = :student_id AND class_id = :class_id");
        $stmt->execute([':student_id' => $studentId, ':class_id' => $classId]);

        // Remove related attendance
        $stmt = $pdo->prepare("DELETE a FROM attendance a
            INNER JOIN student_classes sc ON a.student_class_id = sc.id
            WHERE sc.student_id = :student_id AND sc.class_id = :class_id");
        $stmt->execute([':student_id' => $studentId, ':class_id' => $classId]);

        // Remove related grades
        $stmt = $pdo->prepare("DELETE g FROM grades g
            INNER JOIN student_classes sc ON g.student_class_id = sc.id
            WHERE sc.student_id = :student_id AND sc.class_id = :class_id");
        $stmt->execute([':student_id' => $studentId, ':class_id' => $classId]);

        // Optional: Remove student from students table if not enrolled in any class
        // $stmt = $pdo->prepare("DELETE FROM students WHERE id = :student_id AND NOT EXISTS (SELECT 1 FROM student_classes WHERE student_id = :student_id)");
        // $stmt->execute([':student_id' => $studentId]);

        header("Location: students.php?students_page=" . ($_GET['students_page'] ?? 1));
        exit;
    }

    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }
    $recordsPerPage = 10;
    
    $studentsPage = isset($_GET['students_page']) && is_numeric($_GET['students_page']) 
    ? (int)$_GET['students_page'] 
    : 1;    
    $studentsOffset = ($studentsPage - 1) * $recordsPerPage;

    // Count total attendance rows
    $countStudentsSql = "SELECT COUNT(DISTINCT s.id)
                        FROM student_classes sc
                        JOIN students s ON sc.student_id = s.id
                        WHERE sc.class_id = :class_id";
    $countStudentsStmt = $pdo->prepare($countStudentsSql);
    $countStudentsStmt->execute([':class_id' => $_SESSION["class_id"]]);
    $totalStudentsRows = $countStudentsStmt->fetchColumn();
    $totalStudentsPages = ceil($totalStudentsRows / $recordsPerPage);

    $sqlStudents = "SELECT 
                        s.id AS student_id,
                        s.first_name, s.last_name, s.address, s.phone,
                        sc.class_id
                    FROM student_classes sc
                    JOIN students s ON sc.student_id = s.id
                    WHERE sc.class_id = :class_id
                    ORDER BY s.last_name, s.first_name
                    LIMIT :limit OFFSET :offset";

    $stmtStudents = $pdo->prepare($sqlStudents);
    $stmtStudents->bindValue(':class_id', $_SESSION["class_id"], PDO::PARAM_INT);
    $stmtStudents->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmtStudents->bindValue(':offset', $studentsOffset, PDO::PARAM_INT);
    $stmtStudents->execute();
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-link:hover {
            color: var(--primary-color);
        }
        
        .page-link:focus {
            color: white !important;
            background-color: green;
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
        <div class="wrapper">
        <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
                <!-- Main Content -->
                <div class="main-content">
                <!-- Top Navbar -->
                
                <?php include 'navbar.php'; ?>

                <!-- Page Content -->
                <div class="container-fluid p-5">
                    <h2 class="mb-4">Students Lists</h2>
                    
                    <div class="card mt-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Student Enrolled In Class</h5>
                            <div class="d-flex justify-content-end">
                                <div>
                                    <a href="new_student.php" class="btn btn-success">Add Student +</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Student ID</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Address</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count = $studentsOffset + 1; if (!empty($students)): ?>
                                            
                                            <?php foreach($students as $student): ?>
                                                    <tr>
                                                        <th scope="row"><?= $count++ ?></th>
                                                        <td><?= $student["student_id"] ?></td>
                                                        <td><?= $student["first_name"] ?></td>
                                                        <td><?= $student["last_name"] ?></td>
                                                        <td><?= $student["address"] ?></td>
                                                        <td><?= $student["phone"] ?></td>
                                                        <td>
                                                            <a href="edit_student.php?id=<?= $student["student_id"] ?>" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                                <input type="hidden" name="delete_student_id" value="<?= $student["student_id"] ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; ?>
                                        
                                        <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No students records found.</td>
                                                    </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            </div>

                            <!-- Students Pagination -->
                            <nav aria-label="Students pagination">
                                <ul class="pagination justify-content-center mt-4">
                                    <!-- Previous Button -->
                                    <li class="page-item <?= ($studentsPage <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link text-dark" 
                                        href="?students_page=<?= max(1, $studentsPage - 1) ?>">
                                        Previous
                                        </a>
                                    </li>

                                    <!-- First page -->
                                    <?php if ($studentsPage > 3): ?>
                                        <li class="page-item">
                                            <a class="page-link text-success" 
                                            href="?students_page=1">
                                            1
                                            </a>
                                        </li>
                                        <?php if ($studentsPage > 4): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link text-dark">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Page Numbers around current page -->
                                    <?php for ($i = max(1, $studentsPage - 2); $i <= min($totalStudentsPages, $studentsPage + 2); $i++): ?>
                                        <li class="page-item <?= ($studentsPage == $i) ? 'active' : '' ?>">
                                            <a class="page-link <?= ($studentsPage == $i) ? 'bg-success text-white border-success' : 'text-success' ?>" 
                                            href="?students_page=<?= $i ?>">
                                            <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Last page -->
                                    <?php if ($studentsPage < $totalStudentsPages - 2): ?>
                                        <?php if ($studentsPage < $totalStudentsPages - 3): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link text-dark">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link text-success" 
                                            href="?students_page=<?= $totalStudentsPages ?>">
                                            <?= $totalStudentsPages ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?= ($studentsPage >= $totalStudentsPages) ? 'disabled' : '' ?>">
                                        <a class="page-link text-dark" 
                                        href="?students_page=<?= min($totalStudentsPages, $studentsPage + 1) ?>">
                                        Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                    
                </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
    
    <script src="../assets/js/main.js"></script>
   
</body>
</html>