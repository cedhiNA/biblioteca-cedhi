<?php
$host = "localhost";
$user = "root";
$pass = "";    
$db   = "biblioteca_cedhi";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexi贸n: " . $e->getMessage());
}