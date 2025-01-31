<?php
session_start();
include('db.php'); // Assurez-vous que ce fichier contient la connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php'); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit;
}

$userId = $_SESSION['user']['id']; // Récupérer l'ID de l'utilisateur connecté

// Récupérer les notifications de l'utilisateur
$sqlNotifications = "SELECT * FROM notification WHERE clientId = ? AND lu = FALSE ORDER BY dateEnvoi DESC";
$stmtNotifications = $pdo->prepare($sqlNotifications);
$stmtNotifications->execute([$userId]);
$notifications = $stmtNotifications->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les véhicules disponibles
$sqlVehicules = "SELECT * FROM vehicule";
$stmtVehicules = $pdo->query($sqlVehicules);
$vehicules = $stmtVehicules->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque véhicule, récupérer les dates réservées
foreach ($vehicules as &$vehicule) {
    $sqlReservedDates = "SELECT dateHeureDebut, dateHeureFin FROM reservation WHERE vehiculeId = ? AND statut != 'Annulée'";
    $stmtReservedDates = $pdo->prepare($sqlReservedDates);
    $stmtReservedDates->execute([$vehicule['idVehicule']]);
    $vehicule['reservedDates'] = $stmtReservedDates->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les réservations de l'utilisateur
$sqlReservations = "SELECT r.*, v.marque, v.modele, v.prixParJour 
                    FROM reservation r 
                    JOIN vehicule v ON r.vehiculeId = v.idVehicule 
                    WHERE r.clientId = ? AND r.statut = 'En attente'";
$stmtReservations = $pdo->prepare($sqlReservations);
$stmtReservations->execute([$userId]);
$reservations = $stmtReservations->fetchAll(PDO::FETCH_ASSOC);

// Gestion des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Réserver un véhicule
    if (isset($_POST['vehiculeId'], $_POST['dateHeureDebut'], $_POST['dateHeureFin'], $_POST['montantTotal'])) {
        $vehiculeId = $_POST['vehiculeId'];
        $dateHeureDebut = $_POST['dateHeureDebut']; // Format: YYYY-MM-DD HH:MM
        $dateHeureFin = $_POST['dateHeureFin']; // Format: YYYY-MM-DD HH:MM
        $montantTotal = $_POST['montantTotal'];

        // Vérifier que les dates sont valides
        if (!strtotime($dateHeureDebut) || !strtotime($dateHeureFin)) {
            echo json_encode(['success' => false, 'message' => 'Format de date invalide.']);
            exit;
        }

        // Vérifier si le véhicule est déjà réservé pour ces dates
        $sqlCheck = "SELECT * FROM reservation 
                     WHERE vehiculeId = ?  AND statut !='Annulée'
                     AND ((dateHeureDebut <= ? AND dateHeureFin >= ?) 
                     OR (dateHeureDebut <= ? AND dateHeureFin >= ?) 
                     OR (dateHeureDebut >= ? AND dateHeureFin <= ?))";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$vehiculeId, $dateHeureDebut, $dateHeureFin, $dateHeureDebut, $dateHeureFin, $dateHeureDebut, $dateHeureFin]);

        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Le véhicule est déjà réservé pour ces dates.']);
            exit;
        }

        // Insérer la réservation dans la base de données
        $sql = "INSERT INTO reservation (clientId, vehiculeId, dateHeureDebut, dateHeureFin, montantTotal, statut) 
                VALUES (?, ?, ?, ?, ?, 'En attente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $vehiculeId, $dateHeureDebut, $dateHeureFin, $montantTotal]);

        // Mettre à jour le statut du véhicule en "Non Disponible"
        $sqlUpdateVehicule = "UPDATE vehicule SET disponible = 'Non Disponible' WHERE idVehicule = ?";
        $stmtUpdateVehicule = $pdo->prepare($sqlUpdateVehicule);
        $stmtUpdateVehicule->execute([$vehiculeId]);

        echo json_encode(['success' => true]);
        exit;
    }

    // Annuler une réservation
// Annuler et supprimer une réservation
if (isset($_POST['reservationId'])) {
  $reservationId = $_POST['reservationId'];

  // Récupérer l'ID du véhicule associé à la réservation
  $sqlGetVehiculeId = "SELECT vehiculeId FROM reservation WHERE idReservation = ?";
  $stmtGetVehiculeId = $pdo->prepare($sqlGetVehiculeId);
  $stmtGetVehiculeId->execute([$reservationId]);
  $vehiculeId = $stmtGetVehiculeId->fetchColumn();

  if ($vehiculeId) {
      // Supprimer la réservation
      $sqlDeleteReservation = "DELETE FROM reservation WHERE idReservation = ? AND clientId = ?";
      $stmtDeleteReservation = $pdo->prepare($sqlDeleteReservation);
      $stmtDeleteReservation->execute([$reservationId, $userId]);

      // Mettre à jour le statut du véhicule en "Disponible"
      $sqlUpdateVehicule = "UPDATE vehicule SET disponible = 'Disponible' WHERE idVehicule = ?";
      $stmtUpdateVehicule = $pdo->prepare($sqlUpdateVehicule);
      $stmtUpdateVehicule->execute([$vehiculeId]);

      echo json_encode(['success' => true]);
      exit;
  } else {
      echo json_encode(['success' => false, 'message' => 'Réservation introuvable']);
      exit;
  }
}


    // Modifier une réservation
    if (isset($_POST['modifyReservationId'], $_POST['newDateHeureDebut'], $_POST['newDateHeureFin'], $_POST['newMontantTotal'])) {
        $reservationId = $_POST['modifyReservationId'];
        $newDateHeureDebut = $_POST['newDateHeureDebut'];
        $newDateHeureFin = $_POST['newDateHeureFin'];
        $newMontantTotal = $_POST['newMontantTotal'];

        // Vérifier que les dates sont valides
        if (!strtotime($newDateHeureDebut) || !strtotime($newDateHeureFin)) {
            echo json_encode(['success' => false, 'message' => 'Format de date invalide.']);
            exit;
        }

        // Vérifier si le véhicule est déjà réservé pour ces nouvelles dates
        $sqlCheck = "SELECT * FROM reservation 
                     WHERE vehiculeId = (SELECT vehiculeId FROM reservation WHERE idReservation = ?)
                     AND idReservation != ?
                     AND ((dateHeureDebut <= ? AND dateHeureFin >= ?) 
                     OR (dateHeureDebut <= ? AND dateHeureFin >= ?) 
                     OR (dateHeureDebut >= ? AND dateHeureFin <= ?))";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$reservationId, $reservationId, $newDateHeureDebut, $newDateHeureFin, $newDateHeureDebut, $newDateHeureFin, $newDateHeureDebut, $newDateHeureFin]);

        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Le véhicule est déjà réservé pour ces dates.']);
            exit;
        }

        // Mettre à jour la réservation
        $sql = "UPDATE reservation SET dateHeureDebut = ?, dateHeureFin = ?, montantTotal = ? WHERE idReservation = ? AND clientId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$newDateHeureDebut, $newDateHeureFin, $newMontantTotal, $reservationId, $userId]);

        // Récupérer les données mises à jour de la réservation
        $sqlUpdatedReservation = "SELECT r.*, v.marque, v.modele, v.prixParJour 
                                  FROM reservation r 
                                  JOIN vehicule v ON r.vehiculeId = v.idVehicule 
                                  WHERE r.idReservation = ?";
        $stmtUpdatedReservation = $pdo->prepare($sqlUpdatedReservation);
        $stmtUpdatedReservation->execute([$reservationId]);
        $updatedReservation = $stmtUpdatedReservation->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'reservation' => $updatedReservation]);
        exit;
    }

    // Mettre à jour les notifications
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['notificationId']) && is_numeric($data['notificationId'])) {
        $notificationId = $data['notificationId'];

        $sql = "UPDATE notification SET lu = TRUE WHERE idNotification = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$notificationId]);

        echo json_encode(['success' => true]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <title>Location de Véhicules - Client</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Itim&display=swap");
    body {
      font-family: "Itim", cursive;
    }
    .vehicle-card {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .vehicle-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .vehicle-card img {
      border-radius: 8px;
      width: 100%;
      height: auto;
    }
    .vehicle-card h3 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2d3748;
    }
    .vehicle-card p {
      font-size: 1rem;
      color: #4a5568;
    }
    .btn {
      padding: 10px 20px;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .btn-primary {
      background-color: #4fd1c5;
      color: #ffffff;
    }
    .btn-primary:hover {
      background-color: #38a89d;
      transform: translateY(-2px);
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background-color: #2d3748;
      padding: 20px;
      border-radius: 8px;
      width: 90%;
      max-width: 500px;
    }
    #sidebar {
      background-color: #2d3748;
      color: #ffffff;
      padding: 20px;
      border-radius: 8px;
    }
    #sidebar button {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      color: #ffffff;
      background-color: transparent;
      border: none;
      text-align: left;
      transition: background-color 0.2s ease;
    }
    #sidebar button:hover {
      background-color: #4a5568;
    }
    #sidebar i {
      margin-right: 10px;
      color: #4fd1c5;
    }
    body.dark-mode {
      background-color: #1a202c;
      color: #e2e8f0;
    }
    body.dark-mode .vehicle-card {
      background-color: #2d3748;
      color: #e2e8f0;
    }
    body.dark-mode .vehicle-card h3,
    body.dark-mode .vehicle-card p {
      color: #e2e8f0;
    }
    body.dark-mode .btn-primary {
      background-color: #4fd1c5;
      color: #1a202c;
    }
    body.dark-mode .modal-content {
      background-color: #2d3748;
      color: #e2e8f0;
    }
    .notification-icon {
      position: relative;
      cursor: pointer;
    }
    .notification-count {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: #e53e3e;
      color: white;
      font-size: 0.75rem;
      padding: 2px 6px;
      border-radius: 50%;
    }
    .notification-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background-color: white;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 300px;
      max-height: 400px;
      overflow-y: auto;
      z-index: 1000;
      display: none;
    }
    .notification-dropdown.show {
      display: block;
    }
    .notification-item {
      padding: 12px;
      border-bottom: 1px solid #e2e8f0;
      cursor: pointer;
    }
    .notification-item:last-child {
      border-bottom: none;
    }
    .notification-item:hover {
      background-color: #f7fafc;
    }
    #notification-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
    }
    .notification {
      margin-bottom: 10px;
      animation: slideIn 0.5s ease-out;
    }
    @keyframes slideIn {
      from {
        transform: translateX(100%);
      }
      to {
        transform: translateX(0);
      }
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-900">
  <div id="notification-container"></div>
  <header class="p-4 flex justify-between items-center bg-gray-800">
    <div class="flex items-center gap-2">
      <button id="menuButton" class="text-gray-100 text-3xl lg:hidden hover:text-gray-400" aria-label="Ouvrir le menu">
        <i class="bx bx-menu"></i>
      </button>
      <div class="flex items-center gap-4 text-purple-600 cursor-pointer logo hover:text-purple-800 transition-all duration-300">
        <i class="bx bx-car text-3xl"></i>
        <span class="text-xl font-semibold">Location Auto</span>
      </div>
    </div>
    <div class="flex items-center gap-4">
      <!-- Icône de notification -->
      <div class="notification-icon relative text-gray-100 hover:text-gray-400">
        <i class="bx bx-bell text-2xl"></i>
        <span class="notification-count"><?php echo !empty($notifications) ? count($notifications) : 0; ?></span>
        <!-- Menu déroulant des notifications -->
        <div id="notificationDropdown" class="notification-dropdown">
          <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
              <div class="notification-item" data-notification-id="<?php echo $notification['idNotification']; ?>">
                <p class="text-sm"><?php echo htmlspecialchars($notification['message']); ?></p>
                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($notification['dateEnvoi'])); ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="notification-item">
              <p class="text-sm">Aucune notification</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <!-- Bouton mode nuit -->
      <button id="toggleTheme" class="text-gray-100 hover:text-gray-400">
        <i id="themeIcon" class="bx bx-moon text-2xl"></i>
      </button>
      <!-- Bouton de déconnexion -->
      <button onclick="window.location.href='index.html'" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
        Déconnexion
      </button>
    </div>
  </header>

  <div class="flex p-3 gap-4">
    <aside id="sidebar" class="w-42 hidden lg:block rounded-lg bg-gray-800 p-2 py-5 fixed lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-200 ease-in-out">
      <nav class="space-y-4">
        <button onclick="afficherSection('vehicules')" class="flex items-center space-x-3 text-gray-300 hover:bg-gray-700 p-3 rounded-md w-full text-left">
          <i class="bx bx-car text-teal-400"></i>
          <span>Véhicules Disponibles</span>
        </button>
        <button onclick="afficherSection('reservations')" class="flex items-center space-x-3 text-gray-300 hover:bg-gray-700 p-3 rounded-md w-full text-left">
          <i class="bx bx-calendar text-teal-400"></i>
          <span>Mes Réservations</span>
        </button>
      </nav>
    </aside>
    <main class="flex-1 bg-gray-100 flex flex-col gap-4 ml-0 lg:ml-42">
      <!-- Section Véhicules Disponibles -->
      <section id="vehicules" class="p-4 space-y-6 bg-white flex flex-col rounded-lg shadow-md">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Véhicules Disponibles</h1>
    <div id="vehicules-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Les véhicules seront insérés ici dynamiquement -->
    </div>
