<?php
require "../db/connect.php"; // your PDO connection
require "../backend/helpers.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $activityType = $_POST['activity_type'];
    $activityName = $_POST['activity_name'];
    $gradeDate    = $_POST['grade_date'];
    $maxScore     = $_POST['max_score'];
    $studentClassIds = $_POST['student_class_id']; // updated
    $scores       = $_POST['score'];  
    
    // Validate data
    if (empty($activityType) || empty($activityName) || empty($gradeDate) || 
        empty($maxScore) || empty($studentClassIds) || empty($scores)) {
        die("Error: All fields are required.");
    }
    
    if (count($studentClassIds) !== count($scores)) {
        die("Error: Student class IDs and scores count mismatch.");
    }
    
    $sql = "CALL AddGrade(:student_class_id, :activity_type, :activity_name, :score, :max_score, :grade_date)";
    $stmt = $pdo->prepare($sql);

    for ($i = 0; $i < count($studentClassIds); $i++) {
        $stmt->execute([
            ':student_class_id' => $studentClassIds[$i],
            ':activity_type'    => $activityType,
            ':activity_name'    => $activityName,
            ':score'            => $scores[$i],
            ':max_score'        => $maxScore,
            ':grade_date'       => $gradeDate
        ]);
    }

    header("Location: ../pages/grades.php");
    exit();
}
?>
