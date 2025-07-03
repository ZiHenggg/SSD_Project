<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\SessionManager;

SessionManager::start();

$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

// Store credentials temporarily
SessionManager::set('pending_pw_change', [
    'old_password' => $oldPassword,
    'new_password' => $newPassword,
]);

// Redirect to the same TOTP-based verify page used for login, but in a password-update mode
SessionManager::set('2fa_context', 'password_update');
SessionManager::set('pending_2fa_email', SessionManager::getUser()['email']);

header('Location: verify_2fa_pw_update.php');
?>