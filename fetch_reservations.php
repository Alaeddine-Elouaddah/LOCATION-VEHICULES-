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

    // Requête SQL pour récupérer les réservations triées par statut
    $sql = "
        SELECT 
            r.idReservation, 
            u.email, 
            v.marque, 
            v.modele, 
            r.dateDebut, 
            r.dateFin, 
            r.heureReservation, 
            r.heureRetour, 
            r.statut
        FROM reservation r
        JOIN utilisateur u ON r.clientId = u.id
        JOIN vehicule v ON r.vehiculeId = v.idVehicule
        ORDER BY 
            CASE 
                WHEN r.statut = 'En attente' THEN 1
                WHEN r.statut = 'Confirmée' THEN 2
                WHEN r.statut = 'Annulée' THEN 3
                ELSE 4
            END
    ";

    // Exécution de la requête
    $stmt = $pdo->query($sql);

    // Vérification des résultats
    if ($stmt === false) {
        throw new Exception("Erreur lors de l'exécution de la requête SQL.");
    }

    // Récupération des données
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Réponse JSON en cas de succès
    echo json_encode([
        'status' => 'success',
        'data' => $reservations
    ]);
} catch (PDOException $e) {
    // Gestion des erreurs de connexion à la base de données
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Gestion des autres exceptions
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>