<?php
// Connexion à la base de données et récupération des données
$host = '127.0.0.1';
$dbname = 'locationvoiture';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Récupérer les données des véhicules disponibles
  $sql_vehicules = "SELECT * FROM vehicule WHERE disponible = 'Disponible'";
  $stmt_vehicules = $pdo->query($sql_vehicules);
  $vehicules = $stmt_vehicules->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="client.css" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
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
  </style>
</head>
<body class="bg-gray-100 text-gray-900">
  <header class="p-4 flex justify-between items-center bg-gray-800">
    <div class="flex items-center gap-2">
      <div class="flex items-center gap-4 text-purple-600 cursor-pointer logo hover:text-purple-800 transition-all duration-300">
        <i class="bx bx-car text-3xl"></i>
        <span class="text-xl font-semibold">Location Auto</span>
      </div>
    </div>
    <div class="flex items-center gap-4">
      <button onclick="window.location.href='index.html'" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
        Déconnexion
      </button>
    </div>
  </header>

  <main class="p-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Véhicules Disponibles</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($vehicules as $vehicule): ?>
        <div class="vehicle-card">
          <img src="uploads/<?php echo htmlspecialchars($vehicule['image']); ?>" />
          <h3 class="mt-4"><?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?></h3>
          <p class="mt-2"><i class="bx bx-car"></i> Type: <?php echo htmlspecialchars($vehicule['type']); ?></p>
          <p class="mt-2"><i class="bx bx-money"></i> Prix par jour: <?php echo htmlspecialchars($vehicule['prixParJour']); ?> Mad </p>
          <p class="mt-2"><i class="bx bx-user"></i> Places: <?php echo htmlspecialchars($vehicule['nombrePlaces']); ?></p>
          <p class="mt-2"><i class="bx bx-gas-pump"></i> Carburant: <?php echo htmlspecialchars($vehicule['carburant']); ?></p>
          <button onclick="reserverVehicule(<?php echo htmlspecialchars($vehicule['id']); ?>)" class="btn btn-primary mt-4 w-full">Réserver</button>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Modale de Réservation -->
  <div id="reservationModal" class="modal hidden">
    <div class="modal-content">
      <h2 class="text-xl font-semibold text-gray-100 mb-4">Réserver un Véhicule</h2>
      <form id="reservationForm" class="space-y-4">
        <input type="hidden" id="vehiculeId" name="vehiculeId" />
        <div>
          <label for="dateDebut" class="block text-gray-300">Date de début</label>
          <input type="datetime-local" id="dateDebut" name="dateDebut" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        </div>
        <div>
          <label for="joursLocation" class="block text-gray-300">Nombre de jours de location</label>
          <input type="number" id="joursLocation" name="joursLocation" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        </div>
        <div>
          <label for="dateRetour" class="block text-gray-300">Date de retour</label>
          <input type="datetime-local" id="dateRetour" name="dateRetour" required class="p-2 rounded-md bg-gray-700 text-gray-100 w-full" />
        </div>
        <div class="flex justify-end space-x-4">
          <button type="button" id="cancelReservation" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Annuler</button>
          <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600">Réserver</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function reserverVehicule(vehiculeId) {
      document.getElementById('vehiculeId').value = vehiculeId;
      document.getElementById('reservationModal').classList.remove('hidden');
    }

    document.getElementById('cancelReservation').addEventListener('click', function() {
      document.getElementById('reservationModal').classList.add('hidden');
    });

    document.getElementById('joursLocation').addEventListener('change', function() {
      const dateDebut = document.getElementById('dateDebut').value;
      const joursLocation = parseInt(this.value);
      if (dateDebut && joursLocation) {
        const dateDebutObj = new Date(dateDebut);
        const dateRetourObj = new Date(dateDebutObj.getTime() + joursLocation * 24 * 60 * 60 * 1000);
        const dateRetour = dateRetourObj.toISOString().slice(0, 16);
        document.getElementById('dateRetour').value = dateRetour;
      }
    });

    document.getElementById('reservationForm').addEventListener('submit', function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      fetch('reserver_vehicule.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Réservation effectuée avec succès !');
          window.location.reload();
        } else {
          alert('Erreur lors de la réservation : ' + data.message);
        }
      })
      .catch(error => {
        console.error('Erreur:', error);
      });
    });
  </script>
</body>
</html>