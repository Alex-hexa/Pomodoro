<?php
require 'db.php';

$client = getMongoClient();
$collection = $client->myDatabase->timers;

$options = [
    'sort' => ['_id' => -1], // Trier par ordre décroissant
    'limit' => 15 // Limiter à 15 documents
];

// ! Obtenez l'ID utilisateur à partir du cookie de jstDecode.js
if($userId = $_COOKIE['userId']){
    $uniqIdUser = $_COOKIE['uniq_id'];
    $timers = $collection->find(['userId' => $uniqIdUser], $options);
    
    // Convertir les documents en tableau
    $timersArray = [];
    foreach ($timers as $timer) {
        $startedAt = $timer['startedAt'];
    
        // Convertir la date au format 'd/M/Y H:i:s' pour l'affichage
        $startedAtObj = DateTime::createFromFormat('d/M/Y H:i:s', $startedAt);
        if ($startedAtObj === false) {
            continue; // Ignorer les minuteurs avec des dates invalides
        }
    
        $formattedDate = $startedAtObj->format('d/M/Y H:i:s');
    
        $timersArray[] = [
            '_id' => (string)$timer['_id'],
            'userId' => $timer['userId'],
            'duration' => (int)$timer['duration'], // Durée du minuteur en secondes
            'startedAt' => $formattedDate, // La date formatée
            'uniq_id_timer' => $timer['uniq_id_timer'] // Identifiant unique afin de simplifier les appels
        ];
    }
    
    
    // Renvoyer le tableau sous forme de HTML
    echo '<ul class="list-group list-group-flush">';
    foreach ($timersArray as $timer) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center border-bottom border-4 border-primary rounded m-1" data-id="' . $timer['_id'] . '">';
        echo "Durée : {$timer['duration']} secondes, fait le {$timer['startedAt']}";
        echo '<div>';
        echo '<button class="btn btn-primary btn-sm mr-1" onclick="fillTimer(' . $timer['duration'] . ')"><i class="fa-solid fa-plus"></i></button>';
        echo '<button class="btn btn-danger btn-sm" onclick="deleteTimer(\'' . $timer['uniq_id_timer'] . '\')"><i class="fa-solid fa-trash"></i></button>';
        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
}
// Si aucun minuteur n'est trouvé, afficher un message
if (empty($timersArray)) {
    echo '<li class="list-group-item text-center text-muted">Aucun minuteur trouvé. Créez-en un pour commencer ! <br> PS : Si vous en avez déjà créé, rechargez la page ;)</li>';
    return;
}
?>