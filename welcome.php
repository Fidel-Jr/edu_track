<?php
    include './db/connect.php';
    session_start();
    if (!isset($_SESSION["user_info"]["username"]) && !isset($_SESSION["user_id"])) {
        header("Location: index.php");
        exit;
    }

    // Handle class deletion
    if (isset($_POST['delete_class_id'])) {
        $classId = $_POST['delete_class_id'];

        // Delete related attendance and grades
        $stmt = $pdo->prepare("SELECT id FROM student_classes WHERE class_id = ?");
        $stmt->execute([$classId]);
        $studentClassIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($studentClassIds) {
            $in = str_repeat('?,', count($studentClassIds) - 1) . '?';
            $pdo->prepare("DELETE FROM attendance WHERE student_class_id IN ($in)")->execute($studentClassIds);
            $pdo->prepare("DELETE FROM grades WHERE student_class_id IN ($in)")->execute($studentClassIds);
            $pdo->prepare("DELETE FROM student_classes WHERE id IN ($in)")->execute($studentClassIds);
        }

        // Delete the class itself
        $pdo->prepare("DELETE FROM class WHERE id = ?")->execute([$classId]);

        header("Location: welcome.php");
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
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .container-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header-section {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header-section h1 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
        }
        
        .class-list {
            padding: 30px;
        }
        
        .list-group-item {
            border: none;
            border-left: 4px solid transparent;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            padding: 20px;
            background: var(--sidebar-bg);
        }
        
        .list-group-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .subject-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: white;
            background: var(--primary-color);
        }
        
        .btn-enter {
            min-width: 120px;
            border-radius: 30px;
            background: var(--primary-color);
            border: none;
            font-weight: 600;
            padding: 8px 20px;
            color: #fff;
        }
        
        .btn-enter:hover {
            background-color: #3d8b40;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
            color: #fff;
        }
        
        .class-details h5 {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .class-details p {
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .class-details small {
            color: #95a5a6;
        }
        
        .support-section {
            background: var(--secondary-color);
            border-radius: 10px;
            padding: 15px;
            margin-top: 30px;
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .class-details {
                text-align: center;
                margin-bottom: 15px;
            }
            
            .d-flex {
                flex-direction: column;
            }
            
            .subject-icon {
                margin: 0 auto 15px;
            }
            
            .class-action {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark py-3" style="background-color: #4caf50;">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#">
                    <i class="bi bi-journal-bookmark-fill me-2"></i>EduTrack
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-start" id="mainNavbar">
                    <div class="d-flex w-100 justify-content-end">
                        <div class="me-3">
                            <a href="./pages/profile.php" class="text-white text-decoration-none d-flex align-items-center">
                                <span class="link-text">Profile</span>
                            </a>
                        </div>
                        <div>
                            <a href="./backend/logout.php" class="text-white text-decoration-none d-flex align-items-center">
                                <span class="link-text">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <main class="my-5">
            <div class="container-wrapper">
                <div class="header-section">
                    <h1>Welcome, <?php echo $_SESSION["user_info"]["username"] ?> <span class="ms-2">👋</span></h1>
                    <p class="lead mb-0">Please select a class to continue</p>
                </div>
                
                <div class="class-list">
                    <div class="d-flex justify-content-end">
                        <a href="./pages/new_class.php" class="btn btn-success">New Class <i class="bi bi-plus"></i> </a>
                    </div>
                    
                    <div class="list-group mt-3">
                        <!-- Math Class -->
                        <?php 

                            $sql = "SELECT class.*, teachers.last_name AS teacher_name 
                                    FROM class
                                    JOIN teachers ON class.teacher_id = teachers.id 
                                    WHERE class.teacher_id = :teacher_id";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([':teacher_id' => $_SESSION["user_id"]]);
                            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($classes as $class) {
                        
                        ?>
                        <div class="list-group-item math-class">
                            <div class="d-flex align-items-center">
                                <div class="subject-icon math-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="class-details flex-grow-1">
                                    <h5 class="mb-1"><?= $class["course_name"] ?> <span class="h6 text-secondary" style="opacity: 80%;"><?= $class["course_title"] ?></span> </h5>
                                    <p class="mb-1 text-muted">Course Code: <?= $class["course_code"] ?></p>
                                    <small class="text-muted">Time: <?= date("H:i", strtotime($class["time_from"])) . " - " . date("H:i", strtotime($class["time_to"])); ?> | Room: <?= $class["room"] ?></small>
                                </div>
                                <div class="class-action">
                                    <form action="./backend/enter_class.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="class_id" value="<?= $class['id']; ?>">
                                        <button type="submit" class="btn btn-enter btn-sm">Enter Class</button>
                                    </form>
                                    <div class="mx-2 mt-2">
                                        <a href="pages/edit_class.php?id=<?= $class['id'] ?>" class="text-warning text-decoration-none">Edit</a>
                                        <span class="text-light">|</span>
                                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this class and all related records?');">
                                            <input type="hidden" name="delete_class_id" value="<?= $class['id'] ?>">
                                            <button type="submit" class="btn btn-link text-danger text-decoration-none p-0 m-0" style="vertical-align: baseline;">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        
                    </div>
                    
                    <!-- <div class="mt-4 text-center">
                        <p class="text-muted">Need help? Contact academic support: support@schoolportal.edu | (555) 123-4567</p>
                    </div> -->
                </div>
            </div>
        </main>
    </header>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="./assets/js/main.js"></script>
</body>
</html>