</section>

      <!-- Section Mes Réservations -->
      <section id="reservations" class="hidden p-6 bg-white rounded-lg shadow-md">
  <h1 class="text-2xl font-semibold text-gray-800 mb-6">Mes Réservations</h1>
  <div class="overflow-x-auto">
    <table class="w-full bg-white rounded-lg overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">ID</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">Véhicule</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">Date de début</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">Date de fin</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">Montant Total</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">Statut</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (!empty($reservations)): ?>
          <?php foreach ($reservations as $reservation): ?>
            <tr id="reservation-<?php echo $reservation['idReservation']; ?>" class="hover:bg-gray-50 transition-colors duration-200">
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($reservation['idReservation']); ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($reservation['dateHeureDebut'])); ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($reservation['dateHeureFin'])); ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($reservation['montantTotal']); ?> Mad</td>
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($reservation['statut']); ?></td>
              <td class="px-6 py-4 text-sm">
                <button
                  onclick="annulerReservation(<?php echo $reservation['idReservation']; ?>)"
                  class="bg-red-500 text-white px-3 py-1.5 rounded-md hover:bg-red-600 transition-colors duration-200"
                >
                  Annuler
                </button>
                <button
                  onclick="modifierReservation(<?php echo $reservation['idReservation']; ?>)"
                  class="bg-yellow-500 text-white px-3 py-1.5 rounded-md hover:bg-yellow-600 transition-colors duration-200"
                >
                  Modifier
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">Aucune réservation trouvée.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
    </main>
  </div>

  <!-- Modale de Réservation -->
  <div id="reservationModal" class="fixed inset-0 z-50 hidden">
    <!-- Fond sombre du modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>

    <!-- Contenu du modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
        <!-- Titre du modal -->
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">
          Réserver un Véhicule
        </h2>

        <!-- Formulaire de réservation -->
        <form id="reservationForm">
          <input type="hidden" id="vehiculeId" name="vehiculeId" />
          <div class="mb-4">
            <label for="dateHeureDebut" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Date et heure de début
            </label>
            <input
              type="datetime-local"
              id="dateHeureDebut"
              name="dateHeureDebut"
              required
              class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="mb-4">
            <label for="dateHeureFin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Date et heure de fin
            </label>
            <input
              type="datetime-local"
              id="dateHeureFin"
              name="dateHeureFin"
              required
              class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="mb-4">
            <label for="montantTotal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Montant Total
            </label>
            <input
              type="text"
              id="montantTotal"
              name="montantTotal"
              readonly
              class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="flex justify-end space-x-4">
            <button 
              type="button"
              onclick="closeModal()"
              class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
            >
              Réserver
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modale de confirmation d'annulation -->
  <div id="cancelReservationModal" class="modal hidden">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Annuler la Réservation</h2>
      <form id="cancelReservationForm">
        <input type="hidden" id="reservationId" name="reservationId" />
        <div class="mt-6 flex justify-end space-x-4">
          <button type="button" id="cancelCancelReservation" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Confirmer l'annulation</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modale de modification de réservation -->
  <div id="modifyReservationModal" class="modal hidden">
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <!-- Titre du modal -->
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Modifier la Réservation</h2>

      <!-- Formulaire de modification -->
      <form id="modifyReservationForm">
        <input type="hidden" id="modifyReservationId" name="modifyReservationId" />

        <!-- Champ pour la nouvelle date et heure de début -->
        <div class="mb-6">
          <label for="newDateHeureDebut" class="block text-sm font-medium text-gray-700 mb-2">
            Nouvelle date et heure de début
          </label>
          <input
            type="datetime-local"
            id="newDateHeureDebut"
            name="newDateHeureDebut"
            required
            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Champ pour la nouvelle date et heure de fin -->
        <div class="mb-6">
          <label for="newDateHeureFin" class="block text-sm font-medium text-gray-700 mb-2">
            Nouvelle date et heure de fin
          </label>
          <input
            type="datetime-local"
            id="newDateHeureFin"
            name="newDateHeureFin"
            required
            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Champ pour le nouveau montant total -->
        <div class="mb-6">
          <label for="newMontantTotal" class="block text-sm font-medium text-gray-700 mb-2">
            Nouveau montant total
          </label>
          <input
            type="text"
            id="newMontantTotal"
            name="newMontantTotal"
            readonly
            class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-end space-x-4">
          <button
            type="button"
            id="cancelModifyReservation"
            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Annuler
          </button>
          <button
            type="submit"
            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Confirmer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
  <script>
   document.addEventListener('DOMContentLoaded', function () {
    fetch('fetch_vehicules.php')
        .then(response => response.json())
        .then(data => {
            console.log("Réponse complète du serveur:", data);
            if (data.status === 'success') {
                const vehiculesContainer = document.getElementById('vehicules-container');
                vehiculesContainer.innerHTML = ''; // Clear the container before adding new elements

                data.data.forEach(vehicule => {
                    const vehiculeCard = document.createElement('div');
                    vehiculeCard.className = 'vehicle-card';

                    vehiculeCard.innerHTML = `
                        <img src="uploads/${vehicule.image}" />
                        <h3 class="mt-4">${vehicule.marque} ${vehicule.modele}</h3>
                        <p class="mt-2"><i class="bx bx-car"></i> Type: ${vehicule.type}</p>
                        <p class="mt-2"><i class="bx bx-money"></i> Prix par jour: ${vehicule.prixParJour} Mad</p>
                        <p class="mt-2"><i class="bx bx-user"></i> Places: ${vehicule.nombrePlaces}</p>
                        <p class="mt-2"><i class="bx bx-gas-pump"></i> Carburant: ${vehicule.carburant}</p>
                        <p class="mt-2"><i class="bx bx-calendar"></i> Statut: ${vehicule.disponible}</p>
                        <button onclick='openModal(${vehicule.idVehicule}, ${vehicule.prixParJour}, ${JSON.stringify(vehicule.reservedDates || [])})' class="bg-blue-500 text-white p-2 rounded mt-4 w-full">Réserver</button>
                    `;

                    vehiculesContainer.appendChild(vehiculeCard);
                });
            } else {
                console.error('Erreur lors de la récupération des véhicules:', data.message);
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
        });
});
// Fonction pour afficher des notifications
function afficherNotification(message, type) {
    const container = document.getElementById("notification-container");
    const notification = document.createElement("div");
    notification.className = `notification p-4 rounded-md ${
        type === "success" ? "bg-green-500" : "bg-red-500"
    } text-white flex items-center`;

    // Ajouter une icône si le type est "success"
    if (type === "success") {
        const icon = document.createElement("i");
        icon.className = "fas fa-check-circle mr-2"; // Icône de succès de FontAwesome
        notification.appendChild(icon);
    }

    // Ajouter le message
    const messageText = document.createElement("span");
    messageText.textContent = message;
    notification.appendChild(messageText);

    container.appendChild(notification);

    // Masquer et supprimer la notification après 3 secondes
    setTimeout(() => {
        notification.classList.add("hidden");
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}
// Fonction pour afficher ou masquer les sections
function afficherSection(sectionId) {
    const sections = document.querySelectorAll('main section');
    sections.forEach(section => {
        if (section.id === sectionId) {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    });
}

// Fonction pour afficher la modale de réservation
function openModal(vehiculeId, prixParJour) {
    document.getElementById('vehiculeId').value = vehiculeId;
    document.getElementById('reservationModal').style.display = 'flex';

    const dateHeureDebutInput = document.getElementById('dateHeureDebut');
    const dateHeureFinInput = document.getElementById('dateHeureFin');
    const montantTotalInput = document.getElementById('montantTotal');

    // Récupérer les dates réservées pour ce véhicule
    const vehicule = <?php echo json_encode($vehicules); ?>.find(v => v.idVehicule == vehiculeId);
    const reservedDates = vehicule ? vehicule.reservedDates : [];

    // Convertir les plages de dates réservées en un tableau de jours désactivés
    let disabledDates = [];

    reservedDates.forEach(range => {
        let startDate = new Date(range.dateHeureDebut);
        let endDate = new Date(range.dateHeureFin);

        while (startDate <= endDate) {
            let dateStr = startDate.toISOString().split('T')[0]; // Format YYYY-MM-DD
            if (!disabledDates.includes(dateStr)) {
                disabledDates.push(dateStr); // Ajouter la date
            }
            startDate.setDate(startDate.getDate() + 1); // Passer au jour suivant
        }
    });

    console.log("🚫 Dates désactivées pour le véhicule " + vehiculeId + ":", disabledDates);

    // Initialiser Flatpickr pour la date de début
    flatpickr(dateHeureDebutInput, {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        disable: disabledDates, // Bloquer les dates réservées
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                dateHeureFinInput._flatpickr.set("minDate", selectedDates[0]);
            }
            calculerMontantTotal();
        },
    });

    // Initialiser Flatpickr pour la date de fin
    flatpickr(dateHeureFinInput, {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        disable: disabledDates, // Bloquer les dates réservées
        onChange: function() {
            calculerMontantTotal();
        },
    });

    function calculerMontantTotal() {
        const dateHeureDebutValue = dateHeureDebutInput.value;
        const dateHeureFinValue = dateHeureFinInput.value;

        if (dateHeureDebutValue && dateHeureFinValue) {
            const dateHeureDebutDate = new Date(dateHeureDebutValue);
            const dateHeureFinDate = new Date(dateHeureFinValue);

            if (dateHeureFinDate <= dateHeureDebutDate) {
                montantTotalInput.value = "0.00";
                return;
            }

            const diffTime = Math.abs(dateHeureFinDate - dateHeureDebutDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const montantTotal = diffDays * prixParJour;
            montantTotalInput.value = montantTotal.toFixed(2);
        } else {
            montantTotalInput.value = "0.00";
        }
    }

    montantTotalInput.value = "0.00";
}


// Fermer la modale de réservation
function closeModal() {
    document.getElementById('reservationModal').style.display = 'none';
}

// Soumettre le formulaire de réservation
document.getElementById('reservationForm').onsubmit = async function (e) {
    e.preventDefault();
     console.log("ID du véhicule sélectionné:", vehiculeId);
    const formData = new FormData(this);
    const response = await fetch('', {
        method: 'POST',
        body: formData,
    });

    // Vérifier si la réponse est valide
    if (!response.ok) {
        throw new Error('Erreur réseau');
    }

    const result = await response.json();
   
    if (result.success) {
        afficherNotification('Réservation effectuée avec succès !', 'success');
        closeModal(); // Fermer le modal
        setTimeout(() => {
            location.reload(); // Recharger la page pour afficher les nouvelles réservations
        }, 1000); // Attendre 1 seconde avant de recharger
    } else {
        afficherNotification(result.message || 'Erreur lors de la réservation.', 'error');
    }
};

// Gestion du mode nuit
const toggleThemeButton = document.getElementById('toggleTheme');
const themeIcon = document.getElementById('themeIcon');
const body = document.body;

toggleThemeButton.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    if (body.classList.contains('dark-mode')) {
        themeIcon.classList.replace('bx-moon', 'bx-sun');
    } else {
        themeIcon.classList.replace('bx-sun', 'bx-moon');
    }
});

// Gestion des notifications
const notificationIcon = document.querySelector('.notification-icon');
const notificationDropdown = document.getElementById('notificationDropdown');
const notificationCount = document.querySelector('.notification-count');

notificationIcon.addEventListener('click', () => {
    notificationDropdown.classList.toggle('show');
});

// Fermer le menu déroulant des notifications en cliquant à l'extérieur
document.addEventListener('click', (event) => {
    if (!notificationIcon.contains(event.target)) {
        notificationDropdown.classList.remove('show');
    }
});

// Marquer une notification comme lue lorsqu'elle est cliquée
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', async () => {
        const notificationId = item.dataset.notificationId;

        try {
            const response = await fetch('client.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notificationId }),
            });

            if (!response.ok) {
                throw new Error('Erreur réseau');
            }

            const result = await response.json();

            if (result.success) {
                // Mettre à jour le compteur de notifications
                const count = parseInt(notificationCount.textContent);
                if (count > 0) {
                    notificationCount.textContent = count - 1;
                }

                // Masquer la notification visuellement
                item.style.display = 'none';

                // Si aucune notification n'est visible, masquer le compteur
                if (notificationCount.textContent === '0') {
                    notificationCount.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Erreur :', error);
        }
    });
});

