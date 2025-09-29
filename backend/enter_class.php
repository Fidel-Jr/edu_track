<?php
    session_start();
    require "../db/connect.php"; // your PDO connection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['class_id'] = $_POST['class_id'];
        header("Location: ../pages/dashboard.php");
        // echo $_SESSION['class_id'];
    }
?>