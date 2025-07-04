<?php
try {
    $host = getenv('DB_HOST');
    $db   = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');

    if (!$host || !$db || !$user || !$pass) {
        throw new Exception("Missing one or more required DB environment variables.");
    }

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '+08:00'");
} catch (Exception $e) {
    die("App configuration error: " . $e->getMessage());
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

