<?php

use FontLib\Table\Type\head;

    include '../db/connect.php'; // adjust path to your PDO/MySQLi connection
    session_start();
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"]))) {
        header("Location: ../welcome.php");
        exit;
    }
    if(isset($_SESSION["class_id"])) {
        unset($_SESSION["class_id"]);
    }
    function generateUniqueCourseCode($pdo) {
        do {
            $code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // e.g. 0042, 8591
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM class WHERE course_code = ?");
            $stmt->execute([$code]);
            $exists = $stmt->fetchColumn();
        } while ($exists > 0); 
        
        return $code;
    }

    if (!isset($_SESSION['course_code'])) {
        $_SESSION['course_code'] = generateUniqueCourseCode($pdo);
    }

    // now you can use this everywhere
    $courseCode = $_SESSION['course_code'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
                <h2 class="mb-4">New Class</h2>
                <div class="container-fluid bg-white p-4 rounded shadow-sm">
                    <form action="../backend/new_class.php" method="POST">
                        <div class="mb-3">  
                            <label for="First Name" class="form-label">Course Title</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="course_title" placeholder="Course Title" value="">
                            </div>
                        </div>

                        <div class="mb-3">  
                            <label for="Last Name" class="form-label">Course Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="course_code" placeholder="Course Code" value="<?php echo $courseCode; ?>">
                            </div>
                        </div>

                        <div class="mb-3">  
                            <label for="Username" class="form-label">Course Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="course_name" placeholder="Course Name" value="">
                            </div>
                        </div>
                        
                        <div class="mb-3">  
                            <label for="Password" class="form-label">Room</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="room" placeholder="Room" value="">
                            </div>
                        </div>
                        
                        <div class="mb-3">  
                            <label for="Password" class="form-label">Time</label>
                            <div class="input-group d-flex gap-4">
                                
                                <input type="time" class="form-control" name="time_from" placeholder="From" value="08:00">
                                <input type="time" class="form-control" name="time_to" placeholder="To" value="09:00">
                            </div>
                        </div>
                        
                        <button type="submit" name="add_class" class="btn btn-primary">Submit</button>

                    </form>
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