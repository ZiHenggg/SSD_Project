<?php
use App\SessionManager;

SessionManager::start();
SessionManager::enforceTimeoutIfLoggedIn();

$user = SessionManager::getUser();
$twoFA = SessionManager::get2FA();
$pendingEmail = $twoFA['pending_email'] ?? null;
$is2faVerified = SessionManager::is2faVerified();
$currentScript = basename($_SERVER['SCRIPT_NAME']);

// No user? → login
if (!$user) {
    SessionManager::destroy();
    header('Location: login.php');
    exit;
}

// Logged in, but 2FA not verified, and not on verify_2fa.php? → nuke session
if (!$is2faVerified && $currentScript !== 'verify_2fa.php') {
    SessionManager::resetAuth();
    SessionManager::destroy();
    header('Location: login.php');
    exit;
}

// Typing verify_2fa.php manually when 2FA isn't pending? → nuke session
if ($currentScript === 'verify_2fa.php' && (!$pendingEmail || $is2faVerified)) {
    SessionManager::resetAuth();
    SessionManager::destroy();
    header('Location: login.php');
    exit;
}
