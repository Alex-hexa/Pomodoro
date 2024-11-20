<?php
require 'db.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if userId is present in the request data
    if (isset($data['userId']) && isset($data['duration'])) {
        $userId = $data['userId'];
        $duration = $data['duration'];

        // Now, log the timer duration in the database
        $client = getMongoClient();
        $collection = $client->myDatabase->timers;

        // Insert the timer log using the provided data
        $result = $collection->insertOne([
            'userId' => $userId,
            'duration' => $duration,
            'startedAt' => date('d/M/Y H:i:s')
        ]);

        // Return a success response
        echo json_encode(['status' => 'success']);
        exit();
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Missing userId or duration.']);
        exit();
    }
}