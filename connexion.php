<?php
session_start();
include('db.php'); // Connexion à la base de données

// Constantes pour les rôles
define('ROLE_ADMIN', 'Admin');
define('ROLE_CLIENT', 'Client');


// Initialiser une variable pour afficher un message d'erreur ou de succès
$message = "";

// Fonction pour sécuriser et valider les entrées
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Si le formulaire est soumis en méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inscription
    if (isset($_POST['inscription'])) {
        $email = cleanInput($_POST['email']);
        $motDePasse = $_POST['motdepasse'];
        $cin = cleanInput($_POST['cin']);
        $telephone = cleanInput($_POST['telephone']);
        $numeroPermis = cleanInput($_POST['numeroPermis']);

        // Valider l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "L'email n'est pas valide.";
        } else {
            // Vérifier si l'email existe déjà dans la base de données
            $sql = "SELECT * FROM utilisateur WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                // Si l'email existe déjà, afficher un message d'erreur
                $message = "L'email existe déjà.";
            } else {
                // Hasher le mot de passe
                $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

                // Insérer l'utilisateur dans la base de données
                $sql = "INSERT INTO utilisateur (email, motDePasse, cin, telephone, numeroPermis, role) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $motDePasseHash, $cin, $telephone, $numeroPermis, ROLE_CLIENT]);

                // Connexion automatique après inscription
                $sql = "SELECT * FROM utilisateur WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user'] = $user;

                // Rediriger en fonction du rôle
                if ($user['role'] == ROLE_ADMIN) {
                    header('Location: admin.php');
                    exit;
                } else if ($user['role'] == ROLE_CLIENT) {
                    header('Location: client.php');
                    exit;
                }
            }
        }
    }

    // Connexion
    if (isset($_POST['connexion'])) {
        $emailConnexion = cleanInput($_POST['emailConnexion']);
        $motdepasseConnexion = $_POST['motdepasseConnexion'];

        // Requête pour récupérer l'utilisateur basé sur l'email
        $sql = "SELECT * FROM utilisateur WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emailConnexion]);

        if ($stmt->rowCount() > 0) {
            // Récupérer l'utilisateur
            $user = $stmt->fetch();

            // Comparer le mot de passe avec password_verify
            if (password_verify($motdepasseConnexion, $user['motDePasse'])) {
                // Connexion réussie
                $_SESSION['user'] = $user; // Stocker les informations de l'utilisateur dans la session

                if ($user['role'] == ROLE_ADMIN) {
                    // Si l'utilisateur est un admin, rediriger vers admin.php
                    header('Location: admin.php');
                    exit;
                } else if ($user['role'] == ROLE_CLIENT) {
                    // Si l'utilisateur est un client, rediriger vers client.html
                    header('Location: client.php');
                    exit;
                }
            } else {
                $message = "Mot de passe incorrect.";
            }
        } else {
            $message = "Email non trouvé.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <title>Gestion de Location de Voitures</title>
</head>
<body>

<div class="conteneur" id="conteneur">
    <!-- Formulaire d'inscription -->
    <div class="conteneur-formulaire inscription" style="display: block;">
        <form id="formInscription" action="connexion.php" method="POST">
            <h1>Créer un compte</h1>
            <input type="email" placeholder="Email" required id="email" name="email">
            <div id="emailError" style="color: red; display: none;">L'email existe déjà.</div>
            <input type="password" placeholder="Mot de passe" required id="motdepasse" name="motdepasse">
            <input type="text" placeholder="CIN" required name="cin">
            <input type="tel" placeholder="Téléphone" required name="telephone">
            <input type="text" placeholder="Numéro de permis" required name="numeroPermis">

            <!-- Affichage du message d'erreur -->
            <?php if (!empty($message)): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <button type="submit" name="inscription">S'inscrire</button>
            <div id="successMessage" style="color: green; display: none; margin-top: 10px;">
                Inscription réussie ! 
            </div>
        </form>
    </div>

    <!-- Formulaire de connexion -->
    <div class="conteneur-formulaire connexion" style="display: block;">
        <form id="formConnexion" action="connexion.php" method="POST">
            <h1>Connexion</h1>
            <input type="email" placeholder="Email" required id="emailConnexion" name="emailConnexion">
            <input type="password" placeholder="Mot de passe" required id="motdepasseConnexion" name="motdepasseConnexion">
            
            <!-- Affichage du message d'erreur -->
            <?php if (!empty($message)): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <button type="submit" name="connexion">Se connecter</button>
        </form>
    </div>
   
    <!-- Section bascule entre Connexion et Inscription -->
    <div class="conteneur-bascule">
        <div class="bascule">
            <div class="panneau-bascule panneau-gauche">
                <h1>Rejoignez-nous dès aujourd'hui !</h1>
                <p>Créez votre compte et accédez à des offres exclusives, une réservation simplifiée et un service sur-mesure.</p>
                <button class="caché" id="connexion">Connexion</button>
            </div>
            <div class="panneau-bascule panneau-droit">
                <h1>Bienvenue !</h1>
                <p>Créez un compte pour réserver votre voiture rapidement et facilement.</p>
                <button class="caché" id="inscription">Créer un compte</button>
            </div>
        </div>
    </div>
</div>

<script>
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

    document.getElementById('formInscription').addEventListener('submit', (event) => {
        afficherNotification('CONGRALATUTIONS ', 'success');
    });
</script>
</body>
</html>