<?php
require("./session.php");
if (isset($_GET['sessionId'])) {
    $sessionId = $_GET['sessionId'];
    $session = getActiveSession($sessionId);
    if ($session) {
        $now = time();
        $startTime = $session['startTime'] ?? null;
        $initialDuration = $session['initialDuration'] ?? null;

        if ($startTime !== null && $initialDuration !== null && !$session['isPaused']) {
            $endTime = $startTime + $initialDuration;
            $remaining = max(0, $endTime - $now);
        } else {
            // Si le timer est en pause ou pas défini, on utilise la valeur statique
            $remaining = $session['timer'];
        }

        echo json_encode([
            'status' => 'success',
            'timer' => $remaining, // Utiliser $remaining ici !
            'isPaused' => $session['isPaused'],
            'host' => $session['host'],
            'participants' => $session['participants'],
            'reactions' => $session['reactions'] ?? new stdClass()
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session introuvable']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Session ID manquant']);
}