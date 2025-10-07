<?php 

    include "../db/connect.php";
    session_start();
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }

    // Apply filters
    $filters = [];
    $params = [':class_id' => $_SESSION["class_id"]];

    if (!empty($_GET['day'])) {
        $filters[] = "DAY(attendance.date) = :day";
        $params[':day'] = $_GET['day'];
    }   
    if (!empty($_GET['month'])) {
        $filters[] = "MONTH(attendance.date) = :month";
        $params[':month'] = $_GET['month'];
    }
    if (!empty($_GET['status'])) {
        $filters[] = "attendance.status = :status";
        $params[':status'] = $_GET['status'];
    }
    if (!empty($_GET['student'])) {
        $filters[] = "(students.first_name LIKE :student OR students.last_name LIKE :student)";
        $params[':student'] = "%" . $_GET['student'] . "%";
    }

    $filterSql = "";
    if (!empty($filters)) {
        $filterSql = " AND " . implode(" AND ", $filters);
    }

    // Fetch class name from database
    $stmt = $pdo->prepare("SELECT course_name FROM class WHERE id = :class_id");
    $stmt->execute([':class_id' => $_SESSION["class_id"]]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);
    $className = $class["course_name"];

    // Attendance pagination
    $attendancePage = isset($_GET['attendance_page']) && is_numeric($_GET['attendance_page']) 
        ? (int) $_GET['attendance_page'] : 1;
    $attendancePerPage = 10;
    $attendanceOffset = ($attendancePage - 1) * $attendancePerPage;

    // Count total attendance rows
    $countSql = "SELECT COUNT(*) 
                 FROM attendance
                 INNER JOIN student_classes 
                     ON attendance.student_class_id = student_classes.id
                 INNER JOIN students 
                     ON student_classes.student_id = students.id
                 INNER JOIN class 
                     ON student_classes.class_id = class.id
                 WHERE class.id = :class_id $filterSql";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalAttendanceRows = $countStmt->fetchColumn();
    $totalAttendancePages = ceil($totalAttendanceRows / $attendancePerPage);

    // Fetch paginated rows
    $sql = "SELECT 
                attendance.*, students.first_name, students.last_name, students.id AS student_id
            FROM attendance
            INNER JOIN student_classes 
                ON attendance.student_class_id = student_classes.id
            INNER JOIN students 
                ON student_classes.student_id = students.id
            INNER JOIN class 
                ON student_classes.class_id = class.id
            WHERE class.id = :class_id $filterSql
            ORDER BY attendance.date DESC, students.last_name ASC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === ':class_id') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } elseif ($key === ':day' || $key === ':month') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    $stmt->bindValue(':limit', $attendancePerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $attendanceOffset, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $queryString = http_build_query(array_merge($_GET, ['attendance_page' => null]));
    

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
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important; /* Prevents layout shift */
        }   
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #228c22;
            border-color: #228c22;
        }
        
        .status-present {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-absent {
            color: #dc3545;
            font-weight: 600;
        }
        
        .filter-section {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link {
            color: var(--primary-color);
        }
        
        .attendance-actions {
            display: flex;
            gap: 10px;
        }
        
        .student-list-item {
            transition: all 0.3s ease;
        }
        
        .student-list-item:hover {
            background-color: #f0f5ff;
        }
        
        .attendance-page {
            display: none;
        }
        
        #attendanceCreationPage {
            display: block;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .back-button {
            cursor: pointer;
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
            <div class="container-fluid p-4">
                <!-- Attendance Creation Page -->
    <div id="attendanceCreationPage" class="attendance-page">
        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Attendance Management</h2>
                    <p class="text-muted"><?php echo $className ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                        <i class="fas fa-plus me-1"></i> New Attendance
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="dayFilter" class="form-label">Day</label>
                                <select class="form-select" id="dayFilter" name="day">
                                    <option value="">All Days</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>" <?= (isset($_GET['day']) && $_GET['day'] == $i) ? 'selected' : '' ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="monthFilter" class="form-label">Month</label>
                                <select class="form-select" id="monthFilter" name="month">
                                    <option value="">All Months</option>
                                    <?php 
                                        $months = [
                                            1=>'January',2=>'February',3=>'March',4=>'April',
                                            5=>'May',6=>'June',7=>'July',8=>'August',
                                            9=>'September',10=>'October',11=>'November',12=>'December'
                                        ];
                                        foreach($months as $num=>$name): ?>
                                            <option value="<?= $num ?>" <?= (isset($_GET['month']) && $_GET['month'] == $num) ? 'selected' : '' ?>>
                                                <?= $name ?>
                                            </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select class="form-select" id="statusFilter" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Present" <?= (isset($_GET['status']) && $_GET['status'] == 'Present') ? 'selected' : '' ?>>Present</option>
                                    <option value="Absent" <?= (isset($_GET['status']) && $_GET['status'] == 'Absent') ? 'selected' : '' ?>>Absent</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="studentFilter" class="form-label">Student</label>
                                <input type="text" class="form-control" id="studentFilter" name="student" placeholder="Search by name" value="<?= isset($_GET['student']) ? htmlspecialchars($_GET['student']) : '' ?>">
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="?attendance_page=1" class="btn btn-secondary">
                                <i class="fas fa-redo me-1"></i> Reset Filters
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Attendance Table -->
            <div class="card">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Attendance Records</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Student ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student) { ?>
                                    <tr>
                                        <td><?php echo $student["student_id"] ?></td>
                                        <td><?php echo $student["first_name"] . " " . $student["last_name"] ?></td>
                                        <td><?php echo $student["date"] ?></td>
                                        <td>
                                            <?php if ($student["status"] === "Present") { ?>
                                                <span class="status-present text-success">Present</span>
                                            <?php } else { ?>
                                                <span class="status-absent text-danger">Absent</span>
                                            <?php } ?>
                                        </td>
                                        <td class="attendance-actions">
                                            <a href="edit_attendance.php?id=<?= htmlspecialchars($student["id"]) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit text-white"></i></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No attendance records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Attendance pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <!-- Previous button -->
                            <li class="page-item <?= ($attendancePage <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $queryString ?>&attendance_page=<?= max(1, $attendancePage - 1) ?>">Previous</a>
                            </li>

                            <!-- Page numbers -->
                            <?php
$adjacents = 2; // how many pages to show on each side of current
$start = max(1, $attendancePage - $adjacents);
$end = min($totalAttendancePages, $attendancePage + $adjacents);

if ($start > 1) {
    // First page
    echo '<li class="page-item"><a class="page-link" href="?' . $queryString . '&attendance_page=1">1</a></li>';
    if ($start > 2) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
}

for ($i = $start; $i <= $end; $i++) {
    $active = ($attendancePage == $i) ? 'active' : '';
    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?' . $queryString . '&attendance_page=' . $i . '">' . $i . '</a></li>';
}

if ($end < $totalAttendancePages) {
    if ($end < $totalAttendancePages - 1) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    // Last page
    echo '<li class="page-item"><a class="page-link" href="?' . $queryString . '&attendance_page=' . $totalAttendancePages . '">' . $totalAttendancePages . '</a></li>';
}
?>

                            <!-- Next button -->
                            <li class="page-item <?= ($attendancePage >= $totalAttendancePages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $queryString ?>&attendance_page=<?= min($totalAttendancePages, $attendancePage + 1) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Attendance Page -->
    <div id="studentAttendancePage" class="attendance-page">
        <div class="container-fluid p-4">
            <form action="../backend/save_attendance.php" method="POST" id="attendanceForm">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2 class="mb-0">
                            <i class="fas fa-arrow-left back-button me-2" onclick="navigateTo('attendanceCreationPage')"></i>
                            <span id="attendanceDateTitle">Attendance for 2024-01-15</span>
                        </h2>
                        <p class="text-muted" id="classNameTitle">Mathematics 101</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i> Save Attendance
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="navigateTo('attendanceCreationPage')">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </div>

                <!-- Hidden date input -->
                <input type="hidden" id="selectedDate" name="date" value="2024-01-15">

                <!-- Student List -->
                <div class="card">
                    <div class="card-header py-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col" width="50px">#</th>
                                        <th scope="col">Student ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 

                                        $sql = "SELECT * FROM students
                                                INNER JOIN student_classes 
                                                ON students.id = student_classes.student_id
                                                WHERE student_classes.class_id = :class_id
                                                ORDER BY students.last_name, students.first_name";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute([':class_id' => $_SESSION["class_id"]]);

                                        // Fetch all results
                                        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        $counter = 1;
                                        foreach ($students as $student){
                                           
                                             ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo $student['first_name'] . " " . $student['last_name']; ?></td>
                                            <td class="text-center">
                                                <input type="hidden" name="students[<?php echo $student['id']; ?>][id]" value="<?php echo $student['id']; ?>">

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" 
                                                        name="students[<?php echo $student['id']; ?>][status]" 
                                                        value="Present" checked>
                                                    <label class="form-check-label">Present</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" 
                                                        name="students[<?php echo $student['id']; ?>][status]" 
                                                        value="Absent">
                                                    <label class="form-check-label">Absent</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <!-- Continue for 1004, 1005 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Add Attendance Modal -->
    <div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-labelledby="addAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAttendanceModalLabel">Create New Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="attendanceForm">
                       
                        <div class="mb-3">
                            <label for="attendanceDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="attendanceDate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAttendanceBtn">Create Attendance</button>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>     
    
    <script>
        // Function to navigate between pages
        function navigateTo(pageId) {
            document.querySelectorAll('.attendance-page').forEach(page => {
                page.style.display = 'none';
            });
            document.getElementById(pageId).style.display = 'block';
        }
        
        // Set today's date as default in the modal
        window.onload = function() {
            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1;
            let dd = today.getDate();
            
            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;
            
            const formattedToday = `${yyyy}-${mm}-${dd}`;
            document.getElementById('attendanceDate').value = formattedToday;
        };
        
        // Handle save attendance button click
        document.getElementById('saveAttendanceBtn').addEventListener('click', function() {
            // const classSelect = document.getElementById('classSelect');
            const attendanceDate = document.getElementById('attendanceDate');
            
            // if (!classSelect.value) {
            //     alert('Please select a class');
            //     return;
            // }
            
            if (!attendanceDate.value) {
                alert('Please select a date');
                return;
            }
            
            // Set the title on the student attendance page
            document.getElementById('attendanceDateTitle').textContent = 
                `Attendance for ${attendanceDate.value}`;
            // document.getElementById('classNameTitle').textContent = classSelect.value;
             document.getElementById("selectedDate").value = attendanceDate.value;
            
            // Close the modal and navigate to student attendance page
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAttendanceModal'));
            modal.hide();
            navigateTo('studentAttendancePage');
        });

        document.getElementById("attendanceForm").addEventListener("submit", function (event) {
           event.preventDefault(); // stop immediate submission

            if (confirm("Do you want to save the grades?")) {
                // Optionally show a success message before submitting
                alert("Attendance saved successfully!");
                this.submit(); // proceed with form submission
            } else {
                if (confirm("Cancel without saving?")) {
                    navigateTo('attendanceCreationPage');
                }
                // else: stay on the page
            }
        });
       
        
        // Basic filter functionality (for demonstration)
        document.getElementById('applyFilters').addEventListener('click', function() {
            alert('Filters applied! (This is a demo. In a real application, this would filter the table data.)');
        });
        
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('dayFilter').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('studentFilter').value = '';
            alert('Filters reset!');
        });
    </script>

    <script src="../assets/js/main.js"></script>
</body>
</html>
