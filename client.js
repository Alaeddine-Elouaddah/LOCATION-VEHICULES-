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
  function openModal(vehiculeId, prixParJour, reservedDates) {
    document.getElementById('vehiculeId').value = vehiculeId;
    document.getElementById('reservationModal').style.display = 'flex';

    // Récupérer les éléments du formulaire
    const dateDebutInput = document.getElementById('dateDebut');
    const dateFinInput = document.getElementById('dateFin');
    const montantTotalInput = document.getElementById('montantTotal');

    // Convertir les dates réservées en un tableau de dates
    const reservedDatesArray = reservedDates.map(range => {
      return {
        start: new Date(range.dateDebut),
        end: new Date(range.dateFin),
      };
    });

    // Configurer Flatpickr pour désactiver les dates réservées
    flatpickr(dateDebutInput, {
      enableTime: true,
      minDate: "today",
      disable: reservedDatesArray.map(range => ({ from: range.start, to: range.end })),
      onChange: function(selectedDates, dateStr) {
        dateFinInput._flatpickr.set("minDate", selectedDates[0]);
        calculerMontantTotal();
      },
    });

    flatpickr(dateFinInput, {
      enableTime: true,
      minDate: "today",
      disable: reservedDatesArray.map(range => ({ from: range.start, to: range.end })),
      onChange: function(selectedDates, dateStr) {
        calculerMontantTotal();
      },
    });

    // Fonction pour calculer le montant total
    function calculerMontantTotal() {
      const dateDebutValue = dateDebutInput.value;
      const dateFinValue = dateFinInput.value;

      if (dateDebutValue && dateFinValue) {
        const dateDebutDate = new Date(dateDebutValue);
        const dateFinDate = new Date(dateFinValue);

        // Vérifier que la date de fin est après la date de début
        if (dateFinDate <= dateDebutDate) {
          montantTotalInput.value = "0.00"; // Montant invalide si les dates sont incorrectes
          return;
        }

        // Calculer la différence en millisecondes
        const diffTime = Math.abs(dateFinDate - dateDebutDate);

        // Convertir la différence en jours
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        // Calculer le montant total
        const montantTotal = diffDays * prixParJour;
        montantTotalInput.value = montantTotal.toFixed(2); // Afficher le montant total
      } else {
        montantTotalInput.value = "0.00"; // Réinitialiser si une date est manquante
      }
    }

    // Initialiser le montant total à 0.00
    montantTotalInput.value = "0.00";
  }

  // Fermer la modale de réservation
  function closeModal() {
    document.getElementById('reservationModal').style.display = 'none';
  }

  // Soumettre le formulaire de réservation
  document.getElementById('reservationForm').onsubmit = async function (e) {
    e.preventDefault();
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
      alert('Réservation effectuée avec succès !'); // Afficher une alerte
      closeModal(); // Fermer le modal
      setTimeout(() => {
        location.reload(); // Recharger la page pour afficher les nouvelles réservations
      }, 1000); // Attendre 1 seconde avant de recharger
    } else {
      alert(result.message || 'Erreur lors de la réservation.'); // Afficher une alerte d'erreur
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
    if (confirm("Êtes-vous sûr de vouloir annuler cette réservation ?")) {
      fetch('client.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: reservationId, reason: "Annulation par l'utilisateur" }),
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert("Réservation annulée avec succès."); // Afficher une alerte
            window.location.reload(); // Recharger la page pour mettre à jour la liste
          } else {
            alert("Erreur lors de l'annulation de la réservation."); // Afficher une alerte d'erreur
          }
        })
        .catch(error => console.error('Erreur :', error));
    }
  }