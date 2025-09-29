<?php
session_start();
require "../db/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = $_POST["id_number"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM teachers WHERE id = :id_number AND password = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id_number" => $id_number,
        ":password" => $password
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($stmt->rowCount() > 0) {
        $_SESSION["username"] = $user["username"];
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_info"] = $user;
        header("Location: ../welcome.php");
        exit;
    } else {
        $_SESSION["error"] = "Invalid username or password!";
        header("Location: ../index.php");
        exit;
    }
}
?>