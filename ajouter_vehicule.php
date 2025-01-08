<?php
session_start();
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Créer le dossier uploads s'il n'existe pas
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Récupérer et valider les données du formulaire
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $prixParJour = floatval($_POST['prixParJour'] ?? 0);
    $nombrePlaces = intval($_POST['nombrePlaces'] ?? 0);
    $carburant = trim($_POST['carburant'] ?? '');
    $disponible = trim($_POST['disponible'] ?? 'Disponible');

    if (empty($marque) || empty($modele) || empty($type) || $prixParJour <= 0 || $nombrePlaces <= 0 || empty($carburant)) {
        echo json_encode(['status' => 'error', 'message' => 'Tous les champs sont obligatoires et doivent être valides.']);
        exit;
    }

    // Gérer l'upload de l'image
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;

        // Vérifier le type MIME de l'image
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileMimeType = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Le fichier doit être une image (JPEG, PNG ou GIF).']);
            exit;
        }

        // Déplacer le fichier téléchargé
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $filename; // Stocker uniquement le nom du fichier
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Échec du téléchargement de l\'image.']);
            exit;
        }
    }

    try {
        // Insérer le véhicule dans la base de données
        $sql = "INSERT INTO vehicule (marque, modele, type, prixParJour, disponible, nombrePlaces, carburant, image) 
                VALUES (:marque, :modele, :type, :prixParJour, :disponible, :nombrePlaces, :carburant, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':marque' => $marque,
            ':modele' => $modele,
            ':type' => $type,
            ':prixParJour' => $prixParJour,
            ':disponible' => $disponible,
            ':nombrePlaces' => $nombrePlaces,
            ':carburant' => $carburant,
            ':image' => $image,
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Véhicule ajouté avec succès !']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'ajout du véhicule : ' . $e->getMessage()]);
    }
}
?>