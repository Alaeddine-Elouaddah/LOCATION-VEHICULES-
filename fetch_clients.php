<?php
// Inclure le fichier de connexion à la base de données
require "db.php";

try {
    // Récupérer les clients ayant le rôle "Client"
    $stmt = $pdo->prepare("SELECT email, telephone, cin, numeroPermis FROM utilisateur WHERE role = 'Client'");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des clients ont été trouvés
    if (empty($clients)) {
        // Retourner une réponse JSON indiquant qu'aucun client n'a été trouvé
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => [], 'message' => 'Aucun client trouvé']);
        exit;
    }

    // Retourner les données en JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $clients]);
} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>