<?php
require('./session.php');
$userName = $_COOKIE['username'] ?? 'Inconnu';
$sessionId = createSession($userName);
header("Location: sharedSession.php?sessionId=" . urlencode($sessionId));
exit;