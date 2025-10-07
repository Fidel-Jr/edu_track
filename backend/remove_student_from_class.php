<?php
require "../db/connect.php";
session_start();
if (!isset($_SESSION["class_id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No class selected."]);
    exit;
}
if (!isset($_POST["student_id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No student specified."]);
    exit;
}
$classId = $_SESSION["class_id"];
$studentId = $_POST["student_id"];


// Find the student_class_id for this student in this class
$stmt = $pdo->prepare("SELECT id FROM student_classes WHERE class_id = :class_id AND student_id = :student_id");
$stmt->execute([
    ":class_id" => $classId,
    ":student_id" => $studentId
]);
$studentClass = $stmt->fetch(PDO::FETCH_ASSOC);

if ($studentClass) {
    $studentClassId = $studentClass['id'];
    // Delete attendance records
    $pdo->prepare("DELETE FROM attendance WHERE student_class_id = :scid")->execute([':scid' => $studentClassId]);
    // Delete grades records
    $pdo->prepare("DELETE FROM grades WHERE student_class_id = :scid")->execute([':scid' => $studentClassId]);
    // Now delete the student from the class
    $stmt = $pdo->prepare("DELETE FROM student_classes WHERE id = :scid");
    $success = $stmt->execute([':scid' => $studentClassId]);
    if ($success) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to remove student from class."]);
    }
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Student not found in class."]);
}
