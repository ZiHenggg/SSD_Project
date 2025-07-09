<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\SessionManager;
// use Predis\Client as RedisClient;

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

// ==== Redis Rate Limiting ====
// $redis = new RedisClient([
//     'scheme' => 'tcp',
//     'host'   => 'redis',
//     'port'   => 6379,
// ]);

$username = $_POST['username'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$actionController = $pageControllers['actionController'];

// $userKey = "login_attempts:user:" . $username;
// $ipKey   = "login_attempts:ip:" . $ip;
// $maxAttempts = 5;
// $lockoutDuration = 900;

// $userAttempts = (int) $redis->get($userKey);
// $ipAttempts   = (int) $redis->get($ipKey);

// if ($userAttempts >= $maxAttempts || $ipAttempts >= $maxAttempts) {
//     SessionManager::setLoginError("Account temporarily locked. Try again later.");
//     header("Location: login.php");
//     exit;
// }

if (!$actionController->onIpAction($ip, 'login')) {
    SessionManager::setLoginError("Too many login attempts. Please try again later.");
    header("Location: login.php");
    exit;
}

// ==== Actual Login ====
$pageController = $pageControllers['studentPageController'];
$result = $pageController->loginStudent($_POST);

if ($result['success']) {
    // $redis->del([$userKey, $ipKey]);
    $actionController->pruneOldActions(); // Clean up old actions to prevent DB bloat
    SessionManager::setLoginError(null);
    header("Location: " . $result['redirect']);
    exit;
} else {
//     $redis->incr($userKey);
//     $redis->incr($ipKey);

//     if ($redis->ttl($userKey) <= 0) {
//         $redis->expire($userKey, $lockoutDuration);
//     }
//     if ($redis->ttl($ipKey) <= 0) {
//         $redis->expire($ipKey, $lockoutDuration);
//     }

    SessionManager::setLoginError("Invalid credentials. Please try again.");
    header("Location: login.php");
    exit;
}
