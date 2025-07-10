<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\SessionManager;

// Load CSRF protection
require_once __DIR__ . '/../src/CsrfManager.php';

// CSRF token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfManager::validateToken($_POST['csrf_token'] ?? '')) {
        SessionManager::destroy(); // Full logout
        header('Location: error.php'); // Redirect to user-friendly error page
        exit;
    }
}


// ==== CI MODE ====
if (getenv('CI') === 'true') {
    $pageController = $pageControllers['studentPageController'];
    $result = $pageController->loginStudent($_POST);

    if ($result['success']) {
        SessionManager::setLoginError(null);
        header("Location: " . $result['redirect']);
        exit;
    } else {
        SessionManager::setLoginError("CI mode login failed: " . ($result['message'] ?? 'Unknown error'));
        header("Location: login.php");
        exit;
    }
}

$username = $_POST['username'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$actionController = $pageControllers['actionController'];

if (!$actionController->onIpAction($ip, 'login')) {
    SessionManager::setLoginError("Too many login attempts. Please try again later.");
    header("Location: login.php");
    exit;
}

// ==== Actual Login ====
$pageController = $pageControllers['studentPageController'];
$result = $pageController->loginStudent($_POST);

if ($result['success']) {
    $actionController->pruneOldActions(); // Clean up old actions to prevent DB bloat
    SessionManager::setLoginError(null);
    header("Location: " . $result['redirect']);
    exit;
} else {
    SessionManager::setLoginError("Invalid credentials. Please try again.");
    header("Location: login.php");
    exit;
}
