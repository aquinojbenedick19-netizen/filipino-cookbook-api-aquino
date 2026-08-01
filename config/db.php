<?php

$host = "localhost";
$db_name = "cookbook_db";
$username = "root";
$password = "Password123!";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
