<?php
session_start();
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Récupérer les données JSON du corps de la requête
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Récupérer l'ID du véhicule à supprimer
    $id = intval($data['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de véhicule invalide.']);
        exit;
    }

    try {
        // Commencer une transaction
        $pdo->beginTransaction();

        // Supprimer les réservations associées au véhicule
        $sqlReservation = "DELETE FROM reservation WHERE vehiculeId = :id AND (statut = 'Confirmée' OR statut = 'Annulée')";        $stmtReservation = $pdo->prepare($sqlReservation);
        $stmtReservation->execute([':id' => $id]);

        // Supprimer le véhicule de la base de données
        $sqlVehicule = "DELETE FROM vehicule WHERE idVehicule = :id";
        $stmtVehicule = $pdo->prepare($sqlVehicule);
        $stmtVehicule->execute([':id' => $id]);

        // Valider la transaction
        $pdo->commit();

        if ($stmtVehicule->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Véhicule et réservations associées supprimés avec succès !']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Aucun véhicule trouvé avec cet ID.']);
        }
    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression du véhicule et des réservations : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode de requête non autorisée.']);
}
?>
