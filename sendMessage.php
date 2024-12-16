<?php
require('./session.php');
$userName = $_COOKIE['username'] ?? 'Inconnu';
$sessionId = $_POST['sessionId'] ?? '';
$text = $_POST['text'] ?? '';
$system = isset($_POST['system']) ? (bool)$_POST['system'] : false;

if (!$text || !$sessionId) {
    echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants', 'text' => $text, 'sessionId' => $sessionId]);
    exit;
}

$session = getActiveSession($sessionId);
if (!$session) {
    echo json_encode(['status' => 'error', 'message' => 'Session non trouvée']);
    exit;
}

$client = getMongoClient();
$collection = $client->myDatabase->sessions;

$newMessage = [
    'user' => $system ? 'Système' : $userName,
    'text' => $text,
    'time' => date("Y-m-d H:i:s")
];
if ($system) {
    $newMessage['system'] = true;
}

$collection->updateOne(
    ['sessionId' => $sessionId],
    ['$push' => ['messages' => $newMessage]]
);

echo json_encode(['status' => 'success', 'text' => $text, 'sessionId' => $sessionId]);