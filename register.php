<?php
require 'db.php'; // Inclure le fichier de connexion
use \Firebase\JWT\JWT; // Importer la classe JWT

$client = getMongoClient();
$collection = $client->myDatabase->users;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hacher le mot de passe
    $uniq_id = uniq_id(); // Générer un identifiant unique

    // Insérer dans la base de données
    $result = $collection->insertOne([
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'uniq_id' => $uniq_id
    ]);

    if ($result->getInsertedCount() === 1) {
        // Créer le token
        $payload = [
            'username' => $user['username'],
            'email' => $user['email'],
            'userId' => (string)$user['_id'], // Add the user ID
            'uniq_id' => $user['uniq_id'], // Générer un identifiant unique
            'iat' => time(), // Date de création
            'exp' => time() + (12 * 60 * 60) // Expire dans 12 heures
        ];        

        $jwt = JWT::encode($payload, getJwtSecret(), 'HS512'); // Ajout de l'algorithme

        // Retourner le token en tant que réponse JSON
        echo json_encode(['token' => $jwt]);
        exit(); // Arrêter le script après avoir envoyé la réponse
    } else {
        echo json_encode(['error' => 'Erreur lors de l\'inscription.']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-hH+g0XrzRX1sKHTNB5Q8dNhZAz0eCQK2CLZ2VGxE9BiNeF3D79HLKyg6sTyvUOLjYF6+xNSkG7b9DW3VBpFGOw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<nav class="navbar sticky-top navbar-expand-lg row border-bottom border-dark mx-4">
        <div class="col">
            <a class="navbar-brand mr-5 pl-2 text-danger" href="/index.php"><i class="fas fa-chess-queen"></i></a>
        </div>
        <div class="col-6"></div>
        <div class="col d-flex justify-content-end">
            <button id="toggleMode" class="btn btn-outline-dark mr-3"><i class="fas fa-moon"></i></button>
            <div class="dropdown mr-3">
                <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-share-alt"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/sharedSession.php">Créer une session partagée</a></li>
                    <li><a class="dropdown-item" href="/joinSession.php">Rejoindre via un ID</a></li>
                </ul>
            </div>
            <a href="/profil.php" class="btn btn-primary mr-3"><i class="fas fa-user"></i></a>
            <a href="/logout.php" class="btn btn-danger mr-4"><i class="fas fa-right-from-bracket"></i></a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mt-5">Créer un compte</h2>
        <form id="registrationForm" method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        <p class="mt-3">Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
    </div>

    <script src="./js/register.js"></script>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', async (event) => {
            event.preventDefault(); // Empêche le rechargement par défaut

            const formData = new FormData(event.target);

            try {
                const response = await fetch('', { // Envoie les données au même fichier
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    window.location.href = 'login.php'; // Redirige vers la page d'accueil
                } else {
                    alert('Erreur : ' + (await response.json()).error);
                }
            } catch (error) {
                alert('Une erreur est survenue. Veuillez réessayer.');
            }
        });
    </script>
</body>
</html>