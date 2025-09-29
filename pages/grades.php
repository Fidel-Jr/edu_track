<?php 

    include '../db/connect.php';
    session_start();
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }
    $activity = isset($_GET['activity']) ? $_GET['activity'] : '';
    $gradeRange = isset($_GET['grade_range']) ? $_GET['grade_range'] : '';
    $student = isset($_GET['student']) ? $_GET['student'] : '';
    $whereClauses = ["student_classes.class_id = :class_id"];
    $params = [":class_id" => $_SESSION["class_id"]];

    // Filter by activity type
    if (!empty($activity)) {
        $whereClauses[] = "grades.activity_type = :activity";
        $params[':activity'] = $activity;
    }

    // Filter by grade range
    if (!empty($gradeRange)) {
        [$min, $max] = explode('-', $gradeRange);
        $whereClauses[] = "grades.percentage BETWEEN :minGrade AND :maxGrade";
        $params[':minGrade'] = (int)$min;
        $params[':maxGrade'] = (int)$max;
    }

    // Filter by student name
    if (!empty($student)) {
        $whereClauses[] = "(students.first_name LIKE :student OR students.last_name LIKE :student)";
        $params[':student'] = "%" . $student . "%";
    }

    $whereSQL = implode(" AND ", $whereClauses);

        // Fetch class name from database
        $stmt = $pdo->prepare("SELECT course_name FROM class WHERE id = :class_id");
        $stmt->execute([':class_id' => $_SESSION["class_id"]]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);
        $className = $class["course_name"];

    // Pagination setup
    $gradesPage = isset($_GET['grades_page']) && is_numeric($_GET['grades_page']) ? (int) $_GET['grades_page'] : 1;
    $recordsPerPage = 10;
    $offset = ($gradesPage - 1) * $recordsPerPage;

    // Count total grades
    $countSql = "SELECT COUNT(*)
        FROM grades
        INNER JOIN student_classes ON grades.student_class_id = student_classes.id
        INNER JOIN students ON student_classes.student_id = students.id
        WHERE $whereSQL";

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalGradeRows = $countStmt->fetchColumn();
    $totalGradesPages = ceil($totalGradeRows / $recordsPerPage);

    $sql = "SELECT 
            students.id AS student_id, 
            students.first_name, 
            students.last_name, 
            grades.*
            FROM grades
            INNER JOIN student_classes ON grades.student_class_id = student_classes.id
            INNER JOIN students ON student_classes.student_id = students.id
            WHERE $whereSQL
            ORDER BY grades.date DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    // bind normal params
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $gradesQueryString = http_build_query(array_merge($_GET, ['grades_page' => null]));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management System</title>
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
            overflow: hidden;
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
        
        .grade-high {
            color: #28a745;
            font-weight: 600;
        }
        
        .grade-medium {
            color: #ffc107;
            font-weight: 600;
        }
        
        .grade-low {
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
        
        .grade-actions {
            display: flex;
            gap: 10px;
        }
        
        .student-list-item {
            transition: all 0.3s ease;
        }
        
        .student-list-item:hover {
            background-color: #f0f5ff;
        }
        
        .grade-page {
            display: none;
        }
        
        #gradeManagementPage {
            display: block;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .back-button {
            cursor: pointer;
        }
        
        .activity-badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        
        .class-header {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                

        <!-- Grade Management Page -->
                <div id="gradeManagementPage" class="grade-page">
                    <div class="container-fluid p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h2 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Grade Management</h2>
                                <p class="text-muted"><?php echo $className ?></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                                    <i class="fas fa-plus me-1"></i> New Grade Entry
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
                                        <!-- Activity Type -->
                                        <div class="col-md-4 mb-3">
                                            <label for="activityFilter" class="form-label">Activity Type</label>
                                            <select class="form-select" id="activityFilter" name="activity">
                                                <option value="">All Activities</option>
                                                <option value="Exam" <?= (isset($_GET['activity']) && $_GET['activity'] == 'Exam') ? 'selected' : '' ?>>Exam</option>
                                                <option value="Quiz" <?= (isset($_GET['activity']) && $_GET['activity'] == 'Quiz') ? 'selected' : '' ?>>Quiz</option>
                                                <option value="Assignment" <?= (isset($_GET['activity']) && $_GET['activity'] == 'Assignment') ? 'selected' : '' ?>>Assignment</option>
                                                <option value="Project" <?= (isset($_GET['activity']) && $_GET['activity'] == 'Project') ? 'selected' : '' ?>>Project</option>
                                            </select>
                                        </div>

                                        <!-- Grade Range -->
                                        <div class="col-md-4 mb-3">
                                            <label for="gradeFilter" class="form-label">Grade Range</label>
                                            <select class="form-select" id="gradeFilter" name="grade_range">
                                                <option value="">All Grades</option>
                                                <option value="90-100" <?= (isset($_GET['grade_range']) && $_GET['grade_range'] == '90-100') ? 'selected' : '' ?>>90-100 (A)</option>
                                                <option value="80-89" <?= (isset($_GET['grade_range']) && $_GET['grade_range'] == '80-89') ? 'selected' : '' ?>>80-89 (B)</option>
                                                <option value="70-79" <?= (isset($_GET['grade_range']) && $_GET['grade_range'] == '70-79') ? 'selected' : '' ?>>70-79 (C)</option>
                                                <option value="60-69" <?= (isset($_GET['grade_range']) && $_GET['grade_range'] == '60-69') ? 'selected' : '' ?>>60-69 (D)</option>
                                                <option value="0-59" <?= (isset($_GET['grade_range']) && $_GET['grade_range'] == '0-59') ? 'selected' : '' ?>>0-59 (F)</option>
                                            </select>
                                        </div>

                                        <!-- Student -->
                                        <div class="col-md-4 mb-3">
                                            <label for="studentFilter" class="form-label">Student</label>
                                            <input type="text" 
                                                class="form-control" 
                                                id="studentFilter" 
                                                name="student"
                                                placeholder="Search by name"
                                                value="<?= isset($_GET['student']) ? htmlspecialchars($_GET['student']) : '' ?>">
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <a href="?grades_page=1" class="btn btn-secondary">
                                            <i class="fas fa-redo me-1"></i> Reset Filters
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-1"></i> Apply Filters
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Grade Table -->
                        <div class="card">
                            <div class="card-header py-3">
                                <?php 
                                    
                                ?>
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Grade Records - <?php echo $className ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">Student</th>
                                                <th scope="col">Latest Activity</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Score</th>
                                                <th scope="col">Percentage</th>
                                                <th scope="col">Grade</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                      <tbody>
                                            <?php if (!empty($students)): ?>
                                                <?php foreach ($students as $student): ?>
                                                    <tr>
                                                        <!-- Student column -->
                                                        <td class="align-middle">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-semibold">
                                                                    <?php echo $student["first_name"] . " " . $student["last_name"] ?>
                                                                </span>
                                                                <small class="text-muted"><?php echo $student["student_id"] ?></small>
                                                            </div>
                                                        </td>

                                                        <!-- Latest Activity -->
                                                        <td class="align-middle">
                                                            <span class="badge bg-info me-1"><?php echo $student["activity_type"] ?></span>
                                                            <?php echo $student["activity_name"] ?>
                                                        </td>

                                                        <!-- Date -->
                                                        <td class="align-middle"><?php echo $student["date"] ?></td>

                                                        <!-- Score -->
                                                        <td class="align-middle">
                                                            <?php echo $student["score"] . "/" . $student["maximum_score"] ?>
                                                        </td>

                                                        <!-- Percentage -->
                                                        <?php
                                                            $percentage = $student["percentage"];
                                                            if ($percentage >= 80) {
                                                                $colorClass = "text-success"; // green
                                                            } elseif ($percentage >= 70) {
                                                                $colorClass = "text-warning"; // orange
                                                            } else {
                                                                $colorClass = "text-danger"; // red
                                                            }
                                                        ?>
                                                        <td class="align-middle <?php echo $colorClass; ?>">
                                                            <?php echo $percentage . "%" ?>
                                                        </td>

                                                        <!-- Grade -->
                                                        <td class="align-middle <?php echo $colorClass; ?>">
                                                            <?php echo $student["grade"] ?>
                                                        </td>

                                                        <!-- Actions -->
                                                        <td class="align-middle text-center">
                                                            <div class="btn-group" role="group">
                                                                <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No grade records found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <nav aria-label="Grades pagination">
                                    <ul class="pagination justify-content-center mt-4">
                                        <!-- Previous button -->
                                        <li class="page-item <?= ($gradesPage <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= $gradesQueryString ?>&grades_page=<?= max(1, $gradesPage - 1) ?>">Previous</a>
                                        </li>

                                        <!-- Page numbers -->
                                        <?php for ($i = 1; $i <= $totalGradesPages; $i++): ?>
                                            <li class="page-item <?= ($gradesPage == $i) ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= $gradesQueryString ?>&grades_page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next button -->
                                        <li class="page-item <?= ($gradesPage >= $totalGradesPages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= $gradesQueryString ?>&grades_page=<?= min($totalGradesPages, $gradesPage + 1) ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Grade Entry Page -->
                <div id="studentGradePage" class="grade-page">
                    <div class="container-fluid p-4">
                        <form id="gradesForm" action="../backend/save_grade.php" method="POST">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h2 class="mb-0">
                                        <i class="fas fa-arrow-left back-button me-2" onclick="navigateTo('gradeManagementPage')"></i>
                                        <span id="gradeActivityTitle">Chapter 3 Quiz</span>
                                    </h2>
                                    <p class="text-muted"><?php echo $className ?> - Max Score: <span id="maxScoreValue">20</span></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-success me-2">
                                        <i class="fas fa-save me-1"></i> Save Grades
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="navigateTo('gradeManagementPage')">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Student List -->
                            <div class="card">
                                <div class="card-header py-3">
                                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Grades - <?php echo $className ?></h5>
                                </div>
                                <div class="card-body"> 
                                    <div class="mb-3">
                                        <select class="form-select" name="activity_type" id="activityTypeHidden" required hidden>
                                            <option value="" selected disabled>Select activity type</option>
                                            <option value="Exam">Exam</option>
                                            <option value="Quiz">Quiz</option>
                                            <option value="Assignment">Assignment</option>
                                            <option value="Project">Project</option>
                                            <option value="Lab Work">Lab Work</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" name="activity_name" class="form-control" id="activityNameHidden" placeholder="e.g., Midterm Exam, Chapter 3 Quiz" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" name="grade_date" class="form-control" id="gradeDateHidden" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" name="max_score" class="form-control" id="maxScoreHidden" min="1" value="100" required>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col" width="50px">#</th>
                                                    <th scope="col">Student ID</th>
                                                    <th scope="col">Name</th>
                                                    <th scope="col" class="text-center">Score</th>
                                                    <th scope="col" class="text-center">Percentage</th>
                                                    <th scope="col" class="text-center">Grade</th>
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
                                                    $counter = 0;
                                                    foreach($students as $student){
                                                    ?>
                                                        <input type="hidden" name="student_class_id[]" value="<?php echo $student["id"] ?>">
                                                        <tr class="student-list-item">
                                                            <td><?php echo $counter = $counter + 1 ?></td>
                                                            <td><?php echo $student["student_id"] ?></td>
                                                            <td><?php echo $student["first_name"] . " " . $student["last_name"] ?></td>
                                                            <td class="text-center">
                                                                <input type="number" name="score[]" class="form-control form-control-sm" min="0" max="100" value="0">
                                                            </td>
                                                            <td class="text-center grade-high text-danger">15%</td>
                                                            <td class="text-center grade-high text-danger">F</td>
                                                        </tr>
                                                    <?php 
                                                    }?>
                                                
                                            
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Grade Modal -->
                <div class="modal fade" id="addGradeModal" tabindex="-1" aria-labelledby="addGradeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addGradeModalLabel">Create New Grade Entry</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="gradeForm">
                                    <div class="mb-3">
                                        <label class="form-label">Class</label>
                                        <input type="text" class="form-control" value="<?php echo $className ?>" disabled>
                                        <small class="text-muted">You're currently managing <?php echo $className ?></small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="activityType" class="form-label">Activity Type</label>
                                        <select class="form-select" id="activityType" required>
                                            <option value="" selected disabled>Select activity type</option>
                                            <option value="Exam">Exam</option>
                                            <option value="Quiz">Quiz</option>
                                            <option value="Assignment">Assignment</option>
                                            <option value="Project">Project</option>
                                            <option value="Lab Work">Lab Work</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="activityName" class="form-label">Activity Name</label>
                                        <input type="text" class="form-control" id="activityName" placeholder="e.g., Midterm Exam, Chapter 3 Quiz" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="gradeDate" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="gradeDate" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="maxScore" class="form-label">Maximum Score</label>
                                        <input type="number" class="form-control" id="maxScore" min="1" value="100" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveGradeBtn">Create Grade Entry</button>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
            

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> 
    <script>
        // Function to navigate between pages
        function navigateTo(pageId) {
            document.querySelectorAll('.grade-page').forEach(page => {
                page.style.display = 'none';
            });
            document.getElementById(pageId).style.display = 'block';
            
            // Scroll to top when navigating
            window.scrollTo(0, 0);
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
            document.getElementById('gradeDate').value = formattedToday;
            
        };
        
        
        // Handle save grade button click
        document.getElementById('saveGradeBtn').addEventListener('click', function() {
            const activityType = document.getElementById('activityType');
            const activityName = document.getElementById('activityName');
            const gradeDate = document.getElementById('gradeDate');
            const maxScore = document.getElementById('maxScore');

            document.getElementById('activityTypeHidden').value = activityType.value;
            document.getElementById('activityNameHidden').value = activityName.value;
            document.getElementById('gradeDateHidden').value = gradeDate.value;
            document.getElementById('maxScoreHidden').value = maxScore.value;
            
            if (!activityType.value) {
                alert('Please select an activity type');
                return;
            }
            
            if (!activityName.value) {
                alert('Please enter an activity name');
                return;
            }
            
            if (!gradeDate.value) {
                alert('Please select a date');
                return;
            }
            
            if (!maxScore.value || maxScore.value < 1) {
                alert('Please enter a valid maximum score');
                return;
            }
            
            // Set the title on the student grade page
            document.getElementById('gradeActivityTitle').textContent = activityName.value;
            document.getElementById('maxScoreValue').textContent = maxScore.value;
            
            // Close the modal and navigate to student grade page
            const modal = bootstrap.Modal.getInstance(document.getElementById('addGradeModal'));
            modal.hide();
            navigateTo('studentGradePage');
        });
        
        // Basic filter functionality (for demonstration)
        document.getElementById('applyFilters').addEventListener('click', function() {
            alert('Filters applied! (This is a demo. In a real application, this would filter the table data.)');
        });
        
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('activityFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            document.getElementById('studentFilter').value = '';
            alert('Filters reset!');
        });
        
        // Save grades function
        document.getElementById("gradesForm").addEventListener("submit", function (event) {
            event.preventDefault(); // stop immediate submission

            if (confirm("Do you want to save the grades?")) {
                // Optionally show a success message before submitting
                alert("Grades saved successfully!");
                this.submit(); // proceed with form submission
            } else {
                if (confirm("Cancel without saving?")) {
                    navigateTo('gradeManagementPage');
                }
                // else: stay on the page
            }
        });
        
        // Auto-calculate percentage and grade when score changes
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                const maxScore = document.getElementById('maxScore').value; // This would come from the database in a real app
                const score = parseFloat(this.value);
                const percentage = ((score / maxScore) * 85) + 15;
                
                const row = this.closest('tr');
                const percentageCell = row.querySelector('td:nth-child(5)');
                const gradeCell = row.querySelector('td:nth-child(6)');
                
                percentageCell.textContent = `${percentage.toFixed(0)}%`;
                
                // Set appropriate class based on percentage
                if (percentage >= 90) {
                    percentageCell.className = 'text-center grade-high';
                    gradeCell.textContent = 'A';
                    gradeCell.className = 'text-center grade-high';
                } else if (percentage >= 80) {
                    percentageCell.className = 'text-center grade-high';
                    gradeCell.textContent = 'B';
                    gradeCell.className = 'text-center grade-high';
                } else if (percentage >= 70) {
                    percentageCell.className = 'text-center grade-medium';
                    gradeCell.textContent = 'C';
                    gradeCell.className = 'text-center grade-medium';
                } else if (percentage >= 60) {
                    percentageCell.className = 'text-center grade-low';
                    gradeCell.textContent = 'D';
                    gradeCell.className = 'text-center grade-low';
                } else {
                    percentageCell.className = 'text-center grade-low';
                    gradeCell.textContent = 'F';
                    gradeCell.className = 'text-center grade-low';
                }
            });
        });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>