<?php
use App\SessionManager;

SessionManager::start();
SessionManager::enforceTimeoutIfLoggedIn();

$user = SessionManager::getUser();

if (!$user || !SessionManager::get('2fa_verified')) {
    header('Location: verify_2fa.php');
    exit;
}
