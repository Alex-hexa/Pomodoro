<?php
require ('./db.php'); 

$client = getMongoClient();
$collection = $client->myDatabase->sessions;

function createSession($hostName) {
    global $collection;
    $sessionId = uniqid('sess_', true);
    $sessionData = [
        'sessionId' => $sessionId,
        'host' => $hostName,
        'participants' => [$hostName],
        'timer' => 1500, // 25 min par défaut
        'isPaused' => true,
        'createdAt' => date('d/M/Y H:i:s')
    ];
    $collection->insertOne($sessionData);
    return $sessionId;
}

function joinActiveSession($sessionId, $participantName) {
    global $collection;
    $res = $collection->updateOne(
        ['sessionId' => $sessionId],
        ['$addToSet' => ['participants' => $participantName]]
    );
    return $res->getModifiedCount() > 0;
}

function getActiveSession($sessionId) {
    global $collection;
    return $collection->findOne(['sessionId' => $sessionId]);
}

function updateSessionTimer($sessionId, $timer, $isPaused) {
    global $collection;
    $collection->updateOne(
        ['sessionId' => $sessionId],
        ['$set' => ['timer' => $timer, 'isPaused' => $isPaused]]
    );
}

function updateSessionTimerWithDuration($sessionId, $timer, $isPaused, $startTime, $initialDuration) {
    global $collection;
    $collection->updateOne(
        ['sessionId' => $sessionId],
        ['$set' => [
            'timer' => $timer,
            'isPaused' => $isPaused,
            'startTime' => $startTime,
            'initialDuration' => $initialDuration
        ]]
    );
}