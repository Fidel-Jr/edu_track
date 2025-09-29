<?php
    session_start();
    include '../db/connect.php'; // your PDO connection

    // Check if teacher is logged in
    if (!isset($_SESSION["user_info"]["id"])) {
        header("Location: index.php");
        exit;
    }

    if (isset($_POST['add_class'])) {
        // Sanitize inputs
        $teacherId   = $_SESSION["user_info"]["id"];
        $courseTitle = trim($_POST['course_title']);
        $courseCode  = trim($_POST['course_code']); // already stored in session
        $courseName  = trim($_POST['course_name']);
        $room        = trim($_POST['room']);
        $timeFrom    = trim($_POST['time_from']);
        $timeTo      = trim($_POST['time_to']);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO class (teacher_id, course_title, course_code, course_name, room, time_from, time_to)
                VALUES (:teacher_id, :course_title, :course_code, :course_name, :room, :time_from, :time_to)
            ");

            $stmt->execute([
                ':teacher_id'  => $teacherId,
                ':course_title'=> $courseTitle,
                ':course_code' => $courseCode,
                ':course_name' => $courseName,
                ':room'        => $room,
                ':time_from'   => $timeFrom,
                ':time_to'     => $timeTo
            ]);

            echo "<div class='alert alert-success'>✅ Class added successfully!</div>";

            // OPTIONAL: regenerate a new course code after saving
            unset($_SESSION['course_code']);
            header("Location: ../pages/new_class.php");
            exit;

        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
?>