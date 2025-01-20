<?php
session_start();
include('db.php'); // Assurez-vous que ce fichier contient la connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Récupérer l'ID de la notification depuis la requête POST
$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['notificationId'];

if (empty($notificationId)) {
    echo json_encode(['success' => false, 'message' => 'ID de notification manquant']);
    exit;
}

// Marquer la notification comme lue dans la base de données
$sql = "UPDATE notification SET lu = 1 WHERE idNotification = ? AND clientId = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$notificationId, $_SESSION['user']['id']]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Notification non trouvée']);
}
?>