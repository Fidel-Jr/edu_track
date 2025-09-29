<?php
    require "../db/connect.php";
    session_start();
    if (!isset($_SESSION["user_info"]["id"])) {
        header("Location: ../index.php");
        exit;
    }
    $success = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
        $id = $_SESSION["user_info"]["id"];
        $first_name = $_POST["first_name"];
        $last_name  = $_POST["last_name"];
        $username   = $_POST["username"];
        $password   = $_POST["password"]; // TODO: hash in real-world apps

        try {
            $sql = "UPDATE teachers
                    SET first_name = ?, last_name = ?, username = ?, password = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $username, $password, $id]);
            // Update session info
            $_SESSION["user_info"]["first_name"] = $first_name;
            $_SESSION["user_info"]["last_name"]  = $last_name;
            $_SESSION["user_info"]["username"]   = $username;
            $_SESSION["user_info"]["password"]   = $password;
            // $success = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
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
    
</head>
<body>
    <div class="overlay" id="overlay"></div>
        <div class="wrapper">
        <!-- Sidebar -->
            <?php include 'sidebar.php';?>
                <!-- Main Content -->
                <div class="main-content">
                <!-- Top Navbar -->
                
                <?php include 'navbar.php'; ?>

                <!-- Page Content -->
                <div class="container-fluid p-4">
                    <h2 class="mb-4">Profile</h2>
                    <div class="container-fluid bg-white p-4 rounded shadow-sm">
                        <form action="profile.php" method="POST">
                            <div class="mb-3">  
                                <label for="First Name" class="form-label">First Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?php echo $_SESSION["user_info"]["first_name"]; ?>">
                                </div>
                            </div>

                            <div class="mb-3">  
                                <label for="Last Name" class="form-label">Last Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?php echo $_SESSION["user_info"]["last_name"]; ?>">
                                </div>
                            </div>

                            <div class="mb-3">  
                                <label for="Username" class="form-label">Username</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo $_SESSION["user_info"]["username"]; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">  
                                <label for="Password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="password" placeholder="Password" value="<?php echo $_SESSION["user_info"]["password"]; ?>">
                                </div>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">Submit</button>

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