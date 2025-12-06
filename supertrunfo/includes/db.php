<?php
$host = 'localhost';
$db   = 'supertrunfo_db';
$user = 'root';
$pass = ''; // Senha vazia no XAMPP padrão

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>