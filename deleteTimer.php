<?php
require 'db.php';

if (isset($_POST['uniq_id_timer'])) {
    $timerId = $_POST['uniq_id_timer'];

    // Connexion à MongoDB
    $client = getMongoClient();
    $collection = $client->myDatabase->timers;

    // Supprimer le minuteur par ID
    $result = $collection->deleteOne(['uniq_id_timer' => $timerId]);

    if ($result->getDeletedCount() === 1) {
        echo json_encode(['status' => 'success', 'message' => 'Minuteur supprimé avec succès.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression du minuteur.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aucun ID de minuteur fourni.']);
}
?>