// Fonction pour annuler une réservation
function annulerReservation(reservationId) {
    // Afficher la modale de confirmation d'annulation
    document.getElementById('cancelReservationModal').style.display = 'flex';

    // Remplir l'ID de la réservation dans le formulaire
    document.getElementById('reservationId').value = reservationId;
}

// Fermer la modale de confirmation d'annulation
document.getElementById('cancelCancelReservation').addEventListener('click', () => {
    document.getElementById('cancelReservationModal').style.display = 'none';
});

// Soumettre le formulaire d'annulation
document.getElementById('cancelReservationForm').onsubmit = async function (e) {
    e.preventDefault();

    const reservationId = document.getElementById('reservationId').value;

    const response = await fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `reservationId=${reservationId}`,
    });

    if (!response.ok) {
        throw new Error('Erreur réseau');
    }

    const result = await response.json();

    if (result.success) {
        afficherNotification('Réservation annulée avec succès.', 'success');
        document.getElementById('cancelReservationModal').style.display = 'none';

        // Supprimer la ligne de la réservation annulée
        document.getElementById(`reservation-${reservationId}`).remove();
    } else {
        afficherNotification('Erreur lors de l\'annulation de la réservation.', 'error');
    }
};

// Fonction pour modifier une réservation
function modifierReservation(reservationId) {
    document.getElementById('modifyReservationId').value = reservationId;
    document.getElementById('modifyReservationModal').style.display = 'flex';

    // Récupérer les informations de la réservation
    const reservation = <?php echo json_encode($reservations); ?>.find(r => r.idReservation == reservationId);

    if (reservation) {
        // Pré-remplir les champs de la modale
        document.getElementById('newDateHeureDebut').value = reservation.dateHeureDebut.replace(' ', 'T');
        document.getElementById('newDateHeureFin').value = reservation.dateHeureFin.replace(' ', 'T');
        document.getElementById('newMontantTotal').value = reservation.montantTotal;

        // Récupérer les dates réservées pour ce véhicule
        const vehiculeId = reservation.vehiculeId;
        const reservedDates = <?php echo json_encode($vehicules); ?>.find(v => v.idVehicule == vehiculeId).reservedDates;

        // Convertir les dates réservées en un tableau de plages de dates
        const reservedDatesArray = reservedDates.map(range => {
            return {
                from: new Date(range.dateHeureDebut),
                to: new Date(range.dateHeureFin),
            };
        });

        // Initialiser Flatpickr pour les champs de date
        flatpickr("#newDateHeureDebut", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            disable: reservedDatesArray, // Désactiver les dates réservées
            onChange: function(selectedDates, dateStr) {
                document.getElementById('newDateHeureFin')._flatpickr.set("minDate", selectedDates[0]);
                calculerNouveauMontantTotal();
            },
        });

        flatpickr("#newDateHeureFin", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            disable: reservedDatesArray, // Désactiver les dates réservées
            onChange: function(selectedDates, dateStr) {
                calculerNouveauMontantTotal();
            },
        });
    }
}

