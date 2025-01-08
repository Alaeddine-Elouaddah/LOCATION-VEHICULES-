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
                    <td class="px-4 py-2">Dt:${reservation.dateDebut} Tps:${reservation.dateDebut} </td>
                    <td class="px-4 py-2">Dt:${reservation.dateFin} Tps:${reservation.dateFin} </td>
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

// Charger le thème et les données au démarrage
window.onload = () => {
    loadTheme();
    chargerVehicules();
    chargerReservations();
    afficherSection('dashboard'); // Afficher le tableau de bord par défaut
    chargerClients();
};