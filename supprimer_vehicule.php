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
        // Supprimer le véhicule de la base de données
        $sql = "DELETE FROM vehicule WHERE idVehicule = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Véhicule supprimé avec succès !']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Aucun véhicule trouvé avec cet ID.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression du véhicule : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode de requête non autorisée.']);
}
?>