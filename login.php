<?php
require 'db.php'; // Inclure le fichier de connexion
use \Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = htmlspecialchars($_POST['username_or_email']); // Prendre soit le nom d'utilisateur soit l'email
    $password = $_POST['password']; // Récupérer le mot de passe

    // Recherche de l'utilisateur dans la base de données
    $client = getMongoClient();
    $collection = $client->myDatabase->users;
    $user = $collection->findOne(['$or' => [['username' => $usernameOrEmail], ['email' => $usernameOrEmail]]]);

    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie, créer le token
        $payload = [
            'username' => $user['username'],
            'email' => $user['email'],
            'userId' => (string)$user['_id'],
            'uniq_id' => $user['uniq_id'],
            'iat' => time(), // Date de création
            'exp' => time() + (12 * 60 * 60) // Expire dans 12 heures
        ];

        $jwt = JWT::encode($payload, getJwtSecret(), 'HS512'); // Générer le token

        // Réponse JSON avec le token
        echo json_encode(['token' => $jwt]);
        exit(); // Arrêter le script après avoir envoyé la réponse
    } else {
        // Erreur de connexion
        http_response_code(401);
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="icon" href="/image/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-hH+g0XrzRX1sKHTNB5Q8dNhZAz0eCQK2CLZ2VGxE9BiNeF3D79HLKyg6sTyvUOLjYF6+xNSkG7b9DW3VBpFGOw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <nav class="navbar sticky-top navbar-expand-lg row border-bottom border-dark mx-4">
        <div class="col">
        <a class="navbar-brand mr-5 pl-2 text-danger" href="/index.php"><i class="fas fa-chess-queen"><span class="ml-2">Pomodoro App</span></i></a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mt-5">Connexion</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form id="loginForm" method="POST" action="">
            <div class="form-group">
                <label for="username_or_email">Nom d'utilisateur ou E-mail:</label>
                <input type="text" class="form-control" id="username_or_email" name="username_or_email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
        <p class="mt-3">Pas encore de compte ? <a href="register.php">Créez en un</a></p>
    </div>

    <script src="./js/login.js"></script>
    <script src="./js/storageChecker.js"></script>
</body>

</html>