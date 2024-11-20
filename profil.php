<?php
require 'db.php';

// Vérifier si l'utilisateur est connecté via un cookie
if (!isset($_COOKIE['userId'])) {
    header('Location: login.php'); // Rediriger si l'utilisateur n'est pas connecté
    exit();
}

$uniq_id = $_COOKIE['uniq_id']; // Obtenir l'ID utilisateur depuis le cookie

// Connexion à MongoDB
$client = getMongoClient();
$collection = $client->myDatabase->users; // Remplacer 'myDatabase' par votre base de données

// Récupérer les informations de l'utilisateur avec ObjectId simulé
$uniq = $collection->findOne(['uniq_id' => $uniq_id]);

if (!$uniq) {
    echo "Utilisateur non trouvé.";
    exit();
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les nouvelles informations du formulaire
    $newUsername = $_POST['username'] ?? $uniq['username'];
    $newEmail = $_POST['email'] ?? $uniq['email'];
    $newPassword = $_POST['password'] ?? null;

    // Si un nouveau mot de passe est entré, hacher le mot de passe
    if (!empty($newPassword)) {
        $newPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    } else {
        $newPassword = $uniq['password']; // Conserver l'ancien mot de passe
    }

    // Préparer les données à mettre à jour
    $updateData = [
        'username' => $newUsername,
        'email' => $newEmail,
        'password' => $newPassword
    ];

    // Mettre à jour l'utilisateur dans la base de données
    $updateResult = $collection->updateOne(
        ['uniq_id' => $uniq_id],
        ['$set' => $updateData]
    );

    if ($updateResult->getModifiedCount() > 0) {
        $message = "<div class='alert alert-success' role='alert'>
                        Profil mis à jour avec succès.
                    </div>";
    } else {
        $message = "<div class='alert alert-info' role='alert'>
                        Aucune modification n'a été effectuée.
                    </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/jwtDecoder.js" defer></script>
    <script src="./js/redirect.js" defer></script>
    <script src="./js/storageChecker.js" defer></script>
</head>

<body>
    <nav class="navbar sticky-top navbar-expand-lg row border-bottom border-dark mx-4">
        <div class="col">
        <a class="navbar-brand mr-5 pl-2 text-danger" href="/index.php"><i class="fas fa-chess-queen"><span class="ml-2">Pomodoro App</span></i></a>
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

    <div class="container mt-5">
        <?php if (isset($message)): ?>
            <?= $message ?>
        <?php endif; ?>
        <h2>Profil de l'utilisateur</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($uniq['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($uniq['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Laissez vide si vous ne voulez pas modifier le mot de passe">
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
        </form>
    </div>
</body>

</html>