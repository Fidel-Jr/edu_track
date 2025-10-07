<?php
require "../db/connect.php";
session_start();
if (!isset($_SESSION["username"]) || !isset($_SESSION["class_id"])) {
    header("Location: ../welcome.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
    if (count($ids) > 0) {
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM attendance WHERE id IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
    }
}
// Preserve date in redirect
$date = isset($_POST['date']) ? $_POST['date'] : '';
header("Location: ../pages/records.php?type=attendance" . ($date ? "&date=" . urlencode($date) : ""));
exit;
