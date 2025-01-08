<?php
// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérifier si l'email est fourni
    if (!isset($data['email'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Email non fourni']);
        exit;
    }

    // Récupérer l'email du client à supprimer
    $email = $data['email'];

    // Récupérer l'ID de l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Utilisateur non trouvé']);
        exit;
    }

    // Vérifier si l'utilisateur a des réservations en cours
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE clientId = ? AND statut = 'En attente'");
    $stmt->execute([$userId]);
    $reservationCount = $stmt->fetchColumn();

    if ($reservationCount > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Impossible de supprimer cet utilisateur car il a des réservations en cours.']);
        exit;
    }

    // Commencer une transaction pour garantir l'intégrité des données
    $pdo->beginTransaction();

    try {
        // Supprimer les notifications associées à l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM notification WHERE clientId = ?");
        $stmt->execute([$userId]);

        // Supprimer les réservations associées à l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM reservation WHERE clientId = ? AND (statut='Confirmée' OR statut='Annulée')");
        $stmt->execute([$userId]);

        // Supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
        $stmt->execute([$userId]);

        // Valider la transaction
        $pdo->commit();

        // Retourner une réponse JSON
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Utilisateur et données associées supprimés avec succès']);
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
    }
} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>