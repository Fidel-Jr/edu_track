<?php
require "../db/connect.php";
session_start();

if (!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) {
    header("Location: ../welcome.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: students.php");
    exit;
}

$studentId = $_GET['id'];

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id' => $studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: students.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    $update = $pdo->prepare("UPDATE students SET first_name = :first_name, last_name = :last_name, address = :address, phone = :phone WHERE id = :id");
    $update->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':address' => $address,
        ':phone' => $phone,
        ':id' => $studentId
    ]);
    header("Location: students.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
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
                <h2 class="mb-4">Edit Student</h2>
                <div class="container-fluid bg-white p-4 rounded shadow-sm">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($student['id']) ?>" disabled>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="address" placeholder="Address" value="<?= htmlspecialchars($student['address']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="phone" placeholder="Phone" value="<?= htmlspecialchars($student['phone']) ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="students.php" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="../assets/js/main.js"></script>

</body>
</html>