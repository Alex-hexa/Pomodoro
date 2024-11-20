<?php
// Démarrer la session
session_start();

// Détruire le token dans la session
unset($_SESSION['jwtToken']);

// Détruire la session
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>
    <script>
        // Effacer le sessionStorage
        sessionStorage.clear();

        // Rediriger vers la page de connexion après un délai
        window.onload = function() {
            window.location.href = 'login.php';
        };
    </script>
</head>
<body>
    <p>Déconnexion en cours...</p>
</body>
</html>