<?php
// Connexion à la base de données et récupération des données
$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Récupérer les données
  $sql_vehicules = "SELECT COUNT(*) as total_vehicules FROM vehicule";
  $stmt_vehicules = $pdo->query($sql_vehicules);
  $row_vehicules = $stmt_vehicules->fetch(PDO::FETCH_ASSOC);
  $total_vehicules = $row_vehicules['total_vehicules'];

  $sql_reservations = "SELECT COUNT(*) as total_reservations FROM reservation WHERE statut='En Attente'";
  $stmt_reservations = $pdo->query($sql_reservations);
  $row_reservations = $stmt_reservations->fetch(PDO::FETCH_ASSOC);
  $total_reservations = $row_reservations['total_reservations'];

  $sql_clients = "SELECT COUNT(*) as total_clients FROM utilisateur WHERE role = 'Client'";
  $stmt_clients = $pdo->query($sql_clients);
  $row_clients = $stmt_clients->fetch(PDO::FETCH_ASSOC);
  $total_clients = $row_clients['total_clients'];
} catch (PDOException $e) {
  die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="admin.css" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <title>Tableau de Bord - Location de Véhicules</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Itim&display=swap");
    body {
      font-family: "Itim", cursive;
    }
    .notification {
      opacity: 1;
      transform: translateY(0);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .notification.hidden {
      opacity: 0;
      transform: translateY(-20px);
    }
    /* Modales */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .modal-content {
      background: #1f2937;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 600px;
    }
    
    body.modal-open {
      overflow: hidden;
    }
    /* Styles pour le tableau */
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #e2e8f0;
      padding: 12px;
      text-align: left;
    }
    th {
      background-color: #4a5568;
      color: #ffffff;
      font-weight: bold;
    }
    tr {
      background-color: #ffffff;
    }
    tr:nth-child(even) {
      background-color: #f7fafc;
    }
    tr:hover {
      background-color: #edf2f7;
    }
    .dashboard-card {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .dashboard-card i {
      font-size: 2rem;
      color: #4fd1c5;
    }
    .dashboard-card span {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2d3748;
    }
    .dashboard-card .count {
      font-size: 2rem;
      font-weight: 700;
      color: #4fd1c5;
    }
    /* Styles pour les boutons */
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
    .btn-danger {
      background-color: #e53e3e;
      color: #ffffff;
    }
    .btn-danger:hover {
      background-color: #c53030;
      transform: translateY(-2px);
    }
    /* Styles pour les notifications */
    .notification {
      opacity: 1;
      transform: translateY(0);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .notification.success {
      background-color: #48bb78;
    }
    .notification.error {
      background-color: #e53e3e;
    }
    .notification.hidden {
      opacity: 0;
      transform: translateY(-20px);
    }
    /* Styles pour le menu latéral */
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
    /* Styles pour le header */
    header {
      background-color: #2d3748;
      padding: 20px;
      color: #ffffff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 8px;
    }
    header .logo {
      font-size: 1.5rem;
      font-weight: 700;
      color: #4fd1c5;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      transition: color 0.3s ease, text-shadow 0.3s ease;
    }
    header .logo:hover {
      color: #38a89d;
      text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
    }
    header .logo i {
      margin-right: 10px;
      transition: transform 0.3s ease;
    }
    header .logo:hover i {
      transform: scale(1.1);
    }
    /* Mode Nuit */
    body.dark-mode {
      background-color: #1a202c;
      color: #e2e8f0;
    }
    body.dark-mode .dashboard-card {
      background-color: #2d3748;
      color: #e2e8f0;
    }
    body.dark-mode table {
      background-color: #2d3748;
      color: #e2e8f0;
    }
    body.dark-mode th,
    body.dark-mode td {
      border-color: #4a5568;
      color: #e2e8f0;
    }
    body.dark-mode tr {
      background-color: #2d3748;
    }
    body.dark-mode tr:nth-child(even) {
      background-color: #4a5568;
    }
    body.dark-mode tr:hover {
      background-color: #4a5568;
    }
    body.dark-mode .modal-content {
      background-color: #2d3748;
      color: #e2e8f0;
    }
    body.dark-mode input,
    body.dark-mode select {
      background-color: #4a5568;
      color: #e2e8f0;
      border-color: #4a5568;
    }
    body.dark-mode input:focus,
    body.dark-mode select:focus {
      border-color: #4fd1c5;
      box-shadow: 0 0 0 2px rgba(79, 209, 197, 0.2);
    }
    body.dark-mode .btn-primary {
      background-color: #4fd1c5;
      color: #1a202c;
    }
    body.dark-mode .btn-danger {
      background-color: #e53e3e;
      color: #1a202c;
    }
    body.dark-mode .notification.success {
      background-color: #48bb78;
      color: #1a202c;
    }
    body.dark-mode .notification.error {
      background-color: #e53e3e;
      color: #1a202c;
    }
    
  </style>
</head>
<body class="bg-gray-100 text-gray-900 overflow-x-clip">
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
      <button id="toggleTheme" class="text-gray-100 hover:text-gray-400">
        <i id="themeIcon" class="bx bx-moon text-2xl"></i>
      </button>
      <button onclick="window.location.href='index.html'" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
        Déconnexion
      </button>
    </div>
  </header>

  <div class="flex p-3 gap-4">
    <aside id="sidebar" class="w-42 hidden lg:block rounded-lg bg-gray-800 p-2 py-5 fixed lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-200 ease-in-out">
      <nav class="space-y-4">
        <button onclick="afficherSection('dashboard')" class="flex items-center space-x-3 text-gray-300 hover:bg-gray-700 p-3 rounded-md w-full text-left">
          <i class="bx bx-home-alt text-teal-400"></i>
          <span>Tableau de bord</span>
        </button>
        <button onclick="afficherSection('vehicules')" class="flex items-center space-x-3 text-gray-300 hover:bg-gray-700 p-3 rounded-md w-full text-left">
          <i class="bx bx-car text-teal-400"></i>
          <span>Gestion des Véhicules</span>
        </button>
        <button onclick="afficherSection('reservations')" class="flex items-center space-x-3 text-gray-300 hover:bg-gray-700 p-3 rounded-md w-full text-left">
          <i class="bx bx-calendar text-teal-400"></i>
          <span>Gestion des Réservations</span>
        </button>
        <button onclick="afficherSection('clients')" class="flex items-center space-x-3 text-gray-300 hover:bg-gray-700 p-3 rounded-md w-full text-left">
          <i class="bx bx-user text-teal-400"></i>
          <span>Gestion des Clients</span>
        </button>
      </nav>
    </aside>
    <main class="flex-1 bg-gray-100 flex flex-col gap-4 ml-0 lg:ml-42">
      <!-- Section Tableau de Bord -->
      <section id="dashboard" class="p-4 space-y-6 bg-white flex flex-col rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900">Tableau de Bord</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="dashboard-card">
            <div class="flex items-center gap-2">
              <i class="bx bx-car text-teal-400 text-2xl"></i>
              <span class="text-lg font-semibold">Véhicules</span>
            </div>
            <span class="count"><?php echo htmlspecialchars($total_vehicules); ?></span>
          </div>
          <div class="dashboard-card">
            <div class="flex items-center gap-2">
              <i class="bx bx-calendar text-teal-400 text-2xl"></i>
              <span class="text-lg font-semibold">Réservations</span>
            </div>
            <span class="count"><?php echo htmlspecialchars($total_reservations); ?></span>
          </div>
          <div class="dashboard-card">
            <div class="flex items-center gap-2">
              <i class="bx bx-user text-teal-400 text-2xl"></i>
              <span class="text-lg font-semibold">Clients</span>
            </div>
            <span class="count"><?php echo htmlspecialchars($total_clients); ?></span>
          </div>
        </div>
      </section>

      <!-- Section Véhicules -->
      <section id="vehicules" class="hidden p-4 space-y-6 bg-white flex flex-col rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900">Gestion des Véhicules</h2>
        <div class="flex justify-between items-center">
          <button class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600" onclick="showAddModal()">
            Ajouter un Véhicule
          </button>
          <input type="text" id="searchVehicules" placeholder="Rechercher un véhicule..." class="p-2 rounded-md border border-gray-300">
        </div>
        <table class="w-full bg-white rounded-md overflow-hidden">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Marque</th>
              <th class="px-4 py-2 text-left">Modèle</th>
              <th class="px-4 py-2 text-left">Type</th>
              <th class="px-4 py-2 text-left">Prix par Jour</th>
              <th class="px-4 py-2 text-left">Disponible</th>
              <th class="px-4 py-2 text-left">Nombre de Places</th>
              <th class="px-4 py-2 text-left">Carburant</th>
              <th class="px-4 py-2 text-left">Image</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody id="vehiculesTableBody">
            <!-- Contenu dynamique -->
          </tbody>
        </table>
      </section>

      <!-- Section Réservations -->
      <section id="reservations" class="hidden p-4 space-y-6 bg-white flex flex-col rounded-lg shadow-md">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-900">Gestion des Réservations</h2>
          <input type="text" id="searchReservations" placeholder="Rechercher une réservation..." class="p-2 rounded-md border border-gray-300">
        </div>
        <table class="w-full bg-white rounded-md overflow-hidden">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Client</th>
              <th class="px-4 py-2 text-left">Véhicule</th>
              <th class="px-4 py-2 text-left">Date et heure de début</th>
              <th class="px-4 py-2 text-left">Date et heure de fin</th>
              <th class="px-4 py-2 text-left">Statut</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody id="reservationsTableBody">
            <!-- Contenu dynamique -->
          </tbody>
        </table>
      </section>

      <!-- Section Clients -->
      <section id="clients" class="hidden p-4 space-y-6 bg-white flex flex-col rounded-lg shadow-md">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-900">Gestion des Clients</h2>
          <input type="text" id="searchClients" placeholder="Rechercher un client..." class="p-2 rounded-md border border-gray-300">
        </div>
        <table class="w-full bg-white rounded-md overflow-hidden">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left">Email</th>
              <th class="px-4 py-2 text-left">Téléphone</th>
              <th class="px-4 py-2 text-left">CIN</th>
              <th class="px-4 py-2 text-left">Numéro de permis</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody id="clientsTableBody">
            <!-- Contenu dynamique -->
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <!-- Modale d'Ajout -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Ajouter un Véhicule</h2>
      <form id="form-ajouter-vehicule" action="ajouter_vehicule.php" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input type="text" name="marque" placeholder="Marque" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          <input type="text" name="modele" placeholder="Modèle" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          <select name="type" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
            <option value="Voiture">Voiture</option>
            <option value="Moto">Moto</option>
            <option value="Camion">Camion</option>
          </select>
          <input type="number" name="prixParJour" placeholder="Prix par jour" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          <input type="number" name="nombrePlaces" placeholder="Nombre de places" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          <select name="carburant" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
            <option value="Essence">Essence</option>
            <option value="Diesel">Diesel</option>
            <option value="Electrique">Electrique</option>
            <option value="Hybride">Hybride</option>
          </select>
          <select name="disponible" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
            <option value="Disponible">Disponible</option>
            <option value="Non Disponible">Pas Disponible</option>
          </select>
          <input type="file" name="image" accept="image/*" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        </div>
        <div class="mt-6 flex justify-end space-x-4">
          <button type="button" id="cancelAdd" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600">Ajouter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modale de Suppression -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Confirmer la Suppression</h2>
      <p class="text-gray-300">Êtes-vous sûr de vouloir supprimer ce véhicule ?</p>
      <div class="mt-6 flex justify-end space-x-4">
        <button id="confirmDelete" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Confirmer</button>
        <button id="cancelDelete" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
      </div>
    </div>
  </div>

  <!-- Modale de Modification -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Modifier le Véhicule</h2>
      <form id="editVehicleForm" class="space-y-4">
        <input type="hidden" id="editId" name="id" />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="editMarque" class="block text-gray-300">Marque</label>
            <input type="text" id="editMarque" name="marque" placeholder="Marque" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          </div>
          <div>
            <label for="editModele" class="block text-gray-300">Modèle</label>
            <input type="text" id="editModele" name="modele" placeholder="Modèle" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          </div>
          <div>
            <label for="editType" class="block text-gray-300">Type</label>
            <select id="editType" name="type" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
              <option value="Voiture">Voiture</option>
              <option value="Moto">Moto</option>
              <option value="Camion">Camion</option>
            </select>
          </div>
          <div>
            <label for="editPrixParJour" class="block text-gray-300">Prix par jour</label>
            <input type="number" id="editPrixParJour" name="prixParJour" placeholder="Prix par jour" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          </div>
          <div>
            <label for="editNombrePlaces" class="block text-gray-300">Nombre de places</label>
            <input type="number" id="editNombrePlaces" name="nombrePlaces" placeholder="Nombre de places" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          </div>
          <div>
            <label for="editCarburant" class="block text-gray-300">Carburant</label>
            <select id="editCarburant" name="carburant" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
              <option value="Essence">Essence</option>
              <option value="Diesel">Diesel</option>
              <option value="Electrique">Electrique</option>
              <option value="Hybride">Hybride</option>
            </select>
          </div>
          <div>
            <label for="editDisponible" class="block text-gray-300">Disponible</label>
            <select id="editDisponible" name="disponible" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
              <option value="Disponible">Disponible</option>
              <option value="Non Disponible">Pas Disponible</option>
            </select>
          </div>
          <div>
            <label for="editImage" class="block text-gray-300">Image</label>
            <input type="file" id="editImage" name="image" accept="image/*" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
          </div>
        </div>
        <div class="flex justify-end space-x-4">
          <button type="button" id="cancelEdit" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modale de confirmation d'annulation -->
  <div id="cancelReservationModal" class="modal">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Annuler la Réservation</h2>
      <form id="cancelReservationForm">
        <input type="hidden" id="reservationId" name="reservationId" />
        <div class="space-y-4">
          <label for="reason" class="block text-gray-300">Raison de l'annulation :</label>
          <textarea id="reason" name="reason" rows="4" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" required></textarea>
        </div>
        <div class="mt-6 flex justify-end space-x-4">
          <button type="button" id="cancelCancelReservation" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Confirmer l'annulation</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modale de modification des clients -->
  <div id="editClientModal" class="modal hidden">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Modifier Client</h2>
      <form id="editClientForm" class="space-y-4">
        <input type="hidden" id="editClientEmail" name="email" />
        <label for="editClientTelephone" class="block text-gray-300">Téléphone</label>
        <input type="text" id="editClientTelephone" name="telephone" placeholder="Téléphone" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <label for="editClientCin" class="block text-gray-300">CIN</label>
        <input type="text" id="editClientCin" name="cin" placeholder="CIN" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <label for="editClientNumeroPermis" class="block text-gray-300">Numéro de Permis</label>
        <input type="text" id="editClientNumeroPermis" name="numeroPermis" placeholder="Numéro de Permis" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <div class="flex justify-end space-x-4">
          <button type="button" id="cancelEditClient" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modale de suppression des clients -->
  <div id="deleteClientModal" class="modal hidden">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Supprimer Client</h2>
      <p class="text-gray-300">Êtes-vous sûr de vouloir supprimer ce client ?</p>
      <div class="mt-6 flex justify-end space-x-4">
        <button id="confirmDeleteClient" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Supprimer</button>
        <button id="cancelDeleteClient" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
      </div>
    </div>
  </div>

  <!-- Conteneur pour les notifications -->
  <div id="notification-container" class="fixed top-4 right-4 z-50"></div>

  <script>
    // Fonction pour afficher/masquer les sections
    function afficherSection(sectionId) {
      const sections = document.querySelectorAll("main > section");
      sections.forEach((section) => section.classList.add("hidden"));

      const sectionToShow = document.getElementById(sectionId);
      if (sectionToShow) {
        sectionToShow.classList.remove("hidden");
      } else {
        console.error(`Section avec l'ID "${sectionId}" non trouvée.`);
      }
    }

    // Fonction pour afficher une notification
    function afficherNotification(message, type) {
      const container = document.getElementById("notification-container");
      const notification = document.createElement("div");
      notification.className = `notification p-4 rounded-md ${
        type === "success" ? "bg-green-500" : "bg-red-500"
      } text-white`;
      notification.textContent = message;

      container.appendChild(notification);

      // Masquer et supprimer la notification après 3 secondes
      setTimeout(() => {
        notification.classList.add("hidden");
        setTimeout(() => notification.remove(), 500);
      }, 3000);
    }

    // Fonction pour charger les véhicules
    async function chargerVehicules() {
      try {
        const response = await fetch('fetch_vehicules.php');
        const data = await response.json();

        if (data.status === 'success') {
          const tbody = document.getElementById('vehiculesTableBody');
          if (!tbody) {
            console.error("Le corps du tableau des véhicules n'existe pas.");
            return;
          }
          tbody.innerHTML = ''; // Vider le tableau

          data.data.forEach(vehicule => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td class="px-4 py-2">${vehicule.idVehicule}</td>
              <td class="px-4 py-2">${vehicule.marque}</td>
              <td class="px-4 py-2">${vehicule.modele}</td>
              <td class="px-4 py-2">${vehicule.type}</td>
              <td class="px-4 py-2">${vehicule.prixParJour} Mad</td>
              <td class="px-4 py-2">${vehicule.disponible ? 'Disponible' : 'Non disponible'}</td>
              <td class="px-4 py-2">${vehicule.nombrePlaces}</td>
              <td class="px-4 py-2">${vehicule.carburant}</td>
              <td class="px-4 py-2">
                <img src="uploads/${vehicule.image}" alt="${vehicule.marque} ${vehicule.modele}" class="w-16 h-10 object-cover rounded-md">
              </td>
              <td class="px-4 py-2">
                <button class="bg-blue-500 text-white px-2 py-1 rounded-md edit-btn" data-id="${vehicule.idVehicule}" data-marque="${vehicule.marque}" data-modele="${vehicule.modele}" data-type="${vehicule.type}" data-prix="${vehicule.prixParJour}" data-places="${vehicule.nombrePlaces}" data-carburant="${vehicule.carburant}" data-disponible="${vehicule.disponible}">Modifier</button>
                <button class="bg-red-500 text-white px-2 py-1 rounded-md delete-btn" data-id="${vehicule.idVehicule}">Supprimer</button>
              </td>
            `;
            tbody.appendChild(row);
          });

          // Déclencher la recherche après le chargement des données
          const searchVehicules = document.getElementById('searchVehicules');
          if (searchVehicules) {
            searchVehicules.dispatchEvent(new Event('input'));
          }
        } else {
          console.error('Erreur:', data.message);
          afficherNotification('Erreur lors du chargement des véhicules.', 'error');
        }
      } catch (error) {
        console.error('Erreur:', error);
        afficherNotification('Erreur lors du chargement des véhicules.', 'error');
      }
    }

    // Fonction pour afficher la modale d'ajout
    function showAddModal() {
      const addModal = document.getElementById('addModal');
      if (addModal) {
        addModal.style.display = 'flex';
        document.body.classList.add('modal-open');
      } else {
        console.error("La modale d'ajout n'existe pas.");
      }
    }

    // Fonction pour cacher la modale d'ajout
    function hideAddModal() {
      const addModal = document.getElementById('addModal');
      if (addModal) {
        addModal.style.display = 'none';
        document.body.classList.remove('modal-open');
      }
    }

    // Écouteur d'événement pour annuler l'ajout
    document.getElementById('cancelAdd')?.addEventListener('click', hideAddModal);

    // Écouteur d'événement pour le formulaire d'ajout
    document.getElementById('form-ajouter-vehicule')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);

      try {
        const response = await fetch('ajouter_vehicule.php', {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();

        if (data.status === 'success') {
          afficherNotification('Véhicule ajouté avec succès !', 'success');
          e.target.reset();
          chargerVehicules();
          hideAddModal();
        } else {
          afficherNotification('Veuillez saisir les champs corrects du véhicule.', 'error');
        }
      } catch (error) {
        console.error('Erreur:', error);
        afficherNotification('Erreur lors de l\'ajout du véhicule.', 'error');
      }
    });

    // Variables pour stocker l'ID du véhicule à supprimer ou à modifier
    let vehicleIdToDelete = null;
    let vehicleIdToEdit = null;

    // Fonction pour afficher la modale de suppression
    function supprimerVehicule(id) {
      vehicleIdToDelete = id;
      const deleteModal = document.getElementById('deleteModal');
      if (deleteModal) {
        deleteModal.style.display = 'flex';
        document.body.classList.add('modal-open');
      } else {
        console.error("La modale de suppression n'existe pas.");
      }
    }

    // Fonction pour cacher la modale de suppression
    function hideDeleteModal() {
      const deleteModal = document.getElementById('deleteModal');
      if (deleteModal) {
        deleteModal.style.display = 'none';
        document.body.classList.remove('modal-open');
      }
    }

    // Fonction pour afficher la modale de modification
    function showEditModal(vehicle) {
      vehicleIdToEdit = vehicle.idVehicule;
      const editModal = document.getElementById('editModal');
      if (editModal) {
        document.getElementById('editId').value = vehicle.idVehicule;
        document.getElementById('editMarque').value = vehicle.marque;
        document.getElementById('editModele').value = vehicle.modele;
        document.getElementById('editType').value = vehicle.type;
        document.getElementById('editPrixParJour').value = vehicle.prixParJour;
        document.getElementById('editNombrePlaces').value = vehicle.nombrePlaces;
        document.getElementById('editCarburant').value = vehicle.carburant;
        document.getElementById('editDisponible').value = vehicle.disponible ? 'Disponible' : 'Non Disponible';
        editModal.style.display = 'flex';
        document.body.classList.add('modal-open');
      } else {
        console.error("La modale de modification n'existe pas.");
      }
    }

    // Fonction pour cacher la modale de modification
    function hideEditModal() {
      const editModal = document.getElementById('editModal');
      if (editModal) {
        editModal.style.display = 'none';
        document.body.classList.remove('modal-open');
      }
    }

    // Écouteur d'événement pour annuler la suppression
    document.getElementById('cancelDelete')?.addEventListener('click', hideDeleteModal);

    // Écouteur d'événement pour annuler la modification
    document.getElementById('cancelEdit')?.addEventListener('click', hideEditModal);

    // Écouteurs d'événements pour les boutons de suppression
    document.body.addEventListener('click', (e) => {
      if (e.target.classList.contains('delete-btn')) {
        supprimerVehicule(e.target.dataset.id);
      }
    });

    // Écouteurs d'événements pour les boutons de modification
    document.body.addEventListener('click', (e) => {
      if (e.target.classList.contains('edit-btn')) {
        const vehicle = {
          idVehicule: e.target.dataset.id,
          marque: e.target.dataset.marque,
          modele: e.target.dataset.modele,
          type: e.target.dataset.type,
          prixParJour: e.target.dataset.prix,
          nombrePlaces: e.target.dataset.places,
          carburant: e.target.dataset.carburant,
          disponible: e.target.dataset.disponible === 'true'
        };
        showEditModal(vehicle);
      }
    });

    // Écouteur d'événement pour le formulaire de modification
    document.getElementById('editVehicleForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);

      try {
        const response = await fetch('modifier_vehicule.php', {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();

        if (data.status === 'success') {
          afficherNotification('Véhicule modifié avec succès !', 'success');
          chargerVehicules();
          hideEditModal();
        } else {
          afficherNotification('Veuillez entrer des champs valides pour modifier le véhicule.', 'error');
        }
      } catch (error) {
        console.error('Erreur:', error);
        afficherNotification('Erreur lors de la modification du véhicule.', 'error');
      }
    });

    // Écouteur d'événement pour confirmer la suppression
    document.getElementById('confirmDelete')?.addEventListener('click', async () => {
      if (vehicleIdToDelete) {
        try {
          const response = await fetch('supprimer_vehicule.php', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: vehicleIdToDelete }),
          });
          const data = await response.json();

          if (data.status === 'success') {
            afficherNotification('Véhicule supprimé avec succès !', 'success');
            chargerVehicules();
          } else {
            afficherNotification('Erreur lors de la suppression du véhicule, car il est déjà réservé.', 'error');
          }
        } catch (error) {
          console.error('Erreur:', error);
          afficherNotification('Erreur lors de la suppression du véhicule, car il est déjà réservé.', 'error');
        }
        hideDeleteModal();
      }
    });

    // Fonction pour activer le mode nuit
    function enableDarkMode() {
      document.body.classList.add('dark-mode');
      document.getElementById('themeIcon')?.classList.remove('bx-moon');
      document.getElementById('themeIcon')?.classList.add('bx-sun');
      localStorage.setItem('theme', 'dark');
    }

    // Fonction pour activer le mode clair
    function enableLightMode() {
      document.body.classList.remove('dark-mode');
      document.getElementById('themeIcon')?.classList.remove('bx-sun');
      document.getElementById('themeIcon')?.classList.add('bx-moon');
      localStorage.setItem('theme', 'light');
    }

    // Vérifier la préférence de l'utilisateur au chargement de la page
    function loadTheme() {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark') {
        enableDarkMode();
      } else {
        enableLightMode();
      }
    }

    // Basculer entre les modes
    document.getElementById('toggleTheme')?.addEventListener('click', () => {
      if (document.body.classList.contains('dark-mode')) {
        enableLightMode();
      } else {
        enableDarkMode();
      }
    });

    // Fonction pour charger les réservations
    async function chargerReservations() {
      try {
        const response = await fetch('fetch_reservations.php');
        const data = await response.json();

        if (data.status === 'success') {
          const tbody = document.getElementById('reservationsTableBody');
          if (!tbody) {
            console.error("Le corps du tableau des réservations n'existe pas.");
            return;
          }
          tbody.innerHTML = ''; // Vider le tableau

          data.data.forEach(reservation => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td class="px-4 py-2">${reservation.idReservation}</td>
              <td class="px-4 py-2">${reservation.email || 'Non disponible'}</td>
              <td class="px-4 py-2">${reservation.marque || ''} ${reservation.modele || ''}</td>
              <td class="px-4 py-2">${reservation.dateDebut}  </td>
              <td class="px-4 py-2">${reservation.dateFin}  </td>
              <td class="px-4 py-2">${reservation.statut}</td>
              <td class="px-4 py-2">
                ${reservation.statut !== 'Confirmée' && reservation.statut !== 'Annulée' ? `
                  <button onclick="confirmReservation(${reservation.idReservation})" class="bg-green-500 text-white px-2 py-1 rounded-md hover:bg-green-600">Confirmer</button>
                  <button onclick="showCancelReservationModal(${reservation.idReservation})" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">Annuler</button>
                ` : ''}
              </td>
            `;
            tbody.appendChild(row);
          });

          // Déclencher la recherche après le chargement des données
          const searchReservations = document.getElementById('searchReservations');
          if (searchReservations) {
            searchReservations.dispatchEvent(new Event('input'));
          }
        } else {
          console.error('Erreur du backend :', data.message);
        }
      } catch (error) {
        console.error('Erreur lors de la récupération des réservations :', error);
      }
    }

    // Fonction pour confirmer une réservation
    async function confirmReservation(reservationId) {
      try {
        const response = await fetch('confirm_reservation.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: reservationId }),
        });
        const data = await response.json();

        if (data.success) {
          afficherNotification('Réservation confirmée avec succès', 'success');
          chargerReservations(); // Recharger les réservations
        } else {
          afficherNotification('Erreur lors de la confirmation de la réservation', 'error');
        }
      } catch (error) {
        console.error('Erreur :', error);
        afficherNotification('Erreur lors de la confirmation de la réservation', 'error');
      }
    }

    // Fonction pour afficher le modal d'annulation
    function showCancelReservationModal(reservationId) {
      const cancelReservationModal = document.getElementById('cancelReservationModal');
      if (cancelReservationModal) {
        document.getElementById('reservationId').value = reservationId;
        cancelReservationModal.style.display = 'flex';
      } else {
        console.error("Le modal d'annulation n'existe pas.");
      }
    }

    // Fonction pour annuler une réservation
    document.getElementById('cancelReservationForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const reservationId = document.getElementById('reservationId').value;
      const reason = document.getElementById('reason').value;

      try {
        const response = await fetch('cancel_reservation.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: reservationId, reason: reason }),
        });
        const data = await response.json();

        if (data.success) {
          afficherNotification('Réservation annulée avec succès', 'success');
          chargerReservations(); // Recharger les réservations
          document.getElementById('cancelReservationModal').style.display = 'none';
        } else {
          afficherNotification('Erreur lors de l\'annulation de la réservation', 'error');
        }
      } catch (error) {
        console.error('Erreur :', error);
        afficherNotification('Erreur lors de l\'annulation de la réservation', 'error');
      }
    });

    // Fonction pour fermer le modal
    document.getElementById('cancelCancelReservation')?.addEventListener('click', () => {
      const cancelReservationModal = document.getElementById('cancelReservationModal');
      if (cancelReservationModal) {
        cancelReservationModal.style.display = 'none';
      }
    });

    // Fonction pour afficher le modal de confirmation de réservation
    function showConfirmReservationModal(reservationId) {
      const confirmReservationModal = document.getElementById('confirmReservationModal');
      if (confirmReservationModal) {
        confirmReservationModal.classList.remove('hidden');
        document.getElementById('confirmReservationButton').onclick = () => confirmReservation(reservationId);
      } else {
        console.error("Le modal de confirmation de réservation n'existe pas.");
      }
    }

    // Fonction pour fermer le modal de confirmation de réservation
    document.getElementById('confirmCancelReservation')?.addEventListener('click', () => {
      const confirmReservationModal = document.getElementById('confirmReservationModal');
      if (confirmReservationModal) {
        confirmReservationModal.classList.add('hidden');
      }
    });

    // GESTION DES CLIENTS

    // Fonction pour charger les clients depuis la base de données
    async function chargerClients() {
      try {
        const response = await fetch('fetch_clients.php');
        const data = await response.json();

        if (data.status === 'success') {
          const tbody = document.getElementById('clientsTableBody');
          if (!tbody) {
            console.error("Le corps du tableau des clients n'existe pas.");
            return;
          }
          tbody.innerHTML = ''; // Vider le tableau

          data.data.forEach(client => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td class="px-4 py-2">${client.email}</td>
              <td class="px-4 py-2">${client.telephone}</td>
              <td class="px-4 py-2">${client.cin}</td>
              <td class="px-4 py-2">${client.numeroPermis}</td>
              <td class="px-4 py-2">
                <button class="bg-blue-500 text-white px-2 py-1 rounded-md edit-client-btn" 
                  data-email="${client.email}" 
                  data-telephone="${client.telephone}" 
                  data-cin="${client.cin}" 
                  data-numero-permis="${client.numeroPermis}">
                  Modifier
                </button>
                <button class="bg-red-500 text-white px-2 py-1 rounded-md delete-client-btn" 
                  data-email="${client.email}">
                  Supprimer
                </button>
              </td>
            `;
            tbody.appendChild(row);
          });

          // Déclencher la recherche après le chargement des données
          const searchClients = document.getElementById('searchClients');
          if (searchClients) {
            searchClients.dispatchEvent(new Event('input'));
          }
        } else {
          console.error('Erreur:', data.message);
          afficherNotification('Erreur lors du chargement des clients', 'error');
        }
      } catch (error) {
        console.error('Erreur lors du chargement des clients:', error);
        afficherNotification('Erreur lors du chargement des clients', 'error');
      }
    }

    // Fonction pour afficher la modale de modification des clients
    function showEditClientModal(client) {
      document.getElementById('editClientEmail').value = client.email;
      document.getElementById('editClientTelephone').value = client.telephone;
      document.getElementById('editClientCin').value = client.cin;
      document.getElementById('editClientNumeroPermis').value = client.numeroPermis;
      document.getElementById('editClientModal').style.display = 'flex';
      document.body.classList.add('modal-open');
    }

    // Fonction pour masquer la modale de modification des clients
    function hideEditClientModal() {
      document.getElementById('editClientModal').style.display = 'none';
      document.body.classList.remove('modal-open');
    }

    // Fonction pour afficher la modale de suppression des clients
    function showDeleteClientModal(email) {
      document.getElementById('deleteClientModal').style.display = 'flex';
      document.body.classList.add('modal-open');
      document.getElementById('confirmDeleteClient').dataset.email = email;
    }

    // Fonction pour masquer la modale de suppression des clients
    function hideDeleteClientModal() {
      document.getElementById('deleteClientModal').style.display = 'none';
      document.body.classList.remove('modal-open');
    }

    // Écouteur d'événement pour les boutons de modification des clients
    document.body.addEventListener('click', (e) => {
      if (e.target.classList.contains('edit-client-btn')) {
        const client = {
          email: e.target.dataset.email,
          telephone: e.target.dataset.telephone,
          cin: e.target.dataset.cin,
          numeroPermis: e.target.dataset.numeroPermis
        };
        showEditClientModal(client);
      }
    });

    // Écouteur d'événement pour les boutons de suppression des clients
    document.body.addEventListener('click', (e) => {
      if (e.target.classList.contains('delete-client-btn')) {
        const email = e.target.dataset.email;
        showDeleteClientModal(email);
      }
    });

    // Écouteur d'événement pour annuler la modification des clients
    document.getElementById('cancelEditClient')?.addEventListener('click', hideEditClientModal);

    // Écouteur d'événement pour annuler la suppression des clients
    document.getElementById('cancelDeleteClient')?.addEventListener('click', hideDeleteClientModal);

    // Écouteur d'événement pour le formulaire de modification des clients
    document.getElementById('editClientForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = {
        email: document.getElementById('editClientEmail').value,
        telephone: document.getElementById('editClientTelephone').value,
        cin: document.getElementById('editClientCin').value,
        numeroPermis: document.getElementById('editClientNumeroPermis').value
      };

      try {
        const response = await fetch('modifier_client.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(formData),
        });
        const data = await response.json();

        if (data.status === 'success') {
          afficherNotification('Client modifié avec succès !', 'success');
          chargerClients(); // Rafraîchir la liste des clients
          hideEditClientModal();
        } else {
          afficherNotification('Erreur : ' + data.message, 'error');
        }
      } catch (error) {
        console.error('Erreur:', error);
        afficherNotification('Erreur lors de la modification du client.', 'error');
      }
    });

    // Écouteur d'événement pour confirmer la suppression des clients
    document.getElementById('confirmDeleteClient')?.addEventListener('click', async () => {
      const email = document.getElementById('confirmDeleteClient').dataset.email;

      if (email) {
        try {
          const response = await fetch('supprimer_client.php', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email }),
          });
          const data = await response.json();

          if (data.status === 'success') {
            afficherNotification('Client supprimé avec succès !', 'success');
            chargerClients(); // Rafraîchir la liste des clients
          } else {
            afficherNotification(data.message, 'error');
          }
        } catch (error) {
          console.error('Erreur:', error);
          afficherNotification('Erreur lors de la suppression du client.', 'error');
        }
        hideDeleteClientModal();
      }
    });

    // Fonction pour activer la recherche après le chargement des données
    function activerRecherche() {
      const searchVehicules = document.getElementById('searchVehicules');
      if (searchVehicules) {
        searchVehicules.addEventListener('input', function() {
          const searchTerm = this.value.toLowerCase();
          const rows = document.querySelectorAll('#vehiculesTableBody tr');
          rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
          });
        });
      }

      const searchReservations = document.getElementById('searchReservations');
      if (searchReservations) {
        searchReservations.addEventListener('input', function() {
          const searchTerm = this.value.toLowerCase();
          const rows = document.querySelectorAll('#reservationsTableBody tr');
          rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
          });
        });
      }

      const searchClients = document.getElementById('searchClients');
      if (searchClients) {
        searchClients.addEventListener('input', function() {
          const searchTerm = this.value.toLowerCase();
          const rows = document.querySelectorAll('#clientsTableBody tr');
          rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
          });
        });
      }
    }

    // Charger le thème et les données au démarrage
    window.onload = () => {
      loadTheme();
      chargerVehicules();
      chargerReservations();
      afficherSection('dashboard'); // Afficher le tableau de bord par défaut
      chargerClients();
      activerRecherche(); // Activer la recherche après le chargement des données
    };
  </script>
</body>
</html>
