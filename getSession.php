<?php
require("./session.php");
if (isset($_GET['sessionId'])) {
    $sessionId = $_GET['sessionId'];
    $session = getActiveSession($sessionId);
    if ($session) {
        $now = time();
        // Vérifiez que startTime et initialDuration existent et qu'ils ne sont pas null
        $startTime = $session['startTime'] ?? null;
        $initialDuration = $session['initialDuration'] ?? null;

        if ($startTime !== null && $initialDuration !== null && !$session['isPaused']) {
            $endTime = $startTime + $initialDuration;
            $remaining = max(0, $endTime - $now);
        } else {
            // Si le timer est en pause ou pas encore défini, utilisez $session['timer']
            $remaining = $session['timer'];
        }

        echo json_encode([
            'status' => 'success',
            'timer' => $session['timer'],
            'isPaused' => $session['isPaused'],
            'host' => $session['host'],
            'participants' => $session['participants'],
            'reactions' => $session['reactions'] ?? new stdClass()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Session ID manquant']);
}
