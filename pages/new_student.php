<?php 
    require "../db/connect.php"; // your PDO connection
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }

    $classId = $_SESSION["class_id"];
$searchResult = null;
$message = "";

// Handle Search
if (isset($_GET['search_id'])) {
    $studentId = trim($_GET['search_id']);
    
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $searchResult = $stmt->fetch(PDO::FETCH_ASSOC);

    // If found, check if already in class
    if ($searchResult) {
        $stmtCheck = $pdo->prepare("SELECT * FROM student_classes WHERE student_id = ? AND class_id = ?");
        $stmtCheck->execute([$searchResult['id'], $classId]);
        $alreadyInClass = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $searchResult['already_in_class'] = $alreadyInClass ? true : false;
    }
}

// Handle Add
if (isset($_POST['add_student_id'])) {
    $studentId = $_POST['add_student_id'];

    // Get student from students table
    $stmt = $pdo->prepare("SELECT id FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // Check if already in class
        $stmtCheck = $pdo->prepare("SELECT * FROM student_classes WHERE student_id = ? AND class_id = ?");
        $stmtCheck->execute([$student['id'], $classId]);
        $already = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($already) {
            $message = "<div class='alert alert-warning'>Student is already in this class.</div>";
            $searchResult['already_in_class'] = true;
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)");
            $stmtInsert->execute([$studentId, $classId]);
            $message = "<div class='alert alert-success'>Student successfully added to class!</div>";

            // ✅ Force update
            $searchResult['already_in_class'] = true;
        }
    } else {
        $message = "<div class='alert alert-danger'>Student not found.</div>";
    }
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
    
    <style>
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        .search-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
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
                    <h2 class="mb-4 fw-bold">Add Student</h2>
                    <?= $message ?>

                    <!-- Search Card -->
                    <div class="card shadow-sm mb-5">
                        <div class="card-body">
                            <form method="get" class="d-flex">
                                <input 
                                    type="text" 
                                    name="search_id" 
                                    class="form-control" 
                                    placeholder="Enter Student ID" 
                                    value="<?= isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : '' ?>" 
                                >
                                <button type="submit" class="search-btn btn btn-success px-4">Search</button>
                            </form>
                        </div>
                    </div>

                    <?php if ($searchResult): ?>
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white fw-bold">Search Results</div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= htmlspecialchars($searchResult['id']) ?></td>
                                            <td><?= htmlspecialchars($searchResult['first_name']) ?></td>
                                            <td><?= htmlspecialchars($searchResult['last_name']) ?></td>
                                            <td>
                                                <?php if ($searchResult['already_in_class']): ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Already Enrolled</button>
                                                <?php else: ?>
                                                    <form action="new_student.php?search_id=<?= $searchResult['id']; ?>" method="post" class="d-inline">
                                                        <input type="hidden" name="add_student_id" value="<?= htmlspecialchars($searchResult['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-person-plus"></i> Add
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php elseif (isset($_GET['search_id'])): ?>
                        <div class="alert alert-danger">No student found with that ID.</div>
                    <?php endif; ?>
                </div>


        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
    
    <script src="../assets/js/main.js"></script>
</body>
</html>