<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['userId']) && isset($data['duration'])) {
        $userId = $_COOKIE['uniq_id'];
        $duration = $data['duration'];
        $uniq_id_timer = uniqid();

        $client = getMongoClient();
        $collection = $client->myDatabase->timers;

        $result = $collection->insertOne([
            'userId' => $userId,
            'duration' => $duration,
            'startedAt' => date('d/M/Y H:i:s'),
            'uniq_id_timer' => $uniq_id_timer
        ]);

        echo json_encode(['status' => 'success']);
        exit();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing userId or duration.']);
        exit();
    }
}