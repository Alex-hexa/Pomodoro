<?php
require("./session.php");
$userName = $_COOKIE['username'] ?? 'Inconnu';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = $_POST['sessionId'];
    if (joinActiveSession($sessionId, $userName)) {
        header("Location: sharedSession.php?sessionId=" . urlencode($sessionId));
        exit;
    } else {
        $message = "Session non trouvée.";
    }
}
?>
<!-- Formulaire HTML -->
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/image/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/pomodoro.js" defer></script>
    <script src="./js/jwtDecoder.js" defer></script>
    <script src="./js/storageChecker.js" defer></script>
    <title>Rejoindre une session</title>
</head>

<body>
    <nav class="navbar sticky-top navbar-expand-lg d-flex border-bottom border-dark mx-4">
        <div class="flex-grow-1">
            <a class="navbar-brand mr-5 pl-2 text-danger" href="/index.php">
                <i class="fas fa-chess-queen"><span class="ml-2">Pomodoro App</span></i>
            </a>
        </div>
        <div class="dropdown mr-3">
            <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-share-alt"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/createSession.php">Créer une session partagée</a></li>
                <li><a class="dropdown-item" href="/joinSession.php">Rejoindre via un ID</a></li>
            </ul>
        </div>
        <a href="/profil.php" class="btn btn-primary mr-3"><i class="fas fa-user"></i></a>
        <a href="/logout.php" class="btn btn-danger mr-4"><i class="fas fa-right-from-bracket"></i></a>
    </nav>
    <div class="container mt-2">
        <?php if (isset($message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>ID de la session:</label>
            <input type="text" name="sessionId" required>
            <button type="submit">Rejoindre</button>
        </form>
    </div>
</body>

</html>