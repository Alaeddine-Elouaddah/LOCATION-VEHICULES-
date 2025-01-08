<?php
$host = '127.0.0.1'; // Adresse du serveur MySQL
$dbname = 'locationvoiture'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur MySQL
$password = ''; // Mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>