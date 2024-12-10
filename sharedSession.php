<?php
require('./session.php');
$userName = $_COOKIE['username'] ?? 'Inconnu';
$sessionId = $_GET['sessionId'] ?? '';
$session = getActiveSession($sessionId);
if (!$session) {
    die("Session non trouvée.");
}
$isHost = ($session['host'] === $userName);

$sessionUrl = "http://" . $_SERVER['HTTP_HOST'] . "/sharedSession.php?sessionId=" . urlencode($sessionId);

$currentTimer = $session['timer'];
$hrs = floor($currentTimer / 3600);
$mins = floor(($currentTimer % 3600) / 60);
$secs = $currentTimer % 60;
?>
<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/image/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <title>Session Partagée</title>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/pomodoro.js" defer></script>
    <script src="./js/jwtDecoder.js" defer></script>
    <script src="./js/storageChecker.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        const sessionHost = "<?= htmlspecialchars($session['host']) ?>";
        const currentUser = "<?= htmlspecialchars($userName) ?>";
        const sessionId = "<?= htmlspecialchars($sessionId) ?>";
        const sessionUrl = "<?= $sessionUrl ?>";

        let timerInterval = null;

        function fetchSessionState() {
            $.getJSON('getSession.php', {
                sessionId: sessionId
            }, function(data) {
                if (data.status === 'success') {
                    const timer = data.timer;
                    const hrs = Math.floor(timer / 3600);
                    const mins = Math.floor((timer % 3600) / 60);
                    const secs = timer % 60;

                    $('#countdown').text(`${hrs}h ${mins}m ${secs}s`);

                    $('#participantList').empty();
                    data.participants.forEach(p => {
                        let className = '';
                        if (p === currentUser) {
                            className = 'text-primary';
                        } else if (p === sessionHost) {
                            className = 'text-danger';
                        }
                        const emoji = data.reactions && data.reactions[p] ? data.reactions[p] : '';
                        $('#participantList').append(`<li class="${className}">${p} ${emoji}</li>`);
                    });

                    if (!data.isPaused && timer > 0) {
                        startLocalCountDown(timer);
                    } else {
                        if (timerInterval) clearInterval(timerInterval);
                    }
                }
            });
        }

        function startLocalCountDown(timerValue) {
            if (timerInterval) clearInterval(timerInterval);
            let currentTimer = timerValue;
            timerInterval = setInterval(() => {
                currentTimer--;
                if (currentTimer <= 0) {
                    clearInterval(timerInterval);
                }
                const hrs = Math.floor(currentTimer / 3600);
                const mins = Math.floor((currentTimer % 3600) / 60);
                const secs = currentTimer % 60;
                $('#countdown').text(`${hrs}h ${mins}m ${secs}s`);
            }, 1000);
        }

        $(document).ready(function() {
            fetchSessionState();
            setInterval(fetchSessionState, 10000);

            $('#sessionIdSpan').on('click', function() {
                navigator.clipboard.writeText(sessionUrl).then(() => {
                    const message = $('#copyMessage');
                    message.show();
                    setTimeout(() => {
                        message.hide();
                    }, 3000);
                }).catch(err => {
                    console.error('Impossible de copier le lien : ', err);
                });
            });

            // Mise à jour de la durée par l'hôte
            $('#updateDurationBtn').on('click', function(e) {
                e.preventDefault();
                const h = parseInt($('#hoursInput').val()) || 0;
                const m = parseInt($('#minutesInput').val()) || 0;
                const s = parseInt($('#secondsInput').val()) || 0;
                const totalSeconds = (h * 3600) + (m * 60) + s;

                $.post('updateTimer.php', {
                    sessionId: sessionId,
                    action: 'setDuration',
                    duration: totalSeconds
                }, function(data) {
                    if (data.status === 'success') {
                        fetchSessionState();
                    } else {
                        console.error(data.message);
                    }
                }, 'json');
            });

            // Bouton Réactions
            $('#reactionBtn').on('click', function() {
                $('#emojiMenu').toggle();
            });

            // Sélection d'un emoji
            $(document).on('click', '.emoji-option', function() {
                const chosenEmoji = $(this).text();
                $.post('updateReaction.php', {
                    sessionId: sessionId,
                    emoji: chosenEmoji
                }, function(data) {
                    if (data.status === 'success') {
                        fetchSessionState();
                    } else {
                        console.error(data.message);
                    }
                }, 'json');
            });

        });
    </script>
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

    <div class="modal fade" id="clickForCopy" tabindex="-1" aria-labelledby="clickForCopyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="clickForCopyLabel">Information</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    Cliquez sur le numéro de session pour copier le lien.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal"><i class="fa-solid fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4">
        <div id="copyMessage" class="alert alert-success" style="display:none;">
            Le lien de la session a été copié !
        </div>
        <h2>Session: <span id="sessionIdSpan" class="text-success" style="cursor: pointer;"><?= htmlspecialchars($sessionId) ?></span><span style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#clickForCopy" class="ml-2 text-success"><i class="fa-solid fa-circle-info"></i></span></h2>
        <h3 class="py-2">Hôte: <span class="text-danger"><?= htmlspecialchars($session['host']) ?></span></h3>
        <h3 class="pb-1">Vous êtes: <span class="text-primary"><?= htmlspecialchars($userName) ?> <?= $isHost ? '(Hôte)' : '' ?></span></h3>

        <div class="py-4">
            <h3>Timer: <span class="text-primary d-flex justify-content-center" id="countdown"></span></h3>
            <?php if ($isHost): ?>
                <div class="d-flex justify-content-center">
                    <button class="btn btn-primary m-1" id="startBtn" onclick="$.post('updateTimer.php',{sessionId:'<?= $sessionId ?>',action:'start'},fetchSessionState)"> <i class="fa-solid fa-play"></i></button>
                    <button class="btn btn-warning m-1" id="pauseBtn" onclick="$.post('updateTimer.php',{sessionId:'<?= $sessionId ?>',action:'pause'},fetchSessionState)"><i class="fa-solid fa-pause"></i></button>
                    <button class="btn btn-danger m-1" id="resetBtn" onclick="$.post('updateTimer.php',{sessionId:'<?= $sessionId ?>',action:'reset'},fetchSessionState)"><i class="fa-solid fa-rotate-right"></i></button>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    <div class="form-inline">
                        <input type="number" id="hoursInput" class="form-control m-1" placeholder="Heures" min="0" max="99" value="<?= $hrs ?>">
                        :
                        <input type="number" id="minutesInput" class="form-control m-1" placeholder="Minutes" min="0" max="59" value="<?= $mins ?>">
                        :
                        <input type="number" id="secondsInput" class="form-control m-1" placeholder="Secondes" min="0" max="59" value="<?= $secs ?>">
                        <button class="btn btn-info m-1 ml-2" id="updateDurationBtn">Mettre à jour la durée</button>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-danger d-flex justify-content-center">Seul l’hôte peut contrôler le timer.</p>
            <?php endif; ?>
        </div>

        <div>
            <h3>Participants:</h3>
            <ul id="participantList">
                <?php foreach ($session['participants'] as $part): ?>
                    <?php
                    $className = '';
                    if ($part === $session['host']) {
                        $className = 'text-danger';
                    } elseif ($part === $userName) {
                        $className = 'text-primary';
                    }
                    ?>
                    <li class="<?= $className ?>"><?= htmlspecialchars($part) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="reaction-container">
            <button id="reactionBtn" class="btn btn-info"><i class="fa-solid fa-icons"></i></button>
            <div id="emojiMenu">
                <span class="emoji-option">😊</span>
                <span class="emoji-option">😎</span>
                <span class="emoji-option">👍</span>
                <span class="emoji-option">🔥</span>
                <span class="emoji-option">🎉</span>
                <span class="emoji-option">👏</span>
                <span class="emoji-option">🤩</span>
                <span class="emoji-option">🐨</span>
                <span class="emoji-option">❤️</span>
            </div>
        </div>
    </div>
</body>

</html>