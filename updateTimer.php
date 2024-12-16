<?php
require("./session.php");
$userName = $_COOKIE['username'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';
$action = $_POST['action'] ?? '';

$session = getActiveSession($sessionId);
if (!$session) {
    echo json_encode(['status' => 'error', 'message' => 'Session non trouvée']);
    exit;
}

if ($session['host'] !== $userName) {
    echo json_encode(['status' => 'error', 'message' => 'Seul l’hôte peut contrôler le timer']);
    exit;
}

$currentTimer = $session['timer'];
$isPaused = $session['isPaused'];
$startTime = $session['startTime'] ?? null;
$initialDuration = $session['initialDuration'] ?? null;

switch ($action) {
    case 'start':
        if ($isPaused) {
            // Redémarrage à partir du temps restant actuel
            $selectedDuration = $currentTimer;
            updateSessionTimerWithDuration($sessionId, $selectedDuration, false, time(), $selectedDuration);
        } else {
            // Si le timer n'est pas en pause, mais pas de startTime/initialDuration
            // On considère qu'on lance le timer pour la première fois
            if ($startTime === null || $initialDuration === null) {
                $selectedDuration = $currentTimer;
                updateSessionTimerWithDuration($sessionId, $selectedDuration, false, time(), $selectedDuration);
            }
            // Sinon, si le timer est déjà en cours, pas besoin de faire quoi que ce soit
        }
        break;

    case 'pause':
        // Pour la pause, on calcule le temps restant si le timer était en cours
        if (!$isPaused && $startTime !== null && $initialDuration !== null) {
            $now = time();
            $endTime = $startTime + $initialDuration;
            $remaining = max(0, $endTime - $now);

            // On met isPaused = true et timer = remaining
            // On peut supprimer startTime et initialDuration car on a maintenant timer = remaining
            updateSessionTimer($sessionId, $remaining, true);
        }
        break;

    case 'reset':
        // On remet à 1500
        $selectedDuration = 1500;
        // On indique que le timer est en pause (pour qu'il attende un start)
        updateSessionTimer($sessionId, $selectedDuration, true);
        // Optionnel : supprimer startTime et initialDuration si on les avait
        $client = getMongoClient();
        $collection = $client->myDatabase->sessions;
        $collection->updateOne(
            ['sessionId' => $sessionId],
            ['$unset' => ['startTime' => '', 'initialDuration' => '']]
        );
        break;

    case 'setDuration':
        if ($session['host'] !== $userName) {
            echo json_encode(['status' => 'error', 'message' => 'Seul l’hôte peut modifier la durée du timer']);
            exit;
        }
        $newDuration = (int)($_POST['duration'] ?? 1500);
        updateSessionTimer($sessionId, $newDuration, true);
        $client = getMongoClient();
        $collection = $client->myDatabase->sessions;
        $collection->updateOne(['sessionId' => $sessionId], ['$unset' => ['startTime' => '', 'initialDuration' => '']]);
        break;

        // Supposons que l'action 'endSession' existe
    case 'endSession':
        $collection->updateOne(
            ['sessionId' => $sessionId],
            ['$unset' => ['messages' => '']]
        );
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action inconnue']);
        exit;
}

echo json_encode(['status' => 'success']);
