<?php
require("./session.php");
$userName = $_COOKIE['username'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';
$emoji = $_POST['emoji'] ?? '';

if (!$sessionId || !$emoji) {
    echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants']);
    exit;
}

$session = getActiveSession($sessionId);
if (!$session) {
    echo json_encode(['status' => 'error', 'message' => 'Session non trouvée']);
    exit;
}

// Convertir l'objet BSONArray en tableau PHP
$participants = $session['participants']->getArrayCopy();

if (!in_array($userName, $participants)) {
    echo json_encode(['status' => 'error', 'message' => 'Vous ne faites pas partie de la session']);
    exit;
}

// Mettre à jour la réaction
$client = getMongoClient();
$collection = $client->myDatabase->sessions;

$collection->updateOne(
    ['sessionId' => $sessionId],
    ['$set' => ["reactions.$userName" => $emoji]]
);

echo json_encode(['status' => 'success']);