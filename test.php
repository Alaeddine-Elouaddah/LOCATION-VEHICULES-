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

  $sql_reservations = "SELECT COUNT(*) as total_reservations FROM reservation";
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
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 1000;
      background: rgba(0, 0, 0, 0.5); /* Fond semi-transparent */
      display: none; /* Masqué par défaut */
    }
    .modal-content {
      background: #1f2937; /* Couleur de fond de la modale */
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    /* Empêcher le fond de disparaître */
    body.modal-open {
      overflow: hidden; /* Empêcher le défilement */
    }
  </style>
</head>
<body class="bg-gray-900 text-gray-100 overflow-x-clip">
  <header class="p-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <button id="menuButton" class="text-gray-100 text-3xl lg:hidden hover:text-gray-400" aria-label="Ouvrir le menu">
        <i class="bx bx-menu"></i>
      </button>
      <div class="flex items-center gap-2 text-teal-400 cursor-pointer">
        <i class="bx bx-car text-3xl"></i>
        <span class="text-xl font-semibold">Location Auto</span>
      </div>
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

    <main class="flex-1 bg-gray-900 flex flex-col gap-4 ml-0 lg:ml-42">
      <!-- Section Tableau de Bord -->
      <section id="dashboard" class="p-4 space-y-6 bg-gray-800 flex flex-col rounded-lg">
        <h2 class="text-xl font-semibold text-gray-100">Tableau de Bord</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="bg-gray-700 p-5 rounded-md flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="bx bx-car text-teal-400 text-2xl"></i>
              <span class="text-lg font-semibold">Véhicules</span>
            </div>
            <span class="text-xl font-bold"><?php echo htmlspecialchars($total_vehicules); ?></span>
          </div>
          <div class="bg-gray-700 p-5 rounded-md flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="bx bx-calendar text-teal-400 text-2xl"></i>
              <span class="text-lg font-semibold">Réservations</span>
            </div>
            <span class="text-xl font-bold"><?php echo htmlspecialchars($total_reservations); ?></span>
          </div>
          <div class="bg-gray-700 p-5 rounded-md flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="bx bx-user text-teal-400 text-2xl"></i>
              <span class="text-lg font-semibold">Clients</span>
            </div>
            <span class="text-xl font-bold"><?php echo htmlspecialchars($total_clients); ?></span>
          </div>
        </div>
      </section>

      <!-- Section Véhicules -->
      <section id="vehicules" class="hidden p-4 space-y-6 bg-gray-800 flex flex-col rounded-lg">
        <h2 class="text-xl font-semibold text-gray-100">Gestion des Véhicules</h2>
        <button class="bg-teal-500 text-gray-900 px-4 py-2 rounded-md hover:bg-teal-600" onclick="showAddModal()">
          Ajouter un Véhicule
        </button>
        <table class="table-auto w-full bg-gray-700 rounded-md overflow-hidden">
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
    </main>
  </div>

  <!-- Modale d'Ajout -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Ajouter un Véhicule</h2>
      <form id="addVehicleForm" class="space-y-4" action="ajouter_vehicule.php" method="POST" enctype="multipart/form-data">
        <input type="text" id="addMarque" name="marque" placeholder="Marque" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <input type="text" id="addModele" name="modele" placeholder="Modèle" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <select id="addType" name="type" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
          <option value="Voiture">Voiture</option>
          <option value="Moto">Moto</option>
          <option value="Camion">Camion</option>
        </select>
        <input type="number" id="addPrixParJour" name="prixParJour" placeholder="Prix par jour" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <input type="number" id="addNombrePlaces" name="nombrePlaces" placeholder="Nombre de places" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <select id="addCarburant" name="carburant" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
          <option value="Essence">Essence</option>
          <option value="Diesel">Diesel</option>
          <option value="Electrique">Electrique</option>
          <option value="Hybride">Hybride</option>
        </select>
        <select id="addDisponible" name="disponible" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
          <option value="Disponible">Disponible</option>
          <option value="Non Disponible">Pas Disponible</option>
        </select>
        <input type="file" id="addImage" name="image" accept="image/*" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <div class="flex justify-end space-x-4">
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
        <input type="text" id="editMarque" name="marque" placeholder="Marque" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <input type="text" id="editModele" name="modele" placeholder="Modèle" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <select id="editType" name="type" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
          <option value="Voiture">Voiture</option>
          <option value="Moto">Moto</option>
          <option value="Camion">Camion</option>
        </select>
        <input type="number" id="editPrixParJour" name="prixParJour" placeholder="Prix par jour" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <input type="number" id="editNombrePlaces" name="nombrePlaces" placeholder="Nombre de places" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <select id="editCarburant" name="carburant" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
          <option value="Essence">Essence</option>
          <option value="Diesel">Diesel</option>
          <option value="Electrique">Electrique</option>
          <option value="Hybride">Hybride</option>
        </select>
        <select id="editDisponible" name="disponible" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full">
          <option value="Disponible">Disponible</option>
          <option value="Non Disponible">Pas Disponible</option>
        </select>
        <input type="file" id="editImage" name="image" accept="image/*" class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        <div class="flex justify-end space-x-4">
          <button type="button" id="cancelEdit" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <div id="notification-container" class="fixed top-4 right-4 space-y-4 z-50"></div>

  <script>
    // Fonctions pour afficher/masquer les sections
    function afficherSection(sectionId) {
      const sections = document.querySelectorAll("main > section");
      sections.forEach((section) => {
        section.classList.add("hidden");
      });
      document.getElementById(sectionId).classList.remove("hidden");
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
          const vehicules = data.data;
          const tbody = document.getElementById('vehiculesTableBody');
          tbody.innerHTML = ''; // Vider le tableau

          vehicules.forEach(vehicule => {
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
      document.getElementById('addModal').style.display = 'block';
      document.body.classList.add('modal-open');
    }

    // Fonction pour cacher la modale d'ajout
    function hideAddModal() {
      document.getElementById('addModal').style.display = 'none';
      document.body.classList.remove('modal-open');
    }

    // Écouteur d'événement pour annuler l'ajout
    document.getElementById('cancelAdd').addEventListener('click', hideAddModal);

    // Ajouter un véhicule
    document.getElementById('addVehicleForm').addEventListener('submit', async (e) => {
      e.preventDefault(); // Empêcher le rechargement de la page

      const formData = new FormData(e.target); // Récupérer les données du formulaire

      try {
        const response = await fetch('ajouter_vehicule.php', {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();

        if (data.status === 'success') {
          afficherNotification('Véhicule ajouté avec succès !', 'success');
          e.target.reset(); // Réinitialiser le formulaire
          chargerVehicules(); // Recharger les véhicules dans le tableau
          hideAddModal(); // Cacher la modale après l'ajout
        } else {
          afficherNotification('Erreur lors de l\'ajout du véhicule.', 'error');
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
      document.getElementById('deleteModal').style.display = 'block';
      document.body.classList.add('modal-open'); // Ajouter une classe pour empêcher le défilement
    }

    // Fonction pour cacher la modale de suppression
    function hideDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
      document.body.classList.remove('modal-open'); // Retirer la classe pour permettre le défilement
    }

    // Fonction pour afficher la modale de modification
    function showEditModal(vehicle) {
      vehicleIdToEdit = vehicle.idVehicule;
      document.getElementById('editId').value = vehicle.idVehicule;
      document.getElementById('editMarque').value = vehicle.marque;
      document.getElementById('editModele').value = vehicle.modele;
      document.getElementById('editType').value = vehicle.type;
      document.getElementById('editPrixParJour').value = vehicle.prixParJour;
      document.getElementById('editNombrePlaces').value = vehicle.nombrePlaces;
      document.getElementById('editCarburant').value = vehicle.carburant;
      document.getElementById('editDisponible').value = vehicle.disponible ? 'Disponible' : 'Non Disponible';
      document.getElementById('editModal').style.display = 'block';
      document.body.classList.add('modal-open'); // Ajouter une classe pour empêcher le défilement
    }

    // Fonction pour cacher la modale de modification
    function hideEditModal() {
      document.getElementById('editModal').style.display = 'none';
      document.body.classList.remove('modal-open'); // Retirer la classe pour permettre le défilement
    }

    // Écouteur d'événement pour annuler la suppression
    document.getElementById('cancelDelete').addEventListener('click', hideDeleteModal);

    // Écouteur d'événement pour annuler la modification
    document.getElementById('cancelEdit').addEventListener('click', hideEditModal);

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
    document.getElementById('editVehicleForm').addEventListener('submit', async (e) => {
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
          afficherNotification('Erreur lors de la modification du véhicule.', 'error');
        }
      } catch (error) {
        console.error('Erreur:', error);
        afficherNotification('Erreur lors de la modification du véhicule.', 'error');
      }
    });

    // Écouteur d'événement pour confirmer la suppression
    document.getElementById('confirmDelete').addEventListener('click', async () => {
      if (vehicleIdToDelete) {
        try {
          const response = await fetch(`supprimer_vehicule.php`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: vehicleIdToDelete }), // Envoyer l'ID dans le corps de la requête
          });
          const data = await response.json();

          if (data.status === 'success') {
            afficherNotification('Véhicule supprimé avec succès !', 'success');
            chargerVehicules(); // Recharger les véhicules dans le tableau
          } else {
            afficherNotification('Erreur lors de la suppression du véhicule, car il est déjà réservé..', 'error');
          }
        } catch (error) {
          console.error('Erreur:', error);
          afficherNotification('Erreur lors de la suppression du véhicule, car il est déjà réservé..', 'error');
        }
        hideDeleteModal(); // Cacher la modale après la suppression
      }
    });

    // Charger les véhicules au démarrage
    window.onload = chargerVehicules;
  </script>
</body>
</html>