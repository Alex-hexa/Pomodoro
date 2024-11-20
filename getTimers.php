<?php
require 'db.php';

$userId = $_COOKIE['userId']; // Obtenez l'ID utilisateur à partir du cookie

// Connexion à MongoDB
$client = getMongoClient();
$collection = $client->myDatabase->timers; // Remplacez 'myDatabase' par votre base de données

// Requête pour obtenir les 15 derniers minuteurs de l'utilisateur connecté
$options = [
    'sort' => ['_id' => -1], // Trier par '_id' en ordre décroissant (les plus récents d'abord)
    'limit' => 15 // Limiter à 15 documents
];

// Requête MongoDB pour récupérer les minuteurs de l'utilisateur, triés par ordre d'insertion et filtrés par userId
$timers = $collection->find(['userId' => $userId], $options);

// Convertir les documents en tableau
$timersArray = [];
foreach ($timers as $timer) {
    // Récupérer la date de 'startedAt' au format 'd/M/Y H:i:s'
    $startedAt = $timer['startedAt'];

    // Convertir la date au format 'd/M/Y H:i:s' pour l'affichage
    $startedAtObj = DateTime::createFromFormat('d/M/Y H:i:s', $startedAt);
    if ($startedAtObj === false) {
        continue; // Ignorer les minuteurs avec des dates invalides
    }

    // Formater la date pour l'affichage en 'd/M/Y H:i:s'
    $formattedDate = $startedAtObj->format('d/M/Y H:i:s');

    $timersArray[] = [
        '_id' => (string)$timer['_id'], // ID du minuteur
        'userId' => $timer['userId'],
        'duration' => (int)$timer['duration'], // Durée du minuteur en secondes
        'startedAt' => $formattedDate // La date formatée
    ];
}

// Si aucun minuteur n'est trouvé, afficher un message
if (empty($timersArray)) {
    echo '<li class="list-group-item text-center text-muted">Aucun minuteur trouvé. Créez-en un pour commencer ! PS : Si vous en avez déjà créé, rechargez la page ;)</li>';
    return;
}

// Renvoyer le tableau sous forme de HTML
echo '<ul class="list-group list-group-flush">';
foreach ($timersArray as $timer) {
    echo '<li class="list-group-item d-flex justify-content-between align-items-center border-bottom border-4 border-primary rounded m-1" data-id="' . $timer['_id'] . '">';
    echo "Durée : {$timer['duration']} secondes, fait le {$timer['startedAt']}";
    echo '<div>';
    echo '<button class="btn btn-primary btn-sm mr-1" onclick="fillTimer(' . $timer['duration'] . ')"><i class="fa-solid fa-plus"></i></button>';
    echo '<button class="btn btn-danger btn-sm" onclick="deleteTimer(\'' . $timer['startedAt'] . '\')"><i class="fa-solid fa-trash"></i></button>';
    echo '</div>';
    echo '</li>';
}
echo '</ul>';
?>