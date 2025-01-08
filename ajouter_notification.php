<?php
// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les données du formulaire
    $clientId = $_POST['clientId'];
    $message = $_POST['message'];
    $dateEnvoi = date('Y-m-d'); // Date du jour

    // Insérer la notification dans la base de données
    $sql = "INSERT INTO notification (clientId, message, dateEnvoi) VALUES (:clientId, :message, :dateEnvoi)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':clientId' => $clientId,
        ':message' => $message,
        ':dateEnvoi' => $dateEnvoi
    ]);

    // Retourner une réponse JSON
    echo json_encode(['status' => 'success', 'message' => 'Notification enregistrée avec succès.']);
} catch (PDOException $e) {
    // En cas d'erreur, retourner un message d'erreur
    echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()]);
}
?>