<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Redis

use Predis\Client as RedisClient;
use App\SessionManager;

$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$studentId = SessionManager::getUser()['id'] ?? 0;
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = "create_group:student:$studentId";
$maxAttempts = 3;
$duration = 600; // 10 minutes

// Check rate limit
if ((int)$redis->get($key) >= $maxAttempts) {
    logEvent('warn', 'Group creation blocked by rate limit', ['studentId' => $studentId, 'ip' => $ip]);
    SessionManager::setError("Too many group creation attempts. Please wait before trying again.");
    header("Location: group_create.php");
    exit;
}

$groupPageController = $pageControllers['groupPageController'];

try {
    $groupPageController->onCreateGroup($_POST, $studentId);

    $redis->incr($key);
    if ($redis->ttl($key) <= 0) {
        $redis->expire($key, $duration);
    }

    logEvent('info', 'Group created', [
        'studentId' => $studentId,
        'groupName' => $_POST['group_name'] ?? '(unknown)'
    ]);

    header("Location: dashboard.php");
    exit;

} catch (Exception $e) {
    logEvent('error', 'Group creation failed', [
        'studentId' => $studentId,
        'error' => $e->getMessage()
    ]);

    SessionManager::setError($e->getMessage());
    header("Location: group_create.php");
    exit;
}
