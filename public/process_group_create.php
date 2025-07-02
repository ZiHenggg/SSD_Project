<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Redis


use Predis\Client as RedisClient;
use App\SessionManager;

// Redis setup
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$studentId = SessionManager::get('user')['id'] ?? 0;
$key = "create_group:student:$studentId";
$maxAttempts = 3;
$duration = 600; // 10 minutes

// Check rate limit
if ((int)$redis->get($key) >= $maxAttempts) {
    SessionManager::setError("Too many group creation attempts. Please wait before trying again.");
    header("Location: group_create.php");
    exit;
}

$groupPageController = $pageControllers['groupPageController'];

try {
    $groupPageController->onCreateGroup($_POST, $studentId);

    // Success: record the attempt
    $redis->incr($key);
    if ($redis->ttl($key) <= 0) {
        $redis->expire($key, $duration); // Only set expiration if it's new
    }

    header("Location: dashboard.php");
    exit;

} catch (Exception $e) {
    SessionManager::setError($e->getMessage());
    header("Location: group_create.php");
    exit;
}
