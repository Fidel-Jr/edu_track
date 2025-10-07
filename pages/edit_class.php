<?php
require "../db/connect.php";
session_start();
    unset($_SESSION['class_id']);
    unset($_SESSION['course_code']);

if (!isset($_SESSION["user_info"]["username"]) && !isset($_SESSION["user_id"])) {
    header("Location: ../welcome.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../welcome.php");
    exit;
}

$classId = $_GET['id'];

// Fetch class info
$stmt = $pdo->prepare("SELECT * FROM class WHERE id = ?");
$stmt->execute([$classId]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    header("Location: ../welcome.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $course_title = trim($_POST['course_title']);
    $course_code = trim($_POST['course_code']);
    $room = trim($_POST['room']);
    $time_from = $_POST['time_from'];
    $time_to = $_POST['time_to'];

    $update = $pdo->prepare("UPDATE class SET course_name=?, course_title=?, course_code=?, room=?, time_from=?, time_to=? WHERE id=?");
    $update->execute([$course_name, $course_title, $course_code, $room, $time_from, $time_to, $classId]);
    header("Location: ../welcome.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class</title>
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
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <?php include 'navbar.php'; ?>
            <div class="container-fluid p-5">
                <h2 class="mb-4">Edit Class</h2>
                <div class="container-fluid bg-white p-4 rounded shadow-sm">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="course_name" placeholder="Course Name" value="<?= htmlspecialchars($class['course_name']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Title</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="course_title" placeholder="Course Title" value="<?= htmlspecialchars($class['course_title']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="course_code" placeholder="Course Code" value="<?= htmlspecialchars($class['course_code']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="room" placeholder="Room" value="<?= htmlspecialchars($class['room']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time From</label>
                            <div class="input-group">
                                <input type="time" class="form-control" name="time_from" value="<?= htmlspecialchars($class['time_from']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time To</label>
                            <div class="input-group">
                                <input type="time" class="form-control" name="time_to" value="<?= htmlspecialchars($class['time_to']) ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="../welcome.php" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>