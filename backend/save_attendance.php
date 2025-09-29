<?php
require "../db/connect.php"; // your PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $students = $_POST['students'];  // array of student attendance
    $date = $_POST['date'];

    $sql = "INSERT INTO attendance (student_class_id, date, status) 
            VALUES (:student_class_id, :date, :status)";
    $stmt = $pdo->prepare($sql);

    foreach ($students as $student) {
        $stmt->execute([
            ':student_class_id' => $student['id'], // comes from your form
            ':date'             => $date,
            ':status'           => $student['status']
        ]);
    }

    // Redirect back to the attendance page for that class
    header("Location: ../pages/attendance.php");
    exit();
}
?>
