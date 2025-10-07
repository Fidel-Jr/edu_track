<?php
require "../db/connect.php";
require "../backend/helpers.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
    header("Location: ../welcome.php");
    exit;
}
require "../backend/dashboard_functions.php";

$grade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$grade_id) {
    die("Grade record not specified.");
}

// Fetch the grade record and student info
$stmt = $pdo->prepare("SELECT g.id AS grade_id, g.activity_type, g.activity_name, g.date AS grade_date, g.maximum_score AS max_score, g.score, g.student_class_id, s.id AS student_id, s.first_name, s.last_name, c.course_name     AS class_name
    FROM grades g
    JOIN student_classes sc ON g.student_class_id = sc.id
    JOIN students s ON sc.student_id = s.id
    JOIN class c ON sc.class_id = c.id
    WHERE g.id = :id");
$stmt->execute(['id' => $grade_id]);
$grade = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$grade) {
    die("Grade record not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_score = floatval($_POST['score']);
    $max_score = floatval($grade['max_score']);
    $percent = $max_score > 0 ? round((($new_score / $max_score) * 85) + 15, 0) : 0;
    // Determine letter grade
    if ($percent >= 81) {
        $letter = 'A';
        $cellClass = 'text-center text-success'; // green
    } elseif ($percent >= 75) {
        $letter = 'C';
        $cellClass = 'text-center text-warning'; // yellow
    } else {
        $letter = 'F';
        $cellClass = 'text-center text-danger'; // red
    }
    // Update score, percentage, and letter grade (assumes columns exist)
    $update = $pdo->prepare("UPDATE grades SET score = :score, percentage = :percentage, grade = :letter_grade WHERE id = :id");
    $update->execute([
        ':score' => $new_score,
        ':percentage' => $percent,
        ':letter_grade' => $letter,
        ':id' => $grade_id
    ]);
    header("Location: grades.php?success=1");
    exit;
}
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
                    <form id="editGradeForm" method="POST">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h2 class="mb-2">
                                    <!-- <a href="grades.php" class="btn btn-link px-0" style="font-size: 1.5rem;">
                                        <i class="fas fa-arrow-left text-dark back-button me-2"></i>
                                    </a> -->
                                    Edit Grade for <?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?>
                                </h2>
                                <p class="text-muted">Student ID: <?= htmlspecialchars($grade['student_id']) ?> | Class: <?= htmlspecialchars($grade['class_name']) ?></p>
                                <p class="text-muted">Activity: <?= htmlspecialchars($grade['activity_name']) ?> (<?= htmlspecialchars($grade['activity_type']) ?>) | Date: <?= htmlspecialchars($grade['grade_date']) ?> | Max Score: <?= htmlspecialchars($grade['max_score']) ?></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bi bi-save me-1"></i> Save Grade
                                </button>
                                <a href="grades.php" class="btn btn-secondary">
                                    <i class="bi bi-x me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header py-3 text-white" style="background-color: var(--primary-color);">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Edit Student Grade</h5>
                            </div>
                            <div class="card-body">
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
                                            <tr>
                                                <td>1</td>
                                                <td><?= htmlspecialchars($grade['student_id']) ?></td>
                                                <td><?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?></td>
                                                <td class="text-center">
                                                    <input type="number" id="scoreInput" name="score" class="form-control form-control-sm" min="0" max="<?= htmlspecialchars($grade['max_score']) ?>" value="<?= htmlspecialchars($grade['score']) ?>" required>
                                                    <input type="hidden" id="maxScoreHidden" value="<?= htmlspecialchars($grade['max_score']) ?>">
                                                </td>
                                               <?php
                                                    $percent = $grade['max_score'] > 0 
                                                        ? round((($grade['score'] / $grade['max_score']) * 85) + 15, 0) 
                                                        : 0;

                                                    // Percent cell color
                                                   list($letter, $colorClass) = getGradeAndColor($percent);
                                                ?>
                                                <td class="<?= $colorClass ?> text-center" id="percentCell">
                                                    <?= $percent . '%' ?>
                                                </td>
                                                <!-- Letter grade logic -->
                                                <td class="<?= $colorClass ?> text-center" id="gradeCell">
                                                    <?= $letter ?>
                                                </td>

                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
    
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scoreInput = document.getElementById('scoreInput');
            const maxScoreInput = document.getElementById('maxScoreHidden');
            const percentCell = document.getElementById('percentCell');
            const gradeCell = document.getElementById('gradeCell');
            function updatePercentAndGrade() {
                const maxScore = parseFloat(maxScoreInput.value) || 0;
                let score = parseFloat(scoreInput.value) || 0;
                if (score > maxScore) {
                    alert('Score cannot be greater than the maximum score (' + maxScore + ').');
                    score = maxScore;
                    scoreInput.value = maxScore;
                }
                let percent = maxScore > 0 ? ((score / maxScore) * 85) + 15 : 0;
                percentCell.textContent = percent.toFixed(0) + '%';
                let letter = 'F';
                let cellClass = 'text-center text-danger'; // F = red
                if (percent >= 81) {
                    letter = 'A'; 
                    cellClass = 'text-center text-success'; // green
                } else if (percent > 74) {
                    letter = 'C'; 
                    cellClass = 'text-center text-warning'; // yellow
                } else {
                    letter = 'F'; 
                    cellClass = 'text-center text-danger'; // red
                }
                gradeCell.textContent = letter;
                gradeCell.className = cellClass;
                percentCell.className = cellClass;
            }
            if (scoreInput) {
                scoreInput.addEventListener('input', updatePercentAndGrade);
            }
            document.getElementById("editGradeForm").addEventListener("submit", function (event) {
                event.preventDefault(); // stop immediate submission

                    if (confirm("Do you want to save the grades?")) {
                        // Optionally show a success message before submitting
                        alert("Save saved successfully!");
                        this.submit(); // proceed with form submission
                    } else {
                        if (confirm("Cancel without saving?")) {
                            navigateTo('GradesPage');
                        }
                        // else: stay on the page
                    }
                });
        });
    </script>
    <style>
    /* Custom color for D grade (orange) */
    .text-orange { color: #fd7e14 !important; }
    </style>
</body>
</html>