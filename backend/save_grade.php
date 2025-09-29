<?php
require "../db/connect.php"; // your PDO connection

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
    
    // Prepare SQL statement
    $sql = "INSERT INTO grades (
                student_class_id, activity_type, activity_name, score, maximum_score, percentage, grade, date
            ) 
            VALUES (
                :student_class_id, :activity_type, :activity_name, :score, :maximum_score, :percentage, :grade, :date
            )";
    
    $stmt = $pdo->prepare($sql);
    
    // Insert each student's grade
    for ($i = 0; $i < count($studentClassIds); $i++) {
        $studentClassId = $studentClassIds[$i];
        $score = $scores[$i];
        
        // Calculate percentage and grade
        $percentage = ($score / $maxScore) * 100;
        
        if ($percentage >= 90) {
            $grade = 'A';
        } elseif ($percentage >= 80) {
            $grade = 'B';
        } elseif ($percentage >= 70) {
            $grade = 'C';
        } elseif ($percentage >= 60) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }
        
        // Bind parameters and execute
        $stmt->bindParam(':student_class_id', $studentClassId);
        $stmt->bindParam(':activity_type', $activityType);
        $stmt->bindParam(':activity_name', $activityName);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':maximum_score', $maxScore);
        $stmt->bindParam(':percentage', $percentage);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':date', $gradeDate);
        
        $stmt->execute();
    }

    header("Location: ../pages/grades.php");
    exit();
}
?>
