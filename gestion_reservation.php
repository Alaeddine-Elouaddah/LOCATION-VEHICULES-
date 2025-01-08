<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Vérifier si les clés existent dans $_POST
  if (!isset($_POST['action']) || !isset($_POST['reservationId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données manquantes.']);
    exit;
  }

  $action = $_POST['action']; // 'confirmer' ou 'annuler'
  $reservationId = $_POST['reservationId'];
  $message = $_POST['message'] ?? ''; // Message pour l'annulation

  if ($action === 'confirmer') {
    // Mettre à jour le statut de la réservation
    $sql = "UPDATE reservation SET statut = 'Confirmée' WHERE idReservation = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $reservationId]);

    echo json_encode(['status' => 'success', 'message' => 'Réservation confirmée avec succès !']);
  } elseif ($action === 'annuler') {
    // Mettre à jour le statut de la réservation
    $sql = "UPDATE reservation SET statut = 'Annulée' WHERE idReservation = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $reservationId]);

    // Ajouter une notification pour l'annulation
    $sqlNotification = "INSERT INTO notification (clientId, message, dateEnvoi)
                        SELECT clientId, :message, CURDATE()
                        FROM reservation
                        WHERE idReservation = :id";
    $stmtNotification = $pdo->prepare($sqlNotification);
    $stmtNotification->execute([':message' => $message, ':id' => $reservationId]);

    echo json_encode(['status' => 'success', 'message' => 'Réservation annulée avec succès !']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Action non reconnue.']);
  }
} catch (PDOException $e) {
  echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()]);
}
?>