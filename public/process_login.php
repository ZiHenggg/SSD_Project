<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php'; // for Predis

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;
use Predis\Client as RedisClient;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redis setup
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
$lockoutDuration = 900; // 15 minutes in seconds

// Check if user or IP is locked
$userAttempts = (int) $redis->get($userKey);
$ipAttempts   = (int) $redis->get($ipKey);

if ($userAttempts >= $maxAttempts || $ipAttempts >= $maxAttempts) {
    $_SESSION['login_error'] = "Account temporarily locked. Try again later.";
    header("Location: login.php");
    exit;
}

// Proceed with login
$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

$result = $pageController->loginStudent($_POST);

if ($result['success']) {
    // Login success — reset both counters
    $redis->del($userKey);
    $redis->del($ipKey);

    header("Location: " . $result['redirect']);
    exit;
} else {
    // Login failed — increment both keys and set expiry
    $redis->incr($userKey);
    $redis->incr($ipKey);

    if ($redis->ttl($userKey) <= 0) {
        $redis->expire($userKey, $lockoutDuration);
    }

    if ($redis->ttl($ipKey) <= 0) {
        $redis->expire($ipKey, $lockoutDuration);
    }

    $_SESSION['login_error'] = "Invalid credentials. Please try again.";
    header("Location: login.php");
    exit;
}
?>
