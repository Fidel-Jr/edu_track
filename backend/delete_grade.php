<?php
require "../db/connect.php";
session_start();
if (!isset($_SESSION["username"]) || !isset($_SESSION["class_id"])) {
    header("Location: ../welcome.php");
    exit;
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
    $stmt->execute([$id]);
}
// Preserve date and type in redirect
$date = isset($_GET['date']) ? $_GET['date'] : '';
header("Location: ../pages/records.php?type=grades" . ($date ? "&date=" . urlencode($date) : ""));
exit;
