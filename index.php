<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <link rel="icon" href="/image/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/pomodoro.js" defer></script>
    <script src="./js/jwtDecoder.js" defer></script>
    <script src="./js/storageChecker.js" defer></script>
    <script>
        function fillTimer(duration) {
            const hours = Math.floor(duration / 3600);
            const minutes = Math.floor((duration % 3600) / 60);
            const seconds = duration % 60;

            document.getElementById('hours').value = hours;
            document.getElementById('minutes').value = minutes;
            document.getElementById('seconds').value = seconds;
        }
    </script>
</head>

<body id="pageBody" class="bg-light text-dark">
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
    <div class="container">
        <span class="hidden" id="userId"></span>
        <h3 class="pt-2">Bienvenue, <span id="username"></span> !</h3>
        <h3 class="py-2 display-2 fw-bold d-flex justify-content-center" id="countdown"></h3>
        <div>
            <h2 class="pt-2">Créer un minuteur Pomodoro</h2>
            <div class="row">
                <div class="form-group form-group-lg col">
                    <label for="hours">Heures:</label>
                    <input type="number" id="hours" class="form-control" min="0" max="23">
                </div>
                <div class="form-group form-group-lg col">
                    <label for="minutes">Minutes:</label>
                    <input type="number" id="minutes" class="form-control" min="0" max="59">
                </div>
                <div class="form-group form-group-lg col">
                    <label for="seconds">Secondes:</label>
                    <input type="number" id="seconds" class="form-control" min="0" max="59">
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button id="startTimer" class="btn btn-lg btn-primary m-1">Démarrer le minuteur</button>
                <button id="pauseTimer" class="btn btn-lg btn-warning m-1" disabled>Pause</button>
                <button id="resetTimer" class="btn btn-lg btn-danger m-1" disabled>Réinitialiser</button>
            </div>
        </div>

        <h2 class="pt-3 pb-2">Mes Minuteurs :</h2>
        <ul id="timerList" class="list-group list-group-flush border border-2 border-secondary rounded bg-light" style="max-height: 300px; overflow-y: auto;">
            <?php include('getTimers.php'); ?>
        </ul>       
    </div>

    <script>
        // Toggle dark mode
        document.getElementById('toggleMode').onclick = function() {
            const body = document.getElementById('pageBody');
            body.classList.toggle('bg-dark');
            body.classList.toggle('text-light');
            body.classList.toggle('bg-light');
            body.classList.toggle('text-dark');

            const navbar = document.querySelector('nav'); // Select the nav element
            navbar.classList.toggle('border-light'); // Change border to light in dark mode
            navbar.classList.toggle('border-dark'); // Change border to dark in light mode

            const timerList = document.getElementById('timerList');
            timerList.classList.toggle('bg-dark'); // Toggle dark background for timer list
            timerList.classList.toggle('text-light'); // Toggle light text for timer list

            const listItems = timerList.getElementsByTagName('li');
            for (let i = 0; i < listItems.length; i++) {
                listItems[i].classList.toggle('bg-light'); // Change item background to dark mode
                listItems[i].classList.toggle('text-dark'); // Change item text to light mode
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
    </script>
</body>

</html>