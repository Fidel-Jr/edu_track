<?php 

    $dns = 'mysql:host=localhost;dbname=edutrack_db; charset=utf8mb4';
    $username = 'root';
    $password = '';
    try {
        $pdo = new PDO($dns, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        // echo "<p style='font-size:50px;'>Connected successfully</p>";
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

?>