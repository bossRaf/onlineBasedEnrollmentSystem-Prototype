<?php
function connect() {
    $host = "sql300.infinityfree.com";
    $dbname = "if0_41274100_enrolment_system";
    $username = "if0_41274100";
    $password = "8AsRjTqpdV3Nu";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed.");
    }
}
