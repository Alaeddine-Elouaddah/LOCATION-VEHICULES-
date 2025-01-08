// Sélection des éléments nécessaires
const conteneur = document.getElementById('conteneur');
const boutonInscription = document.getElementById('inscription');
const boutonConnexion = document.getElementById('connexion');
const formInscription = document.getElementById('formInscription');
const formConnexion = document.getElementById('formConnexion');



// Gestion des boutons pour basculer entre Connexion et Inscription
boutonInscription.addEventListener('click', () => {
    conteneur.classList.add("actif");
});

boutonConnexion.addEventListener('click', () => {
    conteneur.classList.remove("actif");
});
document.getElementById('formInscription').addEventListener('submit', (event) => {
    event.preventDefault(); // Empêche la soumission réelle du formulaire

    // Simuler la fin de l'inscription avec une animation
    let successMessage = document.getElementById('successMessage');
    successMessage.style.display = 'block'; // Assurez-vous que le message est visible

    // Ajoutez la classe "show" pour déclencher l'animation
    setTimeout(() => {
        successMessage.classList.add('show');
    }, 10); // Attendez un peu pour que les styles de base s'appliquent

    // Après l'animation, rediriger vers la page client après 3 secondes
    setTimeout(() => {
        window.location.href = 'client.html'; // Rediriger vers la page client
    }, 3000); // 3 secondes après la fin de l'animation
});