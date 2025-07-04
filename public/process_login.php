<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\SessionManager;
use Predis\Client as RedisClient;

// Start session
SessionManager::start();

// Redis for rate limiting
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host'   => 'redis',
    'port'   => 6379,
]);

$username = $_POST['username'] ?? '';
$ip = $_SERVER['HTTP_X_REAL_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? 'unknown';

$userKey = "login_attempts:user:" . $username;
$ipKey   = "login_attempts:ip:" . $ip;
$maxAttempts = 5;
$lockoutDuration = 900;

$userAttempts = (int) $redis->get($userKey);
$ipAttempts   = (int) $redis->get($ipKey);

if ($userAttempts >= $maxAttempts || $ipAttempts >= $maxAttempts) {
    logEvent('warn', 'Login blocked due to rate limit', ['username' => $username, 'ip' => $ip]);
    SessionManager::setLoginError("Account temporarily locked. Try again later.");
    header("Location: login.php");
    exit;
}

$pageController = $pageControllers['studentPageController'];
$result = $pageController->loginStudent($_POST);

if ($result['success']) {
    // ✅ Set user into session (pending 2FA)
    SessionManager::setUser([
        'id' => $username,
        'email' => $result['email'] ?? null,
        'name' => $result['name'] ?? null,
    ]);

    logEvent('info', 'Login passed password check, pending 2FA', [
        'username' => $username,
        'ip' => $ip
    ]);

    $redis->del([$userKey, $ipKey]);
    SessionManager::setLoginError(null);
    header("Location: " . $result['redirect']);
    exit;
} else {
    logEvent('warn', 'Login failed', ['username' => $username, 'ip' => $ip]);

    $redis->incr($userKey);
    $redis->incr($ipKey);

    if ($redis->ttl($userKey) <= 0) {
        $redis->expire($userKey, $lockoutDuration);
    }
    if ($redis->ttl($ipKey) <= 0) {
        $redis->expire($ipKey, $lockoutDuration);
    }

    SessionManager::setLoginError("Invalid credentials. Please try again.");
    header("Location: login.php");
    exit;
}
