<?php
require_once "db.php";

header('Content-Type: application/json');

try {
    // Récupérer les véhicules
    $sql = "SELECT idVehicule, marque, modele, type, prixParJour, disponible, nombrePlaces, carburant, image FROM vehicule";
    $stmt = $pdo->query($sql);
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter le chemin complet des images
    $vehicules = array_map(function ($vehicule) {
        if ($vehicule['image']) {
            $vehicule['image']=$vehicule['image'];
        }
        return $vehicule;
    }, $vehicules);

    echo json_encode(['status' => 'success', 'data' => $vehicules]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la récupération des véhicules: ' . $e->getMessage()]);
}
?>