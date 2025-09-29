<?php
    session_start();
    unset($_SESSION['class_id']);
    unset($_SESSION['course_code']);
    header("Location: ../welcome.php");
    exit;
?>