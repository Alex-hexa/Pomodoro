<?php
require ("./session.php"); // Incluez le fichier qui gère les sessions

$sessionId = $_GET['sessionId'] ?? '';

// Vérifiez si la session est active
$session = getActiveSession($sessionId);
if (!$session) {
    die("Session non trouvée.");
}

// Récupérer les informations de la session
$host = $session['host'];
$participants = $session['participants'];
$timer = $session['timer'];
?>
<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Active</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Session Active</h1>
        <h2>Hôte: <?= htmlspecialchars($host) ?></h2>
        <h3>Participants:</h3>
        <ul>
            <?php foreach ($participants as $participant): ?>
                <li><?= htmlspecialchars($participant) ?></li>
            <?php endforeach; ?>
        </ul>

        <h3>Minuteur:</h3>
        <div id="countdown"><?= gmdate("H:i:s", $timer) ?></div>

        <script>
            let totalSeconds = <?= $timer ?>;

            function updateCountdown() {
                if (totalSeconds <= 0) {
                    document.getElementById('countdown').innerHTML = "Temps écoulé!";
                    return;
                }

                totalSeconds--;
                const hrs = Math.floor(totalSeconds / 3600);
                const mins = Math.floor((totalSeconds % 3600) / 60);
                const secs = totalSeconds % 60;

                document.getElementById('countdown').innerHTML =
                    `${String(hrs).padStart(2, '0')}h ${String(mins).padStart(2, '0')}m ${String(secs).padStart(2, '0')}s`;
            }

            setInterval(updateCountdown, 1000);
        </script>
    </div>
</body>
</html>