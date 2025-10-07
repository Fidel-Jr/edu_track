<?php

    include 'db/connect.php';

    session_start();
    if (isset($_SESSION["username"])) {
        header("Location: welcome.php");
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <title>EduTrack - Login</title>
    <style>
        :root {
            --primary-color: #4caf50;
            --primary-dark: #388e3c;
            --accent-color: #228c22;
            --light-bg: #f8f9fa;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: var(--light-bg);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        
        .login-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        
        .login-header {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .illustration-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        
        .illustration {
            max-width: 100%;
            height: auto;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        footer {
            margin-top: auto;
        }
        
        @media (max-width: 992px) {
            .illustration-container {
                margin-bottom: 2rem;
                height: auto;
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
            </div>
        </nav>
    </header>

    <main class="login-container">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-6 col-xl-4 col-md-8">
                    <div class="card login-card p-4">
                        <h3 class="login-header text-center">Login to Your Account</h3>
                        <form method="POST" action="./backend/login.php">
                            <div class="mb-3">  
                                <label for="id_number" class="form-label">ID Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="number" name="id_number" class="form-control" id="id_number" placeholder="Enter your ID Number" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" id="    " placeholder="Enter your password" required>
                                </div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary mt-3">Login</button>
                            <span class="text-danger mt-3 text-center d-block">
                                <?php 
                                    if (isset($_SESSION["error"])) {
                                        echo $_SESSION["error"];
                                        unset($_SESSION["error"]);
                                    }
                                ?>
                            </span>
                            
                            <!-- <div class="text-center mt-3">
                                <p>Don't have an account? <strong>Contact Administrator</strong></p>
                            </div> -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0 text-muted">© 2023 EduTrack. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>