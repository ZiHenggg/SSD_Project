<?php
require_once __DIR__ . '/../src/bootstrap.php';

$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

// Store credentials temporarily
$_SESSION['pending_pw_change'] = [
    'old_password' => $oldPassword,
    'new_password' => $newPassword,
];

// Redirect to the same TOTP-based verify page used for login, but in a password-update mode
$_SESSION['2fa_context'] = 'password_update';
$_SESSION['pending_2fa_email'] = $_SESSION['user']['email'];

header('Location: verify_2fa_pw_update.php');
?>