// Fonction pour calculer le nouveau montant total
function calculerNouveauMontantTotal() {
    const dateHeureDebutValue = document.getElementById('newDateHeureDebut').value;
    const dateHeureFinValue = document.getElementById('newDateHeureFin').value;

    if (dateHeureDebutValue && dateHeureFinValue) {
        const dateHeureDebutDate = new Date(dateHeureDebutValue);
        const dateHeureFinDate = new Date(dateHeureFinValue);

        // Vérifier que la date de fin est après la date de début
        if (dateHeureFinDate <= dateHeureDebutDate) {
            document.getElementById('newMontantTotal').value = "0.00";
            return;
        }

        // Calculer la différence en millisecondes
        const diffTime = Math.abs(dateHeureFinDate - dateHeureDebutDate);

        // Convertir la différence en jours
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        // Récupérer le prix par jour (à définir selon votre logique)
        const prixParJour = 100; // Exemple : remplacer par la valeur réelle

        // Calculer le montant total
        const montantTotal = diffDays * prixParJour;
        document.getElementById('newMontantTotal').value = montantTotal.toFixed(2);
    } else {
        document.getElementById('newMontantTotal').value = "0.00";
    }
}

// Gestion de la modale de modification
document.getElementById('cancelModifyReservation').addEventListener('click', () => {
    document.getElementById('modifyReservationModal').style.display = 'none';
});

// Soumettre le formulaire de modification
document.getElementById('modifyReservationForm').onsubmit = async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetch('', {
        method: 'POST',
        body: formData,
    });

    if (!response.ok) {
        throw new Error('Erreur réseau');
    }

    const result = await response.json();
    if (result.success) {
        afficherNotification('Réservation modifiée avec succès.', 'success');
        document.getElementById('modifyReservationModal').style.display = 'none';
        
        // Mettre à jour la ligne de la réservation dans la table
        const reservationRow = document.getElementById(`reservation-${result.reservation.idReservation}`);
        if (reservationRow) {
            // Mettre à jour les cellules de la ligne
            reservationRow.querySelector('td:nth-child(3)').textContent = result.reservation.dateHeureDebut;
            reservationRow.querySelector('td:nth-child(4)').textContent = result.reservation.dateHeureFin;
            reservationRow.querySelector('td:nth-child(5)').textContent = result.reservation.montantTotal + ' Mad';
        }
    } else {
        afficherNotification(result.message || 'Erreur lors de la modification de la réservation.', 'error');
    }
};
</script>
</body>
</html>
