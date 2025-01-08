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

    // Vérifier si les données sont valides
    if (!isset($data['email'], $data['telephone'], $data['cin'], $data['numeroPermis'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Données invalides ou manquantes']);
        exit;
    }

    // Récupérer les données du formulaire
    $email = $data['email'];
    $telephone = $data['telephone'];
    $cin = $data['cin'];
    $numeroPermis = $data['numeroPermis'];

    // Mettre à jour les informations du client
    $stmt = $pdo->prepare("UPDATE utilisateur SET telephone = ?, cin = ?, numeroPermis = ? WHERE email = ?");
    $stmt->execute([$telephone, $cin, $numeroPermis, $email]);

    // Retourner une réponse JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Client modifié avec succès']);
} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>