<?php 
    require "../db/connect.php"; // your PDO connection
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }
    
    require "../backend/functions.php";

    $classId = $_SESSION["class_id"];
    $attendanceRate = getClassAttendanceRate($pdo, $classId);
    $averageGrade = getClassAverageGrade($pdo, $classId);
    $totalStudents = getTotalStudentsInClass($pdo, $classId);
    $totalActivities = getTotalActivitiesInClass($pdo, $classId);

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
                    <h2 class="mb-4">Dashboard</h2>
                    
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card dashboard-card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">ATTENDANCE RATE</h6>
                                            <h3 class="card-text"><?php echo $attendanceRate ?>%</h3>
                                        </div>
                                        <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card dashboard-card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">AVERAGE GRADE</h6>
                                            <h3 class="card-text"><?php echo $averageGrade ?>%</h3>
                                        </div>
                                        <i class="bi bi-journal-check fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card dashboard-card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Students</h6>
                                            <h3 class="card-text"><?php echo $totalStudents ?></h3>
                                        </div>
                                        <i class="bi bi-people fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card dashboard-card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Total Number of Activities</h6>
                                            <h3 class="card-text"><?php echo $totalActivities; ?></h3>
                                        </div>
                                        <i class="bi bi-activity fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include '../include/grades_attendance_summary.php'; ?>
                    
                </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
    
    <script src="../assets/js/main.js"></script>
</body>
</html>