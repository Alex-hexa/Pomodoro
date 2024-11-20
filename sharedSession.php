<?php
require('./session.php'); // Incluez le fichier qui gère les sessions

$userName = $_COOKIE['username']; // Récupérez le nom de l'utilisateur connecté
$sessionId = $_COOKIE['session'] ?? null; // Obtenez l'ID de session à partir du cookie

// Connexion à MongoDB
$client = getMongoClient();
$collection = $client->myDatabase->sessions; // Remplacez par votre nom de base de données

if ($sessionId) {
    // Récupérez les détails de la session pour l'affichage
    $session = getActiveSession($sessionId); // Assurez-vous d'avoir la fonction pour obtenir une session active

    // Vérifiez si la session a été trouvée
    if ($session) {
        $participants = $session['participants']; // Accéder aux participants
        $timer = $session['timer']; // Accéder au minuteur
    } else {
        die("Session non trouvée.");
    }
} else {
    die("Aucun ID de session fourni.");
}
?>
<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Partagée</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/pomodoro.js" defer></script>
    <script src="./js/jwtDecoder.js"></script>
    <script src="./js/redirect.js" defer></script>
    <script src="./js/storageChecker.js" defer></script>
</head>

<body id="pageBody" class="bg-light text-dark">
    <nav class="navbar sticky-top navbar-expand-lg row border-bottom border-dark mx-4">
        <div class="col">
            <a class="navbar-brand mr-5 pl-2 text-danger" href="/index.php"><i class="fas fa-chess-queen"><span class="ml-2">Pomodoro App</span></i></a>
        </div>
        <div class="col-6"></div>
        <div class="col d-flex justify-content-end">
            <button id="toggleMode" class="btn btn-outline-dark mr-2"><i class="fas fa-moon"></i></button>
            <a href="/profile.php" class="btn btn-primary mr-2"><i class="fas fa-user"></i></a>
            <a href="/logout.php" class="btn btn-danger mr-2"><i class="fas fa-right-from-bracket"></i></a>
        </div>
    </nav>

    <div class="container">
        <span class="hidden" id="userId"></span>
        <h1 class="pt-5">Session Partagée</h1>
        <h2>Hôte : <?= htmlspecialchars($userName) ?></h2>
        <h2>Session : <?= htmlspecialchars($sessionId) ?></h2> <!-- Affiche l'ID de session -->

        <div id="memberList" class="mt-3">
            <h2>Membres de la session</h2>
            <ul id="members" class="list-group">
                <?php foreach ($participants as $participant): ?>
                    <li class="list-group-item" style="color: <?= $participant === $userName ? 'blue' : 'black'; ?>">
                        <?= htmlspecialchars($participant) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="mt-3">
            <strong>Durée de la Session :</strong> <span id="sessionDuration"><?= gmdate("H:i:s", $timer) ?></span>
        </div>

        <h3 class="py-2 display-2 fw-bold d-flex justify-content-center" id="countdown">0h 0m 0s</h3>

        <div id="sharedTimer" class="mt-5">
            <h2 class="pt-2">Configurer le minuteur Pomodoro</h2>
            <div class="row">
                <div class="form-group form-group-lg col">
                    <label for="hours">Heures :</label>
                    <input type="number" id="hours" class="form-control" min="0" max="23">
                </div>
                <div class="form-group form-group-lg col">
                    <label for="minutes">Minutes :</label>
                    <input type="number" id="minutes" class="form-control" min="0" max="59">
                </div>
                <div class="form-group form-group-lg col">
                    <label for="seconds">Secondes :</label>
                    <input type="number" id="seconds" class="form-control" min="0" max="59">
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button id="startSharedTimer" class="btn btn-lg btn-primary m-1">Démarrer le minuteur</button>
                <button id="pauseSharedTimer" class="btn btn-lg btn-warning m-1" disabled>Pause</button>
                <button id="stopSharedTimer" class="btn btn-lg btn-danger m-1" disabled>Arrêter</button>
            </div>
        </div>
    </div>

    <script>
        let sharedTotalSeconds = 0;
        let sharedInterval;
        let isSharedPaused = false;
        let members = []; // Tableau pour stocker les membres

        // Ajouter l'hôte à la liste des membres
        members.push({
            name: '<?= htmlspecialchars($userName) ?>',
            host: true
        });

        function updateMemberList() {
            const memberList = document.getElementById('members');
            memberList.innerHTML = ''; // Efface la liste actuelle
            members.forEach(member => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item';
                listItem.textContent = member.name;
                listItem.style.color = member.host ? 'blue' : 'black'; // Met l'hôte en bleu
                memberList.appendChild(listItem);
            });
        }

        // Initialiser la liste des membres
        updateMemberList();

        document.getElementById('startSharedTimer').onclick = function() {
            const hours = parseInt(document.getElementById('hours').value) || 0;
            const minutes = parseInt(document.getElementById('minutes').value) || 0;
            const seconds = parseInt(document.getElementById('seconds').value) || 0;

            sharedTotalSeconds = (hours * 3600) + (minutes * 60) + seconds;

            // Désactive les champs d'entrée après le démarrage
            document.getElementById('hours').disabled = true;
            document.getElementById('minutes').disabled = true;
            document.getElementById('seconds').disabled = true;

            updateCountdown(); // Commence le compte à rebours
        };

        function updateCountdown() {
            if (sharedTotalSeconds <= 0) {
                document.getElementById('countdown').innerHTML = "Temps écoulé!";
                clearInterval(sharedInterval);
                return;
            }

            sharedInterval = setInterval(() => {
                if (isSharedPaused) return; // Si en pause, ne pas décrémenter

                const hrs = Math.floor(sharedTotalSeconds / 3600);
                const mins = Math.floor((sharedTotalSeconds % 3600) / 60);
                const secs = sharedTotalSeconds % 60;

                document.getElementById('countdown').innerHTML =
                    `${String(hrs).padStart(2, '0')}h ${String(mins).padStart(2, '0')}m ${String(secs).padStart(2, '0')}s`;

                sharedTotalSeconds--;

                if (sharedTotalSeconds < 0) {
                    clearInterval(sharedInterval);
                    document.getElementById('countdown').innerHTML = "Temps écoulé!";
                }
            }, 1000);
        }

        document.getElementById('pauseSharedTimer').onclick = function() {
            isSharedPaused = !isSharedPaused;
            this.innerText = isSharedPaused ? "Reprendre" : "Pause";
        };

        document.getElementById('stopSharedTimer').onclick = function() {
            clearInterval(sharedInterval);
            sharedTotalSeconds = 0; // Réinitialise le minuteur
            document.getElementById('countdown').innerHTML = "0h 0m 0s"; // Réinitialise l'affichage
        };

        // Toggle dark mode functionality
        document.getElementById('toggleMode').onclick = function() {
            const body = document.getElementById('pageBody');
            body.classList.toggle('bg-dark');
            body.classList.toggle('text-light');
            body.classList.toggle('bg-light');
            body.classList.toggle('text-dark');

            const navbar = document.querySelector('nav');
            navbar.classList.toggle('border-light');
            navbar.classList.toggle('border-dark');

            const memberList = document.getElementById('members');
            memberList.classList.toggle('bg-dark');
            memberList.classList.toggle('text-light');

            const listItems = memberList.getElementsByTagName('li');
            for (let i = 0; i < listItems.length; i++) {
                listItems[i].classList.toggle('bg-light');
                listItems[i].classList.toggle('text-dark');
            }

            const toggleButton = document.getElementById('toggleMode');
            if (body.classList.contains('bg-dark')) {
                toggleButton.classList = 'btn btn-outline-light mr-2';
                toggleButton.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                toggleButton.classList = 'btn btn-outline-dark mr-2';
                toggleButton.innerHTML = '<i class="fas fa-moon"></i>';
            }
        };

        // URL handling for unique sessions
        const urlParams = new URLSearchParams(window.location.search);
    </script>
</body>

</html>