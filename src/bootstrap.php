<?php
// Set default timezone to Singapore
date_default_timezone_set('Asia/Singapore');

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function displayErrorMessage(): void {
    if (!empty($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
}

function displaySuccessMessage(): void {
    if (!empty($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
}

// Autoload classes from Composer (e.g. Dotenv, custom namespaces)
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/db.php';
?>
