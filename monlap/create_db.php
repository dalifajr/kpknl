<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS monlap');
    echo 'Database monlap created successfully';
} catch (PDOException $e) {
    echo $e->getMessage();
}
