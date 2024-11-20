<?php
require ('./db.php'); // Incluez le fichier de connexion à MongoDB

$client = getMongoClient();
$collection = $client->myDatabase->sessions; // Remplacez "myDatabase" par le nom de votre base de données

// Fonction pour ajouter une session active
function addActiveSession($sessionId, $hostName) {
    global $collection;
    $sessionData = [
        'sessionId' => $sessionId,
        'host' => $hostName,
        'participants' => [$hostName], // Ajoutez l'hôte comme participant
        'timer' => 1500, // Exemple de durée
        'createdAt' => date("d/M/Y H:i:s") // Date actuelle
    ];
    $collection->insertOne($sessionData);
}

// Fonction pour rejoindre une session active
function joinActiveSession($sessionId, $participantName) {
    global $collection;
    return $collection->updateOne(
        ['sessionId' => $sessionId],
        ['$addToSet' => ['participants' => $participantName]] // Ajoute le participant si ce n'est pas déjà dans la liste
    );
}

// Fonction pour récupérer les détails d'une session active
function getActiveSession($sessionId) {
    global $collection;
    return $collection->findOne(['sessionId' => $sessionId]);
}
?>