<?php
require "../db/connect.php";

// Get attendance record by attendance id from URL
$attendance_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$attendance_id) {
  die("Attendance record not specified.");
}

// Fetch attendance record and student info
$stmt = $pdo->prepare("SELECT a.id AS attendance_id, a.date, a.status, s.id AS student_id, s.first_name, s.last_name
  FROM attendance a
  JOIN student_classes sc ON a.student_class_id = sc.id
  JOIN students s ON sc.student_id = s.id
  WHERE a.id = :id");
$stmt->execute(['id' => $attendance_id]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$attendance) {
  die("Attendance record not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_date = $_POST['date'];
  $new_status = $_POST['status'];
  $update = $pdo->prepare("UPDATE attendance SET date = :date, status = :status WHERE id = :id");
  $update->execute([
    ':date' => $new_date,
    ':status' => $new_status,
    ':id' => $attendance_id
  ]);
  header("Location: attendance.php?success=1");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Attendance</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2 class="mb-0">
                           <!-- <i class="fas fa-arrow-left back-button me-2" onclick="navigateTo('attendanceCreationPage')"></i> -->
                            <span id="attendanceDateTitle">Edit Attendance for <?= $attendance["date"] ?></span>
                        </h2>
                        <p class="text-muted">Student ID: <?= htmlspecialchars($attendance['student_id']) ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <form id="attendanceForm" method="POST" class="d-inline">
                            <button type="submit" class="btn btn-success me-2">
                                <i class="bi bi-save me-1"></i> Save Attendance
                            </button>
                            <a href="attendance.php" class="btn btn-secondary">
                                <i class="bi bi-x me-1"></i> Cancel
                            </a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header py-3 text-white" style="background-color: var(--primary-color);">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Edit Student Attendance</h5>
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
                                    <tr>
                                        <td>1</td>
                                        <td><?= htmlspecialchars($attendance['student_id']) ?></td>
                                        <td><?= htmlspecialchars($attendance['first_name'] . ' ' . $attendance['last_name']) ?></td>
                                        <td class="text-center">
                                            <input type="hidden" name="date" value="<?= htmlspecialchars($attendance['date']) ?>">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status" id="present" value="Present" <?= $attendance['status'] === 'Present' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="present">Present</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status" id="absent" value="Absent" <?= $attendance['status'] === 'Absent' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="absent">Absent</label>
                                            </div>
                                           
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
  </div>

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
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
    </script>
</body>
</html>
