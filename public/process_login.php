<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;
use App\SessionManager;
use Predis\Client as RedisClient;

// Start session
SessionManager::start();

// Redis
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host'   => 'redis',
    'port'   => 6379,
]);

$username = $_POST['username'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$userKey = "login_attempts:user:" . $username;
$ipKey   = "login_attempts:ip:" . $ip;
$maxAttempts = 5;
$lockoutDuration = 900;

$userAttempts = (int) $redis->get($userKey);
$ipAttempts   = (int) $redis->get($ipKey);

if ($userAttempts >= $maxAttempts || $ipAttempts >= $maxAttempts) {
    SessionManager::setLoginError("Account temporarily locked. Try again later.");
    header("Location: login.php");
    exit;
}

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

$result = $pageController->loginStudent($_POST);

if ($result['success']) {
    $redis->del([$userKey, $ipKey]);
    SessionManager::setLoginError(null);
    header("Location: " . $result['redirect']);
    exit;
} else {
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
