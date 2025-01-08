<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérifier si les données JSON sont valides
    if (!isset($data['id']) || !isset($data['reason'])) {
        echo json_encode(['success' => false, 'error' => 'Données JSON manquantes ou incorrectes.']);
        exit;
    }

    $idReservation = $data['id'];
    $reason = $data['reason'];

    // Récupérer l'ID du client associé à la réservation
    $stmt = $pdo->prepare("SELECT clientId FROM reservation WHERE idReservation = :idReservation");
    $stmt->execute(['idReservation' => $idReservation]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception("Réservation non trouvée.");
    }

    $clientId = $reservation['clientId'];

    // Annuler la réservation
    $stmt = $pdo->prepare("UPDATE reservation SET statut = 'Annulée' WHERE idReservation = :idReservation");
    $stmt->execute(['idReservation' => $idReservation]);

    // Insérer la notification dans la table `notification`
    $stmt = $pdo->prepare("INSERT INTO notification (clientId, message, dateEnvoi) VALUES (:clientId, :message, NOW())");
    $stmt->execute([
        'clientId' => $clientId,
        'message' => "Votre réservation a été annulée. Raison : $reason"
    ]);

    // Réponse JSON en cas de succès
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>