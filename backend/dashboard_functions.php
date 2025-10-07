<?php
require "../db/connect.php"; // your PDO connection

function getClassAttendanceRate($pdo, $classId) {
    $sql = "
        SELECT 
            ROUND(
                (SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) * 100.0) / COUNT(*),
                0
            ) AS class_attendance_rate
        FROM attendance a
        INNER JOIN student_classes sc 
            ON a.student_class_id = sc.id
        WHERE sc.class_id = :class_id
        GROUP BY sc.class_id
        ORDER BY class_attendance_rate DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':class_id' => $classId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['class_attendance_rate'] : 0;
}

function getClassAverageGrade($pdo, $classId) {
    $sql = "
    SELECT 
        ROUND((SUM(g.score) * 100.0) / NULLIF(SUM(g.maximum_score), 0), 0) AS average_grade_percentage
    FROM grades g
    INNER JOIN student_classes sc 
        ON g.student_class_id = sc.id
    WHERE sc.class_id = :class_id
    GROUP BY sc.class_id
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':class_id' => $classId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['average_grade_percentage'] : 0;
}

function getTotalStudentsInClass($pdo, $classId) {
    $sql = "
        SELECT COUNT(DISTINCT sc.student_id) AS total_students
        FROM student_classes sc
        WHERE sc.class_id = :class_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':class_id' => $classId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['total_students'] : 0;
}

function getTotalActivitiesInClass($pdo, $classId) {
    $sql = "
        SELECT 
            COUNT(DISTINCT activity_type) AS total_activities
        FROM grades g
        INNER JOIN student_classes sc 
            ON g.student_class_id = sc.id
        WHERE sc.class_id = :class_id
        GROUP BY sc.class_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':class_id' => $classId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['total_activities'] : 0;
}

