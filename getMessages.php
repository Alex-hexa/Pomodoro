<?php
require('./session.php');
$sessionId = $_GET['sessionId'] ?? ''; // Utilisez GET car vous faites un $.getJSON
if (!$sessionId) {
    echo json_encode(['status' => 'error', 'message' => 'Session ID manquant']);
    exit;
}

$session = getActiveSession($sessionId);
if (!$session) {
    echo json_encode(['status' => 'error', 'message' => 'Session non trouvée']);
    exit;
}

$messages = $session['messages'] ?? [];
echo json_encode(['status' => 'success', 'messages' => $messages]);
