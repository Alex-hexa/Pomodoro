<?php
require ("./session.php"); // Incluez le fichier qui gère les sessions

if (isset($_GET['sessionId'])) {
    $sessionId = $_GET['sessionId'];

    // Récupérer les détails de la session
    $session = getActiveSession($sessionId);

    if ($session) {
        echo json_encode(['status' => 'success', 'members' => $session['participants'], 'sessionDuration' => $session['timer']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No session ID provided.']);
}
?>
