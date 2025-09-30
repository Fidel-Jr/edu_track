<?php
    require "../db/connect.php";
    session_start();
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

    // Fetch attendance records
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
                                                    </tr>
                                            <?php endforeach; ?>
                                        
                                        <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No students records found.</td>
                                                    </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <!-- Export Button -->
                                <!-- <form action="../backend/export_pdf.php" method="post" target="_blank">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" name="export_attendance_pdf" class="btn btn-success">
                                            <i class="fas fa-file-pdf"></i> Export to PDF
                                        </button>
                                    </div>
                                </form> -->
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