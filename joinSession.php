<?php
require ("./session.php"); // Incluez le fichier qui gère les sessions

$userName = $_COOKIE['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = $_POST['sessionId'] ?? '';

    // Vérifiez si la session est active
    if (joinActiveSession($sessionId, $userName)) {
        header("Location: activeSession.php?sessionId=" . $sessionId);
        exit;
    } else {
        $message = "Session non trouvée.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre une Session</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="pt-5">Rejoindre une Session</h1>
        <form method="POST">
            <div class="form-group">
                <label for="sessionId">Entrez l'ID de la session :</label>
                <input type="text" name="sessionId" id="sessionId" class="form-control" placeholder="ID de la session" required>
            </div>
            <button type="submit" class="btn btn-primary">Rejoindre</button>
        </form>

        <div id="message" class="mt-3">
            <?php if (isset($message)) echo '<div class="alert alert-danger">' . $message . '</div>'; ?>
        </div>
    </div>
</body>
</html>