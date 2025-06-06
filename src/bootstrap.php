<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload classes from Composer (e.g. Dotenv, custom namespaces)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';
?>