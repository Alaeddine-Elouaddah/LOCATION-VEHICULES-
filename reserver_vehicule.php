<?php
session_start();

$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $vehiculeId = $_POST['vehiculeId'];
  $dateDebut = $_POST['dateDebut'];
  $dateFin = $_POST['dateFin'];
  $clientId = $_SESSION['client_id']; // Assurez-vous que l'ID du client est stocké dans la session

  $sql = "INSERT INTO reservation (vehicule_id, client_id, date_debut, date_fin, statut) VALUES (?, ?, ?, ?, 'En Attente')";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$vehiculeId, $clientId, $dateDebut, $dateFin]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>