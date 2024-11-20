<?php
require 'vendor/autoload.php';

function getMongoClient()
{
    $mongoUri = getenv('MONGO_URI');
    $client = new MongoDB\Client($mongoUri);
    return $client;
}

function getJwtSecret()
{
    $secret = getenv('JWT_SECRET');
    if (!$secret) {
        throw new Exception('La clé secrète JWT n\'est pas définie dans le fichier .env');
    }
    return $secret;
}

function uniq_id(){
    return bin2hex(random_bytes(12)); // Générer un identifiant unique
}

?>