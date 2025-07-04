<?php
use App\SessionManager;

SessionManager::start();
SessionManager::enforceTimeoutIfLoggedIn();

$user = SessionManager::getUser();

if (!$user) {
    header('Location: login.php');
    exit;
}